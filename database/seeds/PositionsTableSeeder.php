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

        // 3 loker utama (detail sesuai info screenshot lowongan).
        // updateOrCreate: aman dijalankan ulang + memperkaya baris lama tanpa
        // menimpa cabang yang sudah diatur admin (branch_id hanya diisi bila kosong).
        $data = [
            ['title' => 'IT Application Support', 'location' => 'Surabaya',
             'branch' => $unifraId, 'gaji' => 5500000, 'pendidikan_minimal' => 'D3',
             'usia_min' => 22, 'usia_maks' => 35, 'butuh_lembur' => false, 'syarat_sim' => null,
             'skill_tags' => 'aplikasi:3, troubleshooting:3, support:2, helpdesk:2',
             'description' => "Full time · Memberikan dukungan aplikasi untuk operasional perusahaan.\nMelayani permintaan dan keluhan pengguna aplikasi harian.\nMelakukan troubleshooting error aplikasi bersama tim IT.",
             'requirements' => "Career Development\nCompetitive Salary & Benefits\nInnovative and Dynamic Work Environment\nPendidikan minimal D3/S1 Teknologi Informasi atau Sistem Informasi\nFresh graduate dipersilakan melamar",
             'is_active' => true],
            ['title' => 'Staf Information Technology', 'location' => 'Surabaya', 'branch' => $ppiId,
             'gaji' => 5000000, 'pendidikan_minimal' => 'D3',
             'usia_min' => 22, 'usia_maks' => 35, 'butuh_lembur' => false, 'syarat_sim' => null,
             'skill_tags' => 'troubleshooting:3, database:2, erp:2, jaringan:2, sistem informasi:2',
             'description' => "Pengembangan & Administrasi Database (Teknologi Informasi & Komunikasi).\nKontrak/Temporer · Hibrid (Surabaya, Jawa Timur).\nMemberikan dukungan teknis terkait komputer, printer, jaringan, perangkat lunak, dan akun pengguna.\nMelakukan instalasi, konfigurasi, pemeliharaan, dan perbaikan perangkat IT.\nMemantau keamanan sistem, akses pengguna, dan pencadangan data perusahaan.\nMengelola inventaris aset IT serta dokumentasi teknis.\nBerkoordinasi dengan vendor terkait pemeliharaan dan penyelesaian kendala sistem.\nMembantu proses implementasi ERP, termasuk pengujian sistem, migrasi data, dan pelatihan pengguna.\nMemberikan dukungan kepada pengguna ERP serta menyampaikan kendala kepada vendor terkait.\nMengidentifikasi peluang perbaikan sistem dan digitalisasi proses kerja perusahaan.",
             'requirements' => "Pendidikan minimal D3/S1 Teknologi Informasi, Sistem Informasi, Teknik Informatika, atau bidang terkait\nFresh graduate dipersilakan melamar\nMemahami dasar-dasar perangkat keras, perangkat lunak, jaringan, database, dan troubleshooting",
             'is_active' => true],
            ['title' => 'Koordinator Gudang & GA', 'location' => 'Surabaya', 'branch' => $unifraId,
             'gaji' => 6000000, 'pendidikan_minimal' => 'SMA/SMK',
             'usia_min' => 25, 'usia_maks' => 40, 'butuh_lembur' => true, 'syarat_sim' => 'B',
             'skill_tags' => 'gudang:3, forklift:3, stock opname:2, logistik:2',
             'description' => 'Bertanggung jawab atas operasional gudang dan general affair area Surabaya.',
             'requirements' => "Min. pengalaman 2 tahun di gudang/logistik\nWajib SIM B aktif\nBersedia kerja shift & lembur saat dibutuhkan",
             'is_active' => true],
        ];

        foreach ($data as $row) {
            $branchId = $row['branch'];
            unset($row['branch']);
            $pos = Position::firstOrNew(['title' => $row['title'], 'location' => $row['location']]);
            $pos->fill($row);
            if (empty($pos->branch_id) && $branchId) {
                $pos->branch_id = $branchId;
            }
            $pos->save();
        }
    }
}
