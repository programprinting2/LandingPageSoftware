<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasTable('client_applications')) {
            return;
        }

        $rows = DB::table('client_applications as ca')
            ->join('clients as c', 'c.id', '=', 'ca.client_id')
            ->join('applications as a', 'a.id', '=', 'ca.application_id')
            ->leftJoin('application_packages as p', 'p.id', '=', 'ca.application_package_id')
            ->select([
                'ca.id as pivot_id',
                'ca.client_id',
                'c.user_id',
                'ca.application_id',
                'ca.application_package_id',
                'ca.purchased_at',
                'a.name as application_name',
                'p.name as package_name',
                'p.price as package_price',
            ])
            ->orderBy('ca.id')
            ->get();

        $sequence = (int) DB::table('transactions')->max('id');

        foreach ($rows as $row) {
            $sequence++;
            $paidAt = $row->purchased_at ?: now();
            $year = date('Y', strtotime((string) $paidAt));
            $reference = sprintf('TRX-%s-%05d', $year, $sequence);
            $packageName = $row->package_name ?: 'Paket';
            $description = sprintf('%s — %s', $row->application_name, $packageName);

            DB::table('transactions')->insert([
                'reference' => $reference,
                'client_id' => $row->client_id,
                'user_id' => $row->user_id,
                'application_id' => $row->application_id,
                'application_package_id' => $row->application_package_id,
                'type' => 'purchase',
                'description' => $description,
                'amount' => (float) ($row->package_price ?? 0),
                'currency' => 'IDR',
                'payment_method' => 'admin',
                'status' => 'paid',
                'paid_at' => $paidAt,
                'meta' => json_encode([
                    'source' => 'backfill',
                    'client_application_id' => (int) $row->pivot_id,
                ]),
                'created_at' => $paidAt,
                'updated_at' => $paidAt,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('transactions')
            ->whereNotNull('meta')
            ->where('meta->source', 'backfill')
            ->delete();
    }
};
