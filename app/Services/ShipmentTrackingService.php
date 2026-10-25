<?php

namespace App\Services;

use App\Exceptions\ShippingProviderException;
use App\Models\Shipment;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class ShipmentTrackingService
{
    private const SNAPSHOT_TTL_MINUTES = 43200;

    private const REFRESH_COOLDOWN_MINUTES = 5;

    public function __construct(protected RajaOngkirClient $client) {}

    /** @return array{status: string, delivered: ?bool, events: list<array<string, ?string>>, fetched_at: string}|null */
    public function cachedSnapshot(Shipment $shipment): ?array
    {
        if (! $this->hasTrackingIdentity($shipment)) {
            return null;
        }

        $snapshot = Cache::get($this->snapshotKey($shipment));

        return is_array($snapshot) ? $snapshot : null;
    }

    /** @return array{status: string, delivered: ?bool, events: list<array<string, ?string>>, fetched_at: string} */
    public function refresh(Shipment $shipment): array
    {
        $order = $shipment->order;
        $awb = trim((string) $shipment->tracking_number);
        $courier = strtolower(trim((string) $shipment->courier_code));
        $phone = preg_replace('/\D+/', '', (string) $order?->shipping_phone) ?? '';

        if (! in_array($order?->status, ['shipped', 'delivered', 'completed'], true)
            || ! in_array($shipment->status, ['shipped', 'delivered'], true)
            || $awb === ''
            || ! preg_match('/^[a-z0-9_-]{2,24}$/', $courier)
            || strlen($phone) < 5) {
            throw new InvalidArgumentException('Pelacakan tersedia setelah pesanan dikirim dan kurir, nomor resi, serta nomor telepon valid.');
        }

        $snapshotKey = $this->snapshotKey($shipment);
        $cooldownKey = $snapshotKey.':refresh-cooldown';

        if (! Cache::add($cooldownKey, true, now()->addMinutes(self::REFRESH_COOLDOWN_MINUTES))) {
            $cached = Cache::get($snapshotKey);
            if (is_array($cached)) {
                return $cached;
            }

            throw ShippingProviderException::rateLimited();
        }

        $tracking = $this->client->trackWaybill($awb, $courier, substr($phone, -5));
        $snapshot = $tracking + ['fetched_at' => now()->toIso8601String()];

        Cache::put($snapshotKey, $snapshot, now()->addMinutes(self::SNAPSHOT_TTL_MINUTES));

        return $snapshot;
    }

    private function hasTrackingIdentity(Shipment $shipment): bool
    {
        return filled($shipment->tracking_number)
            && filled($shipment->courier_code)
            && preg_match('/^[a-z0-9_-]{2,24}$/i', (string) $shipment->courier_code) === 1;
    }

    private function snapshotKey(Shipment $shipment): string
    {
        $identity = strtolower(trim((string) $shipment->courier_code)).'|'.trim((string) $shipment->tracking_number);

        return 'shipment-tracking:'.$shipment->id.':'.hash('sha256', $identity);
    }
}
