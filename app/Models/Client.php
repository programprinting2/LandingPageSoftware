<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'whatsapp',
        'status',
        'devices_max',
        'report_archive_password',
    ];

    protected $hidden = [
        'report_archive_password',
    ];

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'client_applications')
            ->withPivot(['application_package_id', 'devices_max', 'extra_device_slots', 'status', 'purchased_at', 'expires_at', 'trial_consumed', 'report_archive_password'])
            ->withTimestamps();
    }

    public function licenseDevices(): HasMany
    {
        return $this->hasMany(LicenseDevice::class, 'user_id', 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
