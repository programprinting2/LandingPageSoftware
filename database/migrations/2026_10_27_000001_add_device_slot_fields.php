<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_packages', function (Blueprint $table) {
            $table->decimal('device_slot_price', 12, 2)->default(0)->after('devices_quota');
            $table->unsignedSmallInteger('max_extra_device_slots')->default(0)->after('device_slot_price');
        });

        Schema::table('client_applications', function (Blueprint $table) {
            $table->unsignedSmallInteger('extra_device_slots')->default(0)->after('devices_max');
        });
    }

    public function down(): void
    {
        Schema::table('client_applications', function (Blueprint $table) {
            $table->dropColumn('extra_device_slots');
        });

        Schema::table('application_packages', function (Blueprint $table) {
            $table->dropColumn(['device_slot_price', 'max_extra_device_slots']);
        });
    }
};
