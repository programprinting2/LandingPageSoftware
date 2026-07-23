<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'default_devices_max',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_devices_max' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_applications')
            ->withPivot(['devices_max', 'status', 'purchased_at', 'expires_at'])
            ->withTimestamps();
    }

    public function licenseDevices(): HasMany
    {
        return $this->hasMany(LicenseDevice::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ApplicationPackage::class)->orderBy('sort_order')->orderBy('id');
    }
}
