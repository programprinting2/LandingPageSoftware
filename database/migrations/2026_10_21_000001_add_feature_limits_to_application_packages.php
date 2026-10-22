<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_packages', function (Blueprint $table) {
            $table->unsignedTinyInteger('parallel_max')->nullable()->after('devices_quota');
            $table->boolean('feature_uv')->default(true)->after('parallel_max');
        });
    }

    public function down(): void
    {
        Schema::table('application_packages', function (Blueprint $table) {
            $table->dropColumn(['parallel_max', 'feature_uv']);
        });
    }
};
