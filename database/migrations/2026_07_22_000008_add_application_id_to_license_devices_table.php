<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('license_devices', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });

        $spotColorId = DB::table('applications')->where('slug', 'spot-color')->value('id');

        if ($spotColorId) {
            DB::table('license_devices')->whereNull('application_id')->update([
                'application_id' => $spotColorId,
            ]);
        }

        Schema::table('license_devices', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'program_id']);
            $table->unique(['user_id', 'application_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::table('license_devices', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'application_id', 'program_id']);
            $table->unique(['user_id', 'program_id']);
            $table->dropConstrainedForeignId('application_id');
        });
    }
};
