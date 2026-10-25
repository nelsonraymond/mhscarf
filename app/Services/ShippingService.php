<?php

namespace App\Services;

use App\Exceptions\ShippingProviderException;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Throwable;

class ShippingService
{
    private const ORIGIN_EXPECTED = [
        'subdistrict_name' => 'MAGUWOHARJO',
        'district_name' => 'DEPOK',
        'city_name' => 'SLEMAN',
        'province_name' => 'DI YOGYAKARTA',
        'zip_code' => '55282',
    ];

    private const COURIERS = [
        'jne', 'jnt', 'sicepat', 'tiki', 'pos', 'ninja', 'anteraja',
        'ide', 'sap', 'lion', 'wahana', 'rex', 'rpx', 'ncs', 'sentral',
        'star', 'dse',
    ];

    public function __construct(protected RajaOngkirClient $client) {}

    /** @return list<string> */
    public static function availableCouriers(): array
    {
        return self::COURIERS;
    }

    /**
     * @return list<array{id: int, label: string, province_name: string, city_name: string, district_name: string, subdistrict_name: string, zip_code: string}>
     */
    public function destinationsForAddress(Address $address): array
    {
        $search = trim((string) ($address->village ?: $address->postal_code));
        if ($search === '') {
            throw ValidationException::withMessages([
                'address_id' => 'Lengkapi kelurahan/desa atau kode pos pada alamat sebelum menghitung ongkir.',
            ]);
        }

        return array_values(array_filter(
            $this->client->searchDestinations($search),
            fn (array $destination): bool => $this->destinationMatchesAddress($destination, $address),
        ));
    }

    /**
     * @return list<array{quote_token: string, destination_id: int, courier: string, courier_name: string, service: string, description: string, cost: int, etd: string, expires_at: int}>
     */
    public function quote(Address $address, int $destinationId, int $weightGram, string $courier, User $user, string $cartFingerprint): array
    {
        if (! in_array($courier, self::COURIERS, true)) {
            throw ValidationException::withMessages(['courier' => 'Kurir yang dipilih tidak tersedia.']);
        }
        if ($weightGram < 1) {
            throw ValidationException::withMessages(['shipping_quote_token' => 'Berat keranjang belum valid. Periksa berat semua varian produk.']);
        }

        $originId = $this->verifiedOriginId();
        $destination = collect($this->destinationsForAddress($address))->firstWhere('id', $destinationId);
        if (! $destination) {
            throw ValidationException::withMessages([
                'destination_id' => 'Tujuan pengiriman tidak cocok dengan alamat yang dipilih. Pilih ulang alamat atau perbarui detail alamat.',
            ]);
        }

        $rates = $this->client->calculateDomesticCost($originId, $destinationId, $weightGram, $courier);
        // Zero-cost responses are not valid payable shipping tariffs.
        $rates = array_values(array_filter(
            $rates,
            fn (array $rate): bool => $rate['cost'] > 0 && $rate['code'] === $courier,
        ));

        return array_map(function (array $rate) use ($address, $destinationId, $weightGram, $user, $cartFingerprint): array {
            $issuedAt = now()->timestamp;
            $expiresAt = $issuedAt + 15 * 60;
            $quote = [
                'user_id' => $user->id,
                'address_id' => $address->id,
                'address_fingerprint' => $this->addressFingerprint($address),
                'destination_id' => $destinationId,
                'courier' => $rate['code'],
                'courier_name' => $rate['name'],
                'service' => $rate['service'],
                'description' => $rate['description'],
                'cost' => $rate['cost'],
                'etd' => $rate['etd'],
                'weight_gram' => $weightGram,
                'cart_fingerprint' => $cartFingerprint,
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
            ];

            return [
                'quote_token' => Crypt::encryptString(json_encode($quote, JSON_THROW_ON_ERROR)),
                'destination_id' => $destinationId,
                'courier' => $rate['code'],
                'courier_name' => $rate['name'],
                'service' => $rate['service'],
                'description' => $rate['description'],
                'cost' => $rate['cost'],
                'etd' => $rate['etd'],
                'expires_at' => $expiresAt,
            ];
        }, $rates);
    }

