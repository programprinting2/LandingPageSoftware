<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $spotColorId = DB::table('applications')->where('slug', 'spot-color')->value('id');

        if (! $spotColorId) {
            return;
        }

        $now = now();
        $clientIds = DB::table('clients')->pluck('id');

        foreach ($clientIds as $clientId) {
            $exists = DB::table('client_applications')
                ->where('client_id', $clientId)
                ->where('application_id', $spotColorId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('client_applications')->insert([
                'client_id' => $clientId,
                'application_id' => $spotColorId,
                'devices_max' => null,
                'status' => 'active',
                'purchased_at' => $now,
                'expires_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $spotColorId = DB::table('applications')->where('slug', 'spot-color')->value('id');

        if ($spotColorId) {
            DB::table('client_applications')->where('application_id', $spotColorId)->delete();
        }
    }
};
