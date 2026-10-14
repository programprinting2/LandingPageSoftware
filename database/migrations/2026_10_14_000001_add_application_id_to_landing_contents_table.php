<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_contents', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $applicationId = DB::table('applications')
            ->where('slug', config('softsale.default_application_slug', 'spot-color'))
            ->value('id');

        if (! $applicationId) {
            $applicationId = DB::table('applications')->orderBy('id')->value('id');
        }

        if ($applicationId) {
            DB::table('landing_contents')->update(['application_id' => $applicationId]);
        }

        Schema::table('landing_contents', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->unique(['application_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('landing_contents', function (Blueprint $table) {
            $table->dropUnique(['application_id', 'key']);
            $table->unique(['key']);
            $table->dropConstrainedForeignId('application_id');
        });
    }
};
