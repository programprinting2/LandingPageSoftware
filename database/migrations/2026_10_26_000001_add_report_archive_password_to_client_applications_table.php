<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_applications', function (Blueprint $table) {
            $table->text('report_archive_password')->nullable()->after('trial_consumed');
        });

        if (Schema::hasColumn('clients', 'report_archive_password')) {
            $clients = DB::table('clients')
                ->whereNotNull('report_archive_password')
                ->where('report_archive_password', '!=', '')
                ->get(['id', 'report_archive_password']);

            foreach ($clients as $client) {
                DB::table('client_applications')
                    ->where('client_id', $client->id)
                    ->where(function ($query) {
                        $query->whereNull('report_archive_password')
                            ->orWhere('report_archive_password', '=', '');
                    })
                    ->update(['report_archive_password' => $client->report_archive_password]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('client_applications', function (Blueprint $table) {
            $table->dropColumn('report_archive_password');
        });
    }
};
