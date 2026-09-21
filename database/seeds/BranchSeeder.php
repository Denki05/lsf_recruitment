<?php

use Illuminate\Database\Seeder;
use App\Branch;
use App\Position;
use App\User;
use Illuminate\Support\Facades\Hash;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $unifra = Branch::firstOrCreate(
            ['name' => 'UNIFRA'],
            ['location' => 'Surabaya', 'address' => null, 'is_active' => true]
        );
        $ppi = Branch::firstOrCreate(
            ['name' => 'PPI'],
            ['location' => 'Jakarta', 'address' => null, 'is_active' => true]
        );

        // Petakan loker lama yang belum punya cabang berdasar kota di kolom location
        foreach (Position::whereNull('branch_id')->get() as $p) {
            $loc = strtolower($p->location ?: '');
            if (strpos($loc, 'jakarta') !== false) {
                $p->branch_id = $ppi->id;
            } else {
                $p->branch_id = $unifra->id;
            }
            $p->save();
        }

        // Akun developer/superadmin: bisa buat cabang + login baru + lihat semua
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

        // Contoh akun per cabang (password sama pola: nama123)
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
