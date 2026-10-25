<?php

namespace App\Exceptions;

use RuntimeException;

class ShippingProviderException extends RuntimeException
{
    public static function unavailable(): self
    {
        return new self('Layanan ongkir sedang tidak tersedia. Silakan coba lagi.');
    }

    public static function invalidConfiguration(): self
    {
        return new self('Konfigurasi layanan ongkir belum lengkap atau tidak valid.');
    }

    public static function rateLimited(): self
    {
        return new self('Permintaan pelacakan terlalu sering. Silakan coba kembali beberapa saat lagi.');
    }
}
