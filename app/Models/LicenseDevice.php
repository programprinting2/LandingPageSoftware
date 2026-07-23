<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseDevice extends Model
{
    protected $fillable = [
        'user_id',
        'application_id',
        'program_id',
        'access_info',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'access_info' => 'array',
            'activated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
