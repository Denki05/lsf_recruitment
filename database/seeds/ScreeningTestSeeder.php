<?php

use Illuminate\Database\Seeder;
use App\Applicant;
use App\Position;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Data TES screening kecocokan (bukan data produksi).
 * BERJALAN MANUAL SAJA: php artisan db:seed --class=ScreeningTestSeeder
 * - Mengisi bahan screening 3 loker (tags/requirement/SIM) bila kosong.
 * - Membuat 5 pelamar tes: cocok / sebagian / kurang / DOC / beda posisi.
 * Hapus data tes: DELETE FROM applicants WHERE email LIKE '%@contoh.id';
 */
class ScreeningTestSeeder extends Seeder
{
    public function run()
    {
        $positions = Position::orderBy('id')->take(3)->get();
        if ($positions->count() < 1) {
            $this->command->error('Isi tabel positions dulu.');
            return;
        }

        // Bahan screening tes (hanya diisi bila kosong — tidak menimpa editan)
        $materials = [
            ['gudang, forklift:3, stock opname:2', "Min. pengalaman 2 tahun di logistik\nWajib SIM B aktif", 'B'],
            ['selling:2, negosiasi:2, target pasar:3', "Min. pengalaman 3 tahun sebagai sales\nWajib SIM A aktif", 'A'],
            ['digital marketing, SEO, konten', "Min. pengalaman 3 tahun di marketing", null],
        ];
        foreach ($positions as $i => $pos) {
            $m = $materials[$i % count($materials)];
            $pos->update([
                'skill_tags' => $pos->skill_tags ?: $m[0],
                'requirements' => $pos->requirements ?: $m[1],
                'syarat_sim' => $pos->syarat_sim ?: $m[2],
            ]);
        }

        Storage::disk('public')->makeDirectory('lamaran');

        $tests = [
            // [nama, jk, hp, domisili, sim, posisi_idx, teks_cv_atau_null, nama_file, status, hari_lalu]
            ['Rina Cocok', 'Perempuan', '081200000011', 'Surabaya', 'B', 0,
                'Rina pengalaman 4 tahun di gudang logistik terbiasa forklift dan stock opname memiliki SIM B aktif',
                'CV_Rina.pdf', 'Baru', 0],
            ['Agus Sebagian', 'Laki-laki', '081200000022', 'Gresik', 'Tidak Punya', 0,
                'Agus pengalaman 1 tahun di gudang logistik administrasi perkantoran umum',
                'CV_Agus.pdf', 'Seleksi', 1],
            ['Dewi Kurang', 'Perempuan', '081200000033', 'Malang', 'Tidak Punya', 0,
                'Dewi lulusan desain grafis mahir photoshop ilustrator pemasaran konten kreatif media sosial',
                'CV_Dewi.pdf', 'Baru', 2],
            ['Budi Doc', 'Laki-laki', '081200000044', 'Sidoarjo', 'A', 0,
                null, 'CV_Budi.doc', 'Baru', 3],
            ['Sinta Sales', 'Perempuan', '081200000055', 'Jakarta', 'A', 1,
                'Sinta pengalaman 5 tahun selling negosiasi target pasar luas memiliki SIM A aktif kendaraan mobil',
                'CV_Sinta.pdf', 'Interview', 1],
        ];

        foreach ($tests as $i => $t) {
            [$nama, $jk, $hp, $dom, $sim, $pIdx, $cvText, $fname, $status, $days] = $t;
            $pos = $positions[$pIdx % $positions->count()];
            $date = Carbon::now()->subDays($days)->setTime(9 + $i, 10);
            $stored = 'lamaran/tes_cv_' . ($i + 1) . '_' . pathinfo($fname, PATHINFO_EXTENSION);

            if ($cvText !== null) {
                Storage::disk('public')->put($stored, $this->makePdf($cvText));
                $mime = 'application/pdf';
                $size = Storage::disk('public')->size($stored);
            } else {
                Storage::disk('public')->put($stored, 'dummy doc content');
                $mime = 'application/msword';
                $size = 512;
            }

            Applicant::create([
                'position_id' => $pos->id,
                'nama_lengkap' => $nama,
                'jenis_kelamin' => $jk,
                'no_hp' => $hp,
                'email' => strtolower(str_replace(' ', '.', $nama)) . '@contoh.id',
                'domisili' => $dom,
                'sim' => $sim,
                'file_path' => $stored,
                'file_original' => $fname,
                'file_mime' => $mime,
                'file_size' => $size,
                'status' => $status,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        $this->command->info('5 data tes screening dibuat.');
    }

    /** PDF minimal valid ber-teks (untuk dites pdfparser). */
    protected function makePdf($text)
    {
        $stream = 'BT /F1 18 Tf 50 750 Td (' . $text . ') Tj ET';
        $objs = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            4 => '<< /Length ' . strlen($stream) . " >> stream\n$stream\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $off = [];
        foreach ($objs as $n => $body) {
            $off[$n] = strlen($pdf);
            $pdf .= "$n 0 obj $body\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref' . "\n" . '0 ' . (count($objs) + 1) . "\n0000000000 65535 f \n";
        for ($n = 1; $n <= count($objs); $n++) {
            $pdf .= sprintf('%010d 00000 n ', $off[$n]) . "\n";
        }
        $pdf .= 'trailer' . "\n" . '<< /Size ' . (count($objs) + 1) . ' /Root 1 0 R >>' . "\nstartxref\n$xref\n%%EOF";
        return $pdf;
    }
}
