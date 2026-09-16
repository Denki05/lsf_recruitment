<?php

use Illuminate\Database\Seeder;
use App\Position;

class PositionsTableSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['title' => 'Koordinator Gudang & GA', 'location' => 'Surabaya',
             'description' => 'Bertanggung jawab atas operasional gudang dan general affair area Surabaya.',
             'requirements' => "- Min. pengalaman 2 tahun di gudang/logistik\n- Wajib SIM B aktif\n- Bersedia kerja shift & lembur saat dibutuhkan",
             'is_active' => true],
            ['title' => 'Sales Manager', 'location' => 'Jakarta',
             'description' => 'Memimpin tim sales area Jakarta, target dan pengembangan pasar.',
             'requirements' => "- Min. pengalaman 3 tahun sebagai sales/leader\n- Wajib SIM A aktif\n- Memiliki jaringan pasar yang luas",
             'is_active' => true],
            ['title' => 'Marketing Manager', 'location' => 'Surabaya',
             'description' => 'Menyusun strategi marketing area Surabaya.',
             'requirements' => "- Min. pengalaman 3 tahun di marketing\n- Memahami digital marketing\n- Bersedia dinas luar kota",
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
