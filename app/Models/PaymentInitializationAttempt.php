<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInitializationAttempt extends Model
{
    protected $fillable = [
        'order_id',
        'attempt_number',
        'idempotency_key',
        'request_fingerprint',
        'status',
        'lease_expires_at',
        'payment_id',
    ];

    protected function casts(): array
    {
        return ['lease_expires_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
