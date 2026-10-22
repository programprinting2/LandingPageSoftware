<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_packages', function (Blueprint $table) {
            $table->boolean('is_trial')->default(false)->after('name');
        });

        Schema::table('client_applications', function (Blueprint $table) {
            $table->boolean('trial_consumed')->default(false)->after('status');
        });

        DB::table('application_packages')
            ->whereRaw('LOWER(name) = ?', ['trial'])
            ->update(['is_trial' => true]);

        DB::table('client_applications')->update(['trial_consumed' => true]);
    }

    public function down(): void
    {
        Schema::table('application_packages', function (Blueprint $table) {
            $table->dropColumn('is_trial');
        });

        Schema::table('client_applications', function (Blueprint $table) {
            $table->dropColumn('trial_consumed');
        });
    }
};
