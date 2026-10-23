<?php

namespace App\Services;

use App\Exceptions\ShippingProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class RajaOngkirClient
{
    /**
     * @return list<array{id: int, label: string, province_name: string, city_name: string, district_name: string, subdistrict_name: string, zip_code: string}>
     */
    public function searchDestinations(string $search, int $limit = 100, int $offset = 0): array
    {
        $search = trim($search);
        if ($search === '' || $limit < 1 || $limit > 100 || $offset < 0) {
            throw new InvalidArgumentException('Parameter pencarian lokasi tidak valid.');
        }

        $cacheKey = 'rajaongkir:destinations:'.hash('sha256', mb_strtolower($search).'|'.$limit.'|'.$offset);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($search, $limit, $offset): array {
            $response = $this->request('destination_search', fn () => $this->client()
                ->get('/destination/domestic-destination', [
                    'search' => $search,
                    'limit' => $limit,
                    'offset' => $offset,
                ]));

            if ($response->status() === 404) {
                return [];
            }

            $body = $this->validatedBody($response, 'destination_search');
            $data = $body['data'] ?? [];

            if (! is_array($data)) {
                throw $this->invalidResponse('destination_search');
            }

            $destinations = [];
            foreach ($data as $row) {
                if (! is_array($row)
                    || ! filter_var($row['id'] ?? null, FILTER_VALIDATE_INT)
                    || ! $this->hasStringFields($row, [
                        'label', 'province_name', 'city_name', 'district_name',
                        'subdistrict_name', 'zip_code',
                    ])) {
                    throw $this->invalidResponse('destination_search');
                }

                $destinations[] = [
                    'id' => (int) $row['id'],
                    'label' => trim($row['label']),
                    'province_name' => trim($row['province_name']),
                    'city_name' => trim($row['city_name']),
                    'district_name' => trim($row['district_name']),
                    'subdistrict_name' => trim($row['subdistrict_name']),
                    'zip_code' => trim($row['zip_code']),
                ];
            }

            return $destinations;
        });
    }

    /**
     * @return list<array{name: string, code: string, service: string, description: string, cost: int, etd: string}>
     */
    public function calculateDomesticCost(int $origin, int $destination, int $weightGram, string $courier): array
    {
        if ($origin < 1 || $destination < 1 || $weightGram < 1 || trim($courier) === '') {
            throw new InvalidArgumentException('Parameter kalkulasi ongkir tidak valid.');
        }

        $response = $this->request('calculate_domestic_cost', fn () => $this->client()
            ->asForm()
            ->post('/calculate/domestic-cost', [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weightGram,
                'courier' => strtolower(trim($courier)),
            ]));

        $body = $this->validatedBody($response, 'calculate_domestic_cost');
        $data = $body['data'] ?? null;

        if (! is_array($data)) {
            throw $this->invalidResponse('calculate_domestic_cost');
        }

        $services = [];
        foreach ($data as $row) {
            if (! is_array($row)
                || ! $this->hasStringFields($row, ['name', 'code', 'service'])
                || ! is_numeric($row['cost'] ?? null)
                || (float) $row['cost'] < 0
                || ! is_string($row['etd'] ?? null)
                || ! is_string($row['description'] ?? null)) {
                throw $this->invalidResponse('calculate_domestic_cost');
            }

            $services[] = [
                'name' => trim($row['name']),
                'code' => strtolower(trim($row['code'])),
                'service' => trim($row['service']),
                'description' => trim($row['description']),
                'cost' => (int) round((float) $row['cost']),
                'etd' => trim($row['etd']),
            ];
        }

        return $services;
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        $key = config('services.rajaongkir.key');
        $baseUrl = config('services.rajaongkir.base_url');
        $timeout = (int) config('services.rajaongkir.timeout', 15);
        $connectTimeout = (int) config('services.rajaongkir.connect_timeout', 5);

        if (! is_string($key) || trim($key) === '' || ! is_string($baseUrl) || trim($baseUrl) === '') {
            throw ShippingProviderException::invalidConfiguration();
        }

        return Http::baseUrl(rtrim($baseUrl, '/'))
            ->acceptJson()
            ->withHeaders(['key' => $key])
            ->connectTimeout(max(1, min($connectTimeout, $timeout)))
            ->timeout(max(1, $timeout));
    }

    private function request(string $operation, callable $callback): \Illuminate\Http\Client\Response
    {
        try {
            $response = $callback();
        } catch (ConnectionException $exception) {
            Log::warning('RajaOngkir request failed', [
                'operation' => $operation,
                'exception' => $exception::class,
            ]);

            throw ShippingProviderException::unavailable();
        }

        if (! $response->successful() && ! ($operation === 'destination_search' && $response->status() === 404)) {
            Log::warning('RajaOngkir returned an HTTP error', [
                'operation' => $operation,
                'status' => $response->status(),
            ]);

            throw ShippingProviderException::unavailable();
        }

        return $response;
    }

    /**
     * @return array{meta: array<string, mixed>, data?: mixed}
     */
    private function validatedBody(\Illuminate\Http\Client\Response $response, string $operation): array
    {
        $body = $response->json();
        $meta = is_array($body) ? ($body['meta'] ?? null) : null;

        if (! is_array($meta)
            || ! in_array($meta['status'] ?? null, ['success', 'error'], true)
            || ! is_numeric($meta['code'] ?? null)) {
            throw $this->invalidResponse($operation);
        }

        if (($meta['status'] ?? null) !== 'success') {
            if ($operation === 'destination_search' && (int) $meta['code'] === 404) {
                return ['meta' => $meta, 'data' => []];
            }

            Log::warning('RajaOngkir returned an API error', [
                'operation' => $operation,
                'status' => (int) $meta['code'],
            ]);

            throw ShippingProviderException::unavailable();
        }

        if (! is_array($body) || ! array_key_exists('data', $body)) {
            throw $this->invalidResponse($operation);
        }

        return ['meta' => $meta, 'data' => $body['data']];
    }

    private function invalidResponse(string $operation): ShippingProviderException
    {
        Log::warning('RajaOngkir returned an invalid response', ['operation' => $operation]);

        return ShippingProviderException::unavailable();
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $fields
     */
    private function hasStringFields(array $row, array $fields): bool
    {
        foreach ($fields as $field) {
            if (! is_string($row[$field] ?? null) || trim($row[$field]) === '') {
                return false;
            }
        }

        return true;
    }
}
