<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'whatsapp',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'status',
        'sales_count',
        'total_commission',
        'pending_commission',
    ];

    protected function casts(): array
    {
        return [
            'sales_count' => 'integer',
            'total_commission' => 'decimal:2',
            'pending_commission' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentOrders(): HasMany
    {
        return $this->hasMany(PaymentOrder::class);
    }
}
