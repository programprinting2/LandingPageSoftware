<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->string('whatsapp', 20)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account', 64)->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('sales_count')->default(0);
            $table->decimal('total_commission', 12, 2)->default(0);
            $table->decimal('pending_commission', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
