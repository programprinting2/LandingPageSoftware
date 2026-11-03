<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoVoucher extends Model
{
    protected $fillable = [
        'code',
        'label',
        'application_id',
        'discount_percent',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'float',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function paymentOrders(): HasMany
    {
        return $this->hasMany(PaymentOrder::class);
    }
}
