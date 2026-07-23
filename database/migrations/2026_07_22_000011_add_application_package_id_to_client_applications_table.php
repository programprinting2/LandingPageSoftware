<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_applications', function (Blueprint $table) {
            $table->foreignId('application_package_id')
                ->nullable()
                ->after('application_id')
                ->constrained('application_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('client_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('application_package_id');
        });
    }
};
