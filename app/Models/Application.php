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
        'logo_url',
        'default_devices_max',
        'is_active',
        'sort_order',
        'notification_enabled',
        'notification_image_url',
        'notification_package_ids',
    ];

    protected function casts(): array
    {
        return [
            'default_devices_max' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'notification_enabled' => 'boolean',
            'notification_package_ids' => 'array',
        ];
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_applications')
            ->withPivot(['application_package_id', 'devices_max', 'status', 'purchased_at', 'expires_at', 'trial_consumed'])
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
