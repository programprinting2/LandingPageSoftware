<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->foreignId('affiliate_id')->nullable()->after('application_package_id')->constrained()->nullOnDelete();
            $table->string('referral_code', 32)->nullable()->after('affiliate_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('affiliate_id');
            $table->dropColumn('referral_code');
        });
    }
};
