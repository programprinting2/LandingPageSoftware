<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'reference',
        'client_id',
        'user_id',
        'application_id',
        'application_package_id',
        'type',
        'description',
        'amount',
        'currency',
        'payment_method',
        'status',
        'paid_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ApplicationPackage::class, 'application_package_id');
    }

    public function paymentOrder(): HasOne
    {
        return $this->hasOne(PaymentOrder::class);
    }
}
