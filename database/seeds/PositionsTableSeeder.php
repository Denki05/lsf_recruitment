<?php

use Illuminate\Database\Seeder;
use App\Position;

class PositionsTableSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['title' => 'Koordinator Gudang & GA', 'location' => 'Surabaya', 'description' => 'Bertanggung jawab atas operasional gudang dan general affair area Surabaya.', 'is_active' => true],
            ['title' => 'Sales Manager', 'location' => 'Jakarta', 'description' => 'Memimpin tim sales area Jakarta, target dan pengembangan pasar.', 'is_active' => true],
            ['title' => 'Marketing Manager', 'location' => 'Surabaya', 'description' => 'Menyusun strategi marketing area Surabaya.', 'is_active' => true],
        ];
        foreach ($data as $row) {
            Position::updateOrCreate(
                ['title' => $row['title'], 'location' => $row['location']],
                $row
            );
        }
    }
}
