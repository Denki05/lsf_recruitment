<?php

use Illuminate\Database\Seeder;
use App\Applicant;
use App\Position;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Data TES screening kecocokan (bukan data produksi).
 * BERJALAN MANUAL SAJA: php artisan db:seed --class=ScreeningTestSeeder
 * - Idempoten: hapus dulu baris tes lama (@contoh.id), lalu buat 12 baru.
 * - 12 pelamar tersebar di 3 loker utama, umur/gaji/pendidikan/SIM/lembur
 *   bervariasi agar filter screening bisa didemo.
 * Hapus data tes: DELETE FROM applicants WHERE email LIKE '%@contoh.id';
 */
class ScreeningTestSeeder extends Seeder
{
    public function run()
    {
        $byTitle = function ($title) {
            return Position::where('title', $title)->first() ?: Position::orderBy('id')->first();
        };
        $itSupport = $byTitle('IT Application Support');
        $stafIt = $byTitle('Staf Information Technology');
        $gudang = $byTitle('Koordinator Gudang & GA');
        if (!$itSupport || !$stafIt || !$gudang) {
            $this->command->error('Isi tabel positions dulu (3 loker utama).');
            return;
        }

        // Bersihkan tes lama agar bisa dijalankan ulang tanpa ganda
        Applicant::where('email', 'like', '%@contoh.id')->delete();
        Storage::disk('public')->makeDirectory('lamaran');

        // [nama, jk, hp, domisili, sim, posisi, cv_text/null, file, status, hari_lalu, lahir, gaji, didik, lembur]
        $tests = [
            ['Rina Cocok', 'Perempuan', '081200000011', 'Surabaya', 'B', 'it',
                'Rina pengalaman 4 tahun application support helpdesk troubleshooting aplikasi error dukungan pengguna',
                'CV_Rina.pdf', 'Baru', 0, '1998-05-12', 5000000, 'D3', true],
            ['Budi Doc', 'Laki-laki', '081200000044', 'Sidoarjo', 'A', 'it',
                null, 'CV_Budi.doc', 'Baru', 3, '1990-11-30', 7000000, 'SMP', true],
            ['Fajar Nugroho', 'Laki-laki', '081200000066', 'Surabaya', 'C', 'it',
                'Fajar fresh graduate sistem informasi magang helpdesk troubleshooting komputer jaringan dasar',
                'CV_Fajar.pdf', 'Baru', 1, '2002-03-08', 4000000, 'D4/S1', false],
            ['Maya Putri', 'Perempuan', '081200000077', 'Sidoarjo', 'Tidak Punya', 'it',
                'Maya pengalaman akuntansi keuangan laporan pajak mahir spreadsheet administrasi perkantoran',
                'CV_Maya.pdf', 'Baru', 2, '1999-12-01', 4500000, 'D3', false],
            ['Dimas Prasetyo', 'Laki-laki', '081200000088', 'Surabaya', 'C', 'staf',
                'Dimas pengalaman 3 tahun database administrasi sistem informasi troubleshooting jaringan ERP implementasi migrasi data',
                'CV_Dimas.pdf', 'Seleksi', 0, '1994-06-17', 5500000, 'D4/S1', true],
            ['Lina Marlina', 'Perempuan', '081200000099', 'Gresik', 'Tidak Punya', 'staf',
                'Lina pengalaman support ERP bantuan pengguna pengujian sistem dokumentasi teknis inventaris aset',
                'CV_Lina.pdf', 'Baru', 1, '1997-01-25', 4800000, 'D3', true],
            ['Eko Saputra', 'Laki-laki', '081200000100', 'Mojokerto', 'C', 'staf',
                'Eko teknisi jaringan instalasi konfigurasi perangkat keras printer pemeliharaan komputer dasar',
                'CV_Eko.pdf', 'Baru', 4, '1991-04-11', 5200000, 'SMA/SMK', false],
            ['Sinta Sales', 'Perempuan', '081200000055', 'Jakarta', 'A', 'staf',
                'Sinta pengalaman 5 tahun selling negosiasi target pasar luas memiliki SIM A aktif kendaraan mobil',
                'CV_Sinta.pdf', 'Interview', 1, '1993-07-15', 8000000, 'D4/S1', true],
            ['Hendra Gunawan', 'Laki-laki', '081200000111', 'Surabaya', 'B', 'gudang',
                'Hendra pengalaman 5 tahun gudang logistik forklift stock opname bongkar muat shift memiliki SIM B aktif',
                'CV_Hendra.pdf', 'Baru', 0, '1989-08-19', 6000000, 'SMA/SMK', true],
            ['Agus Sebagian', 'Laki-laki', '081200000022', 'Gresik', 'Tidak Punya', 'gudang',
                'Agus pengalaman 1 tahun di gudang logistik administrasi perkantoran umum',
                'CV_Agus.pdf', 'Seleksi', 1, '1995-09-03', 4500000, 'SMA/SMK', false],
            ['Ratna Sari', 'Perempuan', '081200000122', 'Surabaya', 'Tidak Punya', 'gudang',
                'Ratna pengalaman administrasi arsip data entry general affair perizinan operasional kantor',
                'CV_Ratna.pdf', 'Baru', 2, '1996-10-05', 4200000, 'D3', false],
            ['Dewi Kurang', 'Perempuan', '081200000033', 'Malang', 'Tidak Punya', 'gudang',
                'Dewi lulusan desain grafis mahir photoshop ilustrator pemasaran konten kreatif media sosial',
                'CV_Dewi.pdf', 'Baru', 5, '2001-02-20', 3500000, 'D4/S1', false],
        ];
        $posMap = ['it' => $itSupport, 'staf' => $stafIt, 'gudang' => $gudang];

        foreach ($tests as $i => $t) {
            [$nama, $jk, $hp, $dom, $sim, $pKey, $cvText, $fname, $status, $days, $lahir, $gaji, $didik, $lembur] = $t;
            $pos = $posMap[$pKey];
            $date = Carbon::now()->subDays($days)->setTime(9 + ($i % 8), 10);
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
                'tanggal_lahir' => $lahir,
                'no_hp' => $hp,
                'email' => strtolower(str_replace(' ', '.', $nama)) . '@contoh.id',
                'domisili' => $dom,
                'expected_salary' => $gaji,
                'education' => $didik,
                'willing_overtime' => $lembur,
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

        $this->command->info('12 data tes screening dibuat.');
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
