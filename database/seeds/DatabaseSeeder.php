<?php

use Illuminate\Database\Seeder;
use App\Branch;
use App\Position;
use App\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Catatan hosting tanpa composer: JANGAN panggil kelas seeder baru dari sini
     * (classmap autoloader basi = "Target class does not exist"). Logika seed
     * cabang ditulis inline di bawah agar `php artisan db:seed` tetap jalan
     * tanpa `composer dump-autoload`. Idempoten via firstOrCreate/updateOrCreate.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminUserSeeder::class);
        $this->seedBranches();
        $this->call(PositionsTableSeeder::class);
    }

    protected function seedBranches()
    {
        // Butuh tabel branches (migrasi 2026_09_22_000001). Kalau migrasi belum
        // jalan di server ini, lewati dengan pesan jelas, bukan error.
        if (!\Illuminate\Support\Facades\Schema::hasTable('branches')) {
            $this->command->warn('Tabel branches belum ada — jalankan php artisan migrate dulu, lalu ulangi db:seed.');
            return;
        }

        $unifra = Branch::firstOrCreate(
            ['name' => 'UNIFRA'],
            ['location' => 'Surabaya', 'address' => null, 'is_active' => true]
        );
        $ppi = Branch::firstOrCreate(
            ['name' => 'PPI'],
            ['location' => 'Jakarta', 'address' => null, 'is_active' => true]
        );

        // Petakan loker lama yang belum punya cabang berdasar kota
        foreach (Position::whereNull('branch_id')->get() as $p) {
            $loc = strtolower($p->location ?: '');
            $p->branch_id = (strpos($loc, 'jakarta') !== false) ? $ppi->id : $unifra->id;
            $p->save();
        }

        // Akun developer/superadmin
        $dev = User::firstOrNew(['email' => 'developer@recruitment.local']);
        $dev->name = 'Developer';
        $dev->password = Hash::make('developer123');
        $dev->is_superadmin = true;
        $dev->save();

        // Admin lama naikkan jadi superadmin bila belum
        if ($admin = User::where('email', 'admin@recruitment.local')->first()) {
            $admin->is_superadmin = true;
            $admin->save();
        }

        // Contoh akun per cabang (butuh tabel pivot branch_user dari migrasi yang sama)
        if (!\Illuminate\Support\Facades\Schema::hasTable('branch_user')) {
            $this->command->warn('Tabel branch_user belum ada — jalankan php artisan migrate dulu.');
            return;
        }
        $unifraUser = User::firstOrNew(['email' => 'unifra@recruitment.local']);
        $unifraUser->name = 'Admin UNIFRA';
        $unifraUser->password = Hash::make('unifra123');
        $unifraUser->is_superadmin = false;
        $unifraUser->save();
        $unifraUser->branches()->syncWithoutDetaching([$unifra->id]);

        $ppiUser = User::firstOrNew(['email' => 'ppi@recruitment.local']);
        $ppiUser->name = 'Admin PPI';
        $ppiUser->password = Hash::make('ppi12345');
        $ppiUser->is_superadmin = false;
        $ppiUser->save();
        $ppiUser->branches()->syncWithoutDetaching([$ppi->id]);
    }
}
