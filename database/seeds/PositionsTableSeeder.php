<?php

use Illuminate\Database\Seeder;
use App\Branch;
use App\Position;

class PositionsTableSeeder extends Seeder
{
    public function run()
    {
        $unifraId = Branch::where('name', 'UNIFRA')->value('id');

        // Loker demo lama diganti dengan posisi Manajer Marketing. Baris lama
        // hanya dihapus bila belum punya pelamar; bila sudah dipakai, cukup
        // ditutup agar data lamaran tidak ikut hilang.
        $legacyTitles = ['IT Application Support', 'Staf Information Technology', 'Koordinator Gudang & GA'];
        foreach (Position::whereIn('title', $legacyTitles)->get() as $old) {
            if ($old->applicants()->exists()) {
                $old->is_active = false;
                $old->save();
            } else {
                $old->delete();
            }
        }

        $position = Position::firstOrNew([
            'title' => 'Manajer Marketing',
            'location' => 'Surabaya',
        ]);

        if (!$position->exists) {
            $position->fill([
                'gaji' => null,
                'pendidikan_minimal' => null,
                'usia_min' => null,
                'usia_maks' => null,
                'butuh_lembur' => false,
                'syarat_sim' => null,
                'skill_tags' => null,
                'description' => "Memimpin dan mengembangkan strategi pemasaran perusahaan.\nMengelola tim marketing serta mengukur pencapaian target.",
                'requirements' => "Kualifikasi menyusul.",
                'is_active' => true,
            ]);
        }

        if (!$position->branch_id && $unifraId) {
            $position->branch_id = $unifraId;
        }
        if (!$position->slug) {
            $position->slug = 'mm';
        }
        $position->save();
    }
}
