<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeAffiliateRoleUsersCommand extends Command
{
    protected $signature = 'user:purge-affiliate-role';

    protected $description = 'Hapus akun role affiliate (coba-coba) dan ubah ke client jika punya profil client';

    public function handle(): int
    {
        $users = User::query()
            ->where('role', 'affiliate')
            ->with(['client', 'affiliate'])
            ->get();

        if ($users->isEmpty()) {
            $this->info('Tidak ada user dengan role affiliate.');

            return self::SUCCESS;
        }

        $converted = 0;
        $deleted = 0;

        DB::transaction(function () use ($users, &$converted, &$deleted) {
            foreach ($users as $user) {
                if ($user->client) {
                    $user->update(['role' => 'client']);
                    $converted++;
                    $this->line("→ {$user->email}: role diubah ke client");

                    continue;
                }

                $user->affiliate?->delete();
                $user->delete();
                $deleted++;
                $this->line("✕ {$user->email}: dihapus");
            }
        });

        $this->info("Selesai. Diubah: {$converted}, dihapus: {$deleted}.");

        return self::SUCCESS;
    }
}
