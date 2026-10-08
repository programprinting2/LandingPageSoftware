<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Semua client lama → active; ke depan hanya active | inactive (kontrol admin/ban).
        DB::table('clients')
            ->whereNotIn('status', ['active', 'inactive'])
            ->update(['status' => 'active', 'updated_at' => now()]);

        DB::table('clients')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'active', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Tidak mengembalikan trial/expired/pending — tidak dapat dipulihkan secara aman.
    }
};
