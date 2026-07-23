<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationPackage extends Model
{
    protected $fillable = [
        'application_id',
        'name',
        'devices_quota',
        'duration_days',
        'price',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'devices_quota' => 'integer',
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
