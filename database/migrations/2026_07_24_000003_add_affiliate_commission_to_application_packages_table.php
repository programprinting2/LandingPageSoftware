<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_packages', function (Blueprint $table) {
            $table->decimal('affiliate_commission_fixed', 12, 2)->default(0)->after('price');
            $table->decimal('affiliate_commission_percent', 5, 2)->default(0)->after('affiliate_commission_fixed');
        });
    }

    public function down(): void
    {
        Schema::table('application_packages', function (Blueprint $table) {
            $table->dropColumn(['affiliate_commission_fixed', 'affiliate_commission_percent']);
        });
    }
};
