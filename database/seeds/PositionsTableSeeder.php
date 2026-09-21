<?php

use Illuminate\Database\Seeder;
use App\Branch;
use App\Position;

class PositionsTableSeeder extends Seeder
{
    public function run()
    {
        $unifraId = Branch::where('name', 'UNIFRA')->value('id');
        $ppiId = Branch::where('name', 'PPI')->value('id');

        $data = [
            ['title' => 'IT Application Support', 'location' => 'Surabaya', 'branch_id' => $unifraId,
             'gaji' => 5500000, 'pendidikan_minimal' => 'D3', 'usia_min' => 22, 'usia_maks' => 35,
             'butuh_lembur' => false, 'syarat_sim' => null,
             'description' => "Memberikan dukungan teknis terkait komputer, printer, jaringan, perangkat lunak, dan akun pengguna.\nMelakukan instalasi, konfigurasi, pemeliharaan, dan perbaikan perangkat IT.\nMemantau keamanan sistem, akses pengguna, dan pencadangan data perusahaan.",
             'requirements' => "Pendidikan minimal D3/S1 Teknologi Informasi, Sistem Informasi, atau bidang terkait\nFresh graduate dipersilakan melamar\nMemahami dasar perangkat keras, jaringan, dan troubleshooting",
             'is_active' => true],
            ['title' => 'Staf Information Technology', 'location' => 'Surabaya', 'branch_id' => $ppiId,
             'gaji' => 5000000, 'pendidikan_minimal' => 'D3', 'usia_min' => 22, 'usia_maks' => 32,
             'butuh_lembur' => true, 'syarat_sim' => 'C',
             'description' => "Pengembangan dan administrasi database perusahaan.\nMembantu implementasi ERP termasuk pengujian sistem, migrasi data, dan pelatihan pengguna.\nMengelola inventaris aset IT serta dokumentasi teknis.",
             'requirements' => "Pendidikan minimal D3/S1 Teknologi Informasi atau Sistem Informasi\nMemahami database dan dasar pemrograman\nBersedia lembur saat implementasi sistem",
             'is_active' => true],
            ['title' => 'Koordinator Gudang & GA', 'location' => 'Surabaya', 'branch_id' => $unifraId,
             'gaji' => 6000000, 'pendidikan_minimal' => 'SMA/SMK', 'usia_min' => 25, 'usia_maks' => 40,
             'butuh_lembur' => true, 'syarat_sim' => 'B',
             'description' => 'Bertanggung jawab atas operasional gudang dan general affair area Surabaya.',
             'requirements' => "Min. pengalaman 2 tahun di gudang/logistik\nWajib SIM B aktif\nBersedia kerja shift & lembur saat dibutuhkan",
             'is_active' => true],
            ['title' => 'Sales Manager', 'location' => 'Jakarta', 'branch_id' => $ppiId,
             'gaji' => 9000000, 'pendidikan_minimal' => 'D4/S1', 'usia_min' => 28, 'usia_maks' => 45,
             'butuh_lembur' => false, 'syarat_sim' => 'A',
             'description' => 'Memimpin tim sales area Jakarta, target dan pengembangan pasar.',
             'requirements' => "Min. pengalaman 3 tahun sebagai sales/leader\nWajib SIM A aktif\nMemiliki jaringan pasar yang luas",
             'is_active' => true],
            ['title' => 'Marketing Manager', 'location' => 'Surabaya', 'branch_id' => $unifraId,
             'gaji' => 8500000, 'pendidikan_minimal' => 'D4/S1', 'usia_min' => 27, 'usia_maks' => 42,
             'butuh_lembur' => false, 'syarat_sim' => null,
             'description' => 'Menyusun strategi marketing area Surabaya.',
             'requirements' => "Min. pengalaman 3 tahun di marketing\nMemahami digital marketing\nBersedia dinas luar kota",
             'is_active' => true],
        ];
        foreach ($data as $row) {
            // firstOrCreate: aman dijalankan ulang, tidak menimpa editan admin
            Position::firstOrCreate(
                ['title' => $row['title'], 'location' => $row['location']],
                $row
            );
        }
    }
}
