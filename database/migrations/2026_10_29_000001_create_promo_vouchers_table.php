<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->string('label')->nullable();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::table('payment_orders', function (Blueprint $table) {
            $table->foreignId('promo_voucher_id')->nullable()->after('affiliate_id')->constrained('promo_vouchers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_voucher_id');
        });

        Schema::dropIfExists('promo_vouchers');
    }
};