    /**
     * @return array<string, int|string>
     */
    public function resolveQuote(string $token, User $user, Address $address, int $weightGram, string $cartFingerprint): array
    {
        try {
            $quote = json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'shipping_quote_token' => 'Pilihan ongkir tidak valid. Hitung ulang ongkir sebelum checkout.',
            ]);
        }

        $valid = is_array($quote)
            && (int) ($quote['user_id'] ?? 0) === (int) $user->id
            && (int) ($quote['address_id'] ?? 0) === (int) $address->id
            && hash_equals((string) ($quote['address_fingerprint'] ?? ''), $this->addressFingerprint($address))
            && (int) ($quote['weight_gram'] ?? 0) === $weightGram
            && hash_equals((string) ($quote['cart_fingerprint'] ?? ''), $cartFingerprint)
            && $weightGram > 0
            && is_int($quote['issued_at'] ?? null)
            && $quote['issued_at'] <= now()->timestamp
            && $quote['issued_at'] >= now()->subMinutes(15)->timestamp
            && is_int($quote['expires_at'] ?? null)
            && $quote['expires_at'] === $quote['issued_at'] + 15 * 60
            && $quote['expires_at'] > now()->timestamp
            && (int) ($quote['destination_id'] ?? 0) > 0
            && in_array($quote['courier'] ?? null, self::COURIERS, true)
            && is_string($quote['service'] ?? null)
            && $quote['service'] !== ''
            && is_numeric($quote['cost'] ?? null)
            && (int) $quote['cost'] > 0;

        if (! $valid) {
            throw ValidationException::withMessages([
                'shipping_quote_token' => 'Pilihan ongkir kedaluwarsa atau tidak sesuai dengan alamat/keranjang. Hitung ulang ongkir.',
            ]);
        }

        return $quote;
    }

    /**
     * @return array<string, int|string>
     */
    public function revalidateQuote(string $token, User $user, Address $address, int $weightGram, string $cartFingerprint): array
    {
        $quote = $this->resolveQuote($token, $user, $address, $weightGram, $cartFingerprint);
        $originId = $this->verifiedOriginId();
        $destination = collect($this->destinationsForAddress($address))->firstWhere('id', (int) $quote['destination_id']);
        if (! $destination) {
            throw ValidationException::withMessages([
                'shipping_quote_token' => 'Tujuan tidak lagi cocok dengan alamat. Pilih ulang alamat dan hitung ongkir.',
            ]);
        }

        $rates = $this->client->calculateDomesticCost(
            $originId,
            (int) $quote['destination_id'],
            $weightGram,
            (string) $quote['courier'],
        );
        $currentRate = collect($rates)->first(
            fn (array $rate): bool => $rate['code'] === $quote['courier']
                && $rate['service'] === $quote['service'],
        );

        if (! $currentRate || $currentRate['cost'] !== (int) $quote['cost']) {
            throw ValidationException::withMessages([
                'shipping_quote_token' => 'Tarif ongkir berubah. Hitung ulang ongkir sebelum melanjutkan checkout.',
            ]);
        }

        return array_merge($quote, [
            'courier_name' => $currentRate['name'],
            'description' => $currentRate['description'],
            'etd' => $currentRate['etd'],
        ]);
    }

    public function verifiedOriginId(): int
    {
        $originId = filter_var(config('services.rajaongkir.origin_id'), FILTER_VALIDATE_INT);
        if (! $originId || $originId < 1) {
            throw ShippingProviderException::invalidConfiguration();
        }

        $origin = collect($this->client->searchDestinations('Maguwoharjo'))->firstWhere('id', (int) $originId);
        if (! $origin) {
            throw ShippingProviderException::invalidConfiguration();
        }

        foreach (self::ORIGIN_EXPECTED as $field => $expected) {
            if ($this->normalize((string) ($origin[$field] ?? '')) !== $this->normalize($expected)) {
                throw ShippingProviderException::invalidConfiguration();
            }
        }

        return (int) $originId;
    }

    /** @param array<string, mixed> $destination */
    private function destinationMatchesAddress(array $destination, Address $address): bool
    {
        foreach ([
            'district_name' => $address->district,
            'city_name' => $address->city,
            'province_name' => $address->province,
        ] as $field => $addressValue) {
            if (trim((string) $addressValue) === ''
                || $this->normalize($destination[$field] ?? '') !== $this->normalize((string) $addressValue)) {
                return false;
            }
        }

        if (trim((string) $address->village) !== ''
            && $this->normalize($destination['subdistrict_name'] ?? '') !== $this->normalize($address->village)) {
            return false;
        }
        if (trim((string) $address->postal_code) !== ''
            && $this->normalize($destination['zip_code'] ?? '') !== $this->normalize($address->postal_code)) {
            return false;
        }

        return trim((string) $address->village) !== '' || trim((string) $address->postal_code) !== '';
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/^(kecamatan|kec\.?|kelurahan|kel\.?|desa|kabupaten|kab\.?|kota|provinsi|prov\.?)\s*/u', '', $value) ?? $value;

        return trim(preg_replace('/[^\pL\pN]+/u', ' ', $value) ?? $value);
    }

    private function addressFingerprint(Address $address): string
    {
        return hash('sha256', json_encode([
            (int) $address->id,
            trim((string) $address->label),
            trim((string) $address->recipient_name),
            trim((string) $address->phone),
            trim((string) $address->address_line),
            $this->normalize((string) $address->village),
            $this->normalize((string) $address->district),
            $this->normalize((string) $address->city),
            $this->normalize((string) $address->province),
            $this->normalize((string) $address->postal_code),
            trim((string) $address->notes),
            (bool) $address->is_default,
        ], JSON_THROW_ON_ERROR));
    }
}
