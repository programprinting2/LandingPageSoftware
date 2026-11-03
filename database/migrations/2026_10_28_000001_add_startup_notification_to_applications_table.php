<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('notification_enabled')->default(false)->after('sort_order');
            $table->string('notification_image_url', 500)->nullable()->after('notification_enabled');
            $table->json('notification_package_ids')->nullable()->after('notification_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'notification_enabled',
                'notification_image_url',
                'notification_package_ids',
            ]);
        });
    }
};
