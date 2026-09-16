<?php

use Illuminate\Database\Seeder;
use App\Applicant;
use App\Position;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Data dummy untuk demo admin panel.
 * BERJALAN MANUAL SAJA: php artisan db:seed --class=DummyApplicantsSeeder
 * JANGAN dijalankan di production. Hapus data demo:
 *   DELETE FROM applicants WHERE email LIKE '%@contoh.id';
 */
class DummyApplicantsSeeder extends Seeder
{
    public function run()
    {
        $positions = Position::orderBy('id')->get();
        if ($positions->isEmpty()) {
            $this->command->error('Isi tabel positions dulu.');
            return;
        }

        $names = [
            ['Budi Santoso', 'Laki-laki'], ['Siti Rahayu', 'Perempuan'],
            ['Agus Wijaya', 'Laki-laki'], ['Dewi Lestari', 'Perempuan'],
            ['Rudi Hartono', 'Laki-laki'], ['Maya Putri', 'Perempuan'],
            ['Joko Prasetyo', 'Laki-laki'], ['Rina Marlina', 'Perempuan'],
            ['Hendra Gunawan', 'Laki-laki'], ['Fitri Handayani', 'Perempuan'],
            ['Dedi Kurniawan', 'Laki-laki'], ['Nina Kurnia', 'Perempuan'],
            ['Tono Suharto', 'Laki-laki'], ['Wulan Sari', 'Perempuan'],
        ];
        $cities = ['Surabaya', 'Sidoarjo', 'Gresik', 'Jakarta', 'Bekasi', 'Malang'];
        $sims = ['A', 'B', 'C', 'A dan C', 'Tidak Punya'];
        $statuses = ['Baru', 'Baru', 'Baru', 'Seleksi', 'Seleksi', 'Interview', 'Diterima', 'Ditolak'];

        Storage::disk('public')->makeDirectory('lamaran');

        foreach ($names as $i => [$nama, $jk]) {
            $pos = $positions[$i % $positions->count()];
            $date = Carbon::now()->subDays($i % 14)->setTime(9 + ($i % 8), 15);
            $fname = 'lamaran/dummy_cv_' . ($i + 1) . '.pdf';
            Storage::disk('public')->put($fname, "%PDF-1.4\n% Dummy CV $nama\n");

            Applicant::create([
                'position_id' => $pos->id,
                'nama_lengkap' => $nama,
                'jenis_kelamin' => $jk,
                'no_hp' => '0812' . str_pad(10000000 + $i * 137, 8, '0', STR_PAD_LEFT),
                'email' => strtolower(str_replace(' ', '.', $nama)) . '@contoh.id',
                'domisili' => $cities[$i % count($cities)],
                'sim' => $sims[$i % count($sims)],
                'file_path' => $fname,
                'file_original' => 'CV_' . str_replace(' ', '_', $nama) . '.pdf',
                'file_mime' => 'application/pdf',
                'file_size' => 20480,
                'status' => $statuses[$i % count($statuses)],
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        $this->command->info('14 data dummy dibuat.');
    }
}
