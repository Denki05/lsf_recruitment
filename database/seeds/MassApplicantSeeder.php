<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Applicant;
use App\Position;
use App\User;

/**
 * Data DUMMY pelamar dalam jumlah banyak (BUKAN data produksi) untuk uji tampilan,
 * filter, skoring, bulk action, dan performa.
 *
 * Jalankan manual:  php artisan db:seed --class=MassApplicantSeeder
 * Jumlah default 300; ubah lewat $total di bawah atau env SEED_TOTAL.
 * Bersihkan saja:   SEED_CLEAN=1 (hapus semua data dummy tanpa membuat baru)
 * Idempoten: data dummy lama (email @dummy.contoh.id) + file CV-nya dihapus dulu.
 */
class MassApplicantSeeder extends Seeder
{
    protected $total = 300;

    const MARKER = '%@dummy.contoh.id';

    protected $usedPhones = [];
    protected $people = [];

    protected $mFirst = ['Ahmad', 'Budi', 'Agus', 'Andi', 'Bagus', 'Bayu', 'Dedi', 'Dimas', 'Eko', 'Fajar', 'Faisal', 'Gilang', 'Hendra', 'Heru', 'Ilham', 'Joko', 'Krisna', 'Lukman', 'Mochamad', 'Nanda', 'Oki', 'Putra', 'Rangga', 'Rizki', 'Satria', 'Teguh', 'Wahyu', 'Yoga', 'Yusuf', 'Zainal'];
    protected $fFirst = ['Ayu', 'Amelia', 'Anisa', 'Bella', 'Citra', 'Dewi', 'Dina', 'Eka', 'Fitri', 'Gita', 'Indah', 'Intan', 'Kartika', 'Lestari', 'Maya', 'Nabila', 'Novi', 'Putri', 'Rina', 'Sari', 'Sinta', 'Tiara', 'Utami', 'Vina', 'Wulan', 'Yuni', 'Zahra', 'Rahma', 'Lina', 'Ratna'];
    protected $last   = ['Saputra', 'Pratama', 'Wijaya', 'Santoso', 'Hidayat', 'Nugroho', 'Setiawan', 'Kurniawan', 'Ramadhan', 'Permana', 'Wibowo', 'Susanto', 'Firmansyah', 'Maulana', 'Hartono', 'Prasetyo', 'Utomo', 'Puspita', 'Anggraini', 'Rahayu', 'Handayani', 'Wulandari', 'Kusuma', 'Purnama', 'Siregar', 'Nasution', 'Hakim', 'Fauzi'];
    protected $companies = ['PT Sinar Abadi', 'CV Maju Jaya', 'PT Sumber Rejeki', 'UD Berkah Makmur', 'PT Nusantara Logistik', 'PT Cahaya Teknologi', 'CV Mitra Solusi', 'Toko Sinar Baru', 'PT Karya Mandiri', 'PT Prima Sentosa', 'PT Global Data Solusi', 'CV Digital Nusantara'];
    protected $prefixes = ['811', '812', '813', '821', '822', '823', '851', '852', '853', '856', '857', '858', '877', '878', '881', '895', '896', '897', '898', '899'];

    protected $weakJobs  = ['Admin Kantor', 'Staff Administrasi', 'Petugas Arsip', 'Kasir Toko'];
    protected $weakTasks = ['input data harian', 'mengarsip dokumen', 'membuat laporan sederhana', 'melayani pelanggan'];
    protected $offJobs   = ['Sales Executive', 'Desainer Grafis', 'Kasir Minimarket', 'Customer Service', 'Staff Marketing', 'Content Creator'];
    protected $offTasks  = ['mencapai target penjualan', 'membuat desain promosi', 'melayani keluhan pelanggan', 'mengelola media sosial', 'follow up calon pelanggan'];
    protected $offSkills = ['penjualan', 'desain grafis', 'photoshop', 'kasir', 'pemasaran konten', 'media sosial', 'customer service', 'negosiasi'];

    public function run()
    {
        Storage::disk('local')->makeDirectory('lamaran');
        $this->cleanup();
        if (getenv('SEED_CLEAN')) {
            $this->command->info('Data dummy lama dibersihkan.');
            return;
        }

        $total = (int) (getenv('SEED_TOTAL') ?: $this->total);
        if ($total < 1) {
            $total = $this->total;
        }

        $titles = [
            'it'     => 'IT Application Support',
            'staf'   => 'Staf Information Technology',
            'gudang' => 'Koordinator Gudang & GA',
        ];
        $pos = [];
        foreach ($titles as $k => $title) {
            $pos[$k] = Position::where('title', $title)->first();
            if (!$pos[$k]) {
                $this->command->error("Loker '$title' tidak ditemukan (judul harus sama seperti di PositionsTableSeeder).");
                return;
            }
        }

        $hasExp  = Schema::hasColumn('applicants', 'pengalaman_kerja');
        $hasLogs = Schema::hasTable('applicant_status_logs');
        $adminId = User::where('is_superadmin', true)->value('id');
        $profiles = $this->profiles();
        $now = Carbon::now();

        for ($i = 1; $i <= $total; $i++) {
            $key  = $this->weighted(['it' => 35, 'staf' => 30, 'gudang' => 35]);
            $prof = $profiles[$key];

            // ~6%: orang yang sama melamar loker lain (untuk "Riwayat Pelamar Ini")
            $person = null;
            if (count($this->people) > 10 && $this->chance(6)) {
                $cand = $this->pick($this->people);
                if ($cand['key'] !== $key) {
                    $person = $cand;
                }
            }
            if (!$person) {
                $person = $this->newPerson($prof, $key);
                $this->people[] = $person;
            }

            $tier = $this->weighted(['strong' => 25, 'medium' => 40, 'weak' => 20, 'off' => 15]);
            $cvText = $this->buildCv($tier, $prof, $person);
            $status = $this->weighted($this->statusWeights($tier));
            $created = $this->randomDate($now);

            // Berkas: 88% PDF asli, sisanya DOCX/DOC dummy
            $r = mt_rand(1, 100);
            if ($r <= 88) {
                $ext = 'pdf';
                $mime = 'application/pdf';
                $content = $this->makePdf($cvText);
            } elseif ($r <= 96) {
                $ext = 'docx';
                $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                $content = 'dummy docx content';
            } else {
                $ext = 'doc';
                $mime = 'application/msword';
                $content = 'dummy doc content';
            }
            $stored = 'lamaran/seed_' . str_pad((string) $i, 4, '0', STR_PAD_LEFT) . '_' . Str::random(8) . '.' . $ext;
            Storage::disk('local')->put($stored, $content);

            $a = new Applicant();
            $a->position_id     = $pos[$key]->id;
            $a->nama_lengkap    = $person['nama'];
            $a->jenis_kelamin   = $person['jk'];
            $a->tanggal_lahir   = $person['lahir'];
            $a->no_hp           = $person['hp'];
            $a->email           = $person['email'];
            $a->domisili        = $person['dom'];
            $a->education       = $person['edu'];
            $a->sim             = $person['sim'];
            $a->expected_salary = (int) (round($prof['salary'] * mt_rand(70, 150) / 100 / 100000) * 100000);
            $a->willing_overtime = $this->chance($prof['overtime']);
            if ($hasExp) {
                $a->pengalaman_kerja = $this->buildExperience($tier, $prof);
            }
            $a->file_path     = $stored;
            $a->file_original = 'CV_' . str_replace(' ', '_', $person['nama']) . '.' . $ext;
            $a->file_mime     = $mime;
            $a->file_size     = strlen($content);
            $a->ai_consent    = true;
            $a->status        = 'Baru';
            $a->created_at    = $created;
            $a->updated_at    = $created;

            // Riwayat status (jika tabelnya ada)
            $logs = [];
            $last = $created->copy();
            $note = null;
            foreach ($this->path($status) as $step) {
                $from = $step[0];
                $to = $step[1];
                $last = $last->copy()->addHours(mt_rand(6, 60));
                if ($last->gt($now)) {
                    $last = $now->copy();
                }
                $note = $this->noteFor($to);
                $logs[] = [
                    'from_status' => $from,
                    'to_status'   => $to,
                    'note'        => $note,
                    'created_at'  => $last->format('Y-m-d H:i:s'),
                    'updated_at'  => $last->format('Y-m-d H:i:s'),
                ];
            }
            if ($logs) {
                $a->status = $status;
                $a->catatan_admin = $note;
                $a->updated_at = $last;
            }
            $a->save();

            if ($hasLogs && $logs) {
                foreach ($logs as &$l) {
                    $l['applicant_id'] = $a->id;
                    $l['user_id'] = $adminId;
                }
                unset($l);
                DB::table('applicant_status_logs')->insert($logs);
            }

            if ($i % 100 === 0) {
                $this->command->info("... $i / $total pelamar dibuat");
            }
        }

        $this->command->info("Selesai: $total lamaran dummy dibuat (email @dummy.contoh.id).");
        foreach (Applicant::where('email', 'like', self::MARKER)->select('status', DB::raw('count(*) as c'))->groupBy('status')->get() as $row) {
            $this->command->line('  ' . $row->status . ': ' . $row->c);
        }
    }

    /* ---------- pembersihan ---------- */

    protected function cleanup()
    {
        $old = Applicant::where('email', 'like', self::MARKER)->get(['id', 'file_path']);
        foreach ($old as $o) {
            if ($o->file_path) {
                Storage::disk('local')->delete($o->file_path);
            }
        }
        Applicant::where('email', 'like', self::MARKER)->delete();
    }

    /* ---------- profil per loker ---------- */

    protected function profiles()
    {
        return [
            'it' => [
                'age' => [21, 38], 'salary' => 5500000, 'overtime' => 55,
                'edu' => ['SMA/SMK' => 15, 'D3' => 30, 'D4/S1' => 45, 'S2' => 5],
                'sim' => ['Tidak Punya' => 30, 'C' => 40, 'A' => 10, 'B' => 15, 'A dan C' => 5],
                'strong' => ['application support', 'helpdesk', 'troubleshooting aplikasi', 'dukungan pengguna', 'ticketing', 'sql dasar', 'instalasi software', 'remote support', 'dokumentasi teknis', 'sistem informasi', 'uat testing', 'jaringan dasar'],
                'weak' => ['microsoft office', 'administrasi perkantoran', 'data entry', 'instalasi windows', 'komputer dasar'],
                'jobs' => ['IT Support', 'Helpdesk Staff', 'Application Support', 'Admin Sistem', 'Teknisi Komputer'],
                'tasks' => ['menangani tiket keluhan pengguna', 'troubleshooting error aplikasi', 'instalasi dan update software', 'remote support karyawan', 'membuat dokumentasi panduan pengguna', 'memonitor performa aplikasi'],
            ],
            'staf' => [
                'age' => [21, 38], 'salary' => 5000000, 'overtime' => 50,
                'edu' => ['SMA/SMK' => 15, 'D3' => 30, 'D4/S1' => 45, 'S2' => 5],
                'sim' => ['Tidak Punya' => 30, 'C' => 40, 'A' => 10, 'B' => 15, 'A dan C' => 5],
                'strong' => ['administrasi database', 'sql server', 'mysql', 'erp', 'jaringan lan wan', 'troubleshooting hardware', 'sistem informasi', 'backup restore data', 'inventaris aset it', 'server windows', 'migrasi data', 'dukungan teknis komputer'],
                'weak' => ['microsoft office', 'administrasi perkantoran', 'data entry', 'instalasi windows', 'komputer dasar'],
                'jobs' => ['IT Staff', 'Teknisi Jaringan', 'Database Admin Junior', 'Staff ERP', 'IT Support'],
                'tasks' => ['administrasi dan backup database', 'konfigurasi jaringan kantor', 'perawatan hardware dan printer', 'support pengguna ERP', 'inventaris aset IT', 'migrasi dan validasi data'],
            ],
            'gudang' => [
                'age' => [23, 45], 'salary' => 6000000, 'overtime' => 70,
                'edu' => ['SMP' => 8, 'SMA/SMK' => 60, 'D3' => 17, 'D4/S1' => 15],
                'sim' => ['B' => 55, 'C' => 15, 'A' => 5, 'Tidak Punya' => 25],
                'strong' => ['gudang', 'forklift', 'stock opname', 'logistik', 'bongkar muat', 'inventaris barang', 'admin gudang', 'general affair', 'sistem fifo', 'picking packing', 'pengiriman barang', 'sim b aktif', 'kerja shift'],
                'weak' => ['administrasi', 'pengarsipan', 'kebersihan kantor', 'keamanan gedung', 'kasir toko'],
                'jobs' => ['Staff Gudang', 'Admin Gudang', 'Checker Gudang', 'Operator Forklift', 'Koordinator Logistik', 'Supervisor Gudang'],
                'tasks' => ['cek stok barang masuk dan keluar', 'operasikan forklift', 'stock opname bulanan', 'atur tata letak gudang', 'koordinasi pengiriman barang', 'input data inventaris', 'pengawasan shift gudang'],
            ],
        ];
    }

    protected function statusWeights($tier)
    {
        switch ($tier) {
            case 'strong':
                return ['Baru' => 30, 'Seleksi' => 30, 'Interview' => 22, 'Diterima' => 10, 'Ditolak' => 8];
            case 'medium':
                return ['Baru' => 55, 'Seleksi' => 22, 'Interview' => 10, 'Diterima' => 3, 'Ditolak' => 10];
            case 'weak':
                return ['Baru' => 65, 'Seleksi' => 10, 'Interview' => 3, 'Ditolak' => 22];
            default:
                return ['Baru' => 60, 'Seleksi' => 3, 'Ditolak' => 37];
        }
    }

    protected function path($status)
    {
        switch ($status) {
            case 'Seleksi':
                return [['Baru', 'Seleksi']];
            case 'Interview':
                return [['Baru', 'Seleksi'], ['Seleksi', 'Interview']];
            case 'Diterima':
                return [['Baru', 'Seleksi'], ['Seleksi', 'Interview'], ['Interview', 'Diterima']];
            case 'Ditolak':
                $from = $this->pick(['Baru', 'Seleksi', 'Interview']);
                $p = [];
                if ($from !== 'Baru') {
                    $p[] = ['Baru', 'Seleksi'];
                }
                if ($from === 'Interview') {
                    $p[] = ['Seleksi', 'Interview'];
                }
                $p[] = [$from, 'Ditolak'];
                return $p;
            default:
                return [];
        }
    }

    protected function noteFor($to)
    {
        $map = [
            'Seleksi'   => ['Berkas sesuai, lanjut seleksi', 'Profil cocok dengan kualifikasi', 'Masuk shortlist'],
            'Interview' => ['Panggil interview via WA', 'Jadwal interview minggu ini'],
            'Diterima'  => ['Lolos interview, siap onboarding', 'Setuju dengan penawaran'],
            'Ditolak'   => ['Tidak sesuai kualifikasi', 'Gaji tidak sesuai budget', 'Tidak bisa dihubungi', 'Posisi sudah terisi'],
        ];
        return $this->chance(70) ? $this->pick($map[$to]) : null;
    }

    /* ---------- pembangun data ---------- */

    protected function newPerson($prof, $key)
    {
        $female = $this->chance($key === 'gudang' ? 25 : 40);
        $pool = $female ? $this->fFirst : $this->mFirst;
        $first = $this->pick($pool);
        $mid = $this->chance(45) ? ' ' . $this->pick($pool) : '';
        if ($mid === ' ' . $first) {
            $mid = '';
        }
        $nama = $first . $mid . ' ' . $this->pick($this->last);

        do {
            $hp = '0' . $this->pick($this->prefixes) . str_pad((string) mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        } while (isset($this->usedPhones[$hp]) || Applicant::where('no_hp', $hp)->exists());
        $this->usedPhones[$hp] = true;

        $age = mt_rand($prof['age'][0], $prof['age'][1]);
        $lahir = Carbon::now()->subYears($age)->subDays(mt_rand(0, 364))->format('Y-m-d');

        return [
            'key'   => $key,
            'nama'  => $nama,
            'jk'    => $female ? 'Perempuan' : 'Laki-laki',
            'lahir' => $lahir,
            'hp'    => $hp,
            'dom'   => $this->weighted(['Surabaya' => 45, 'Sidoarjo' => 15, 'Gresik' => 10, 'Mojokerto' => 5, 'Malang' => 6, 'Lamongan' => 4, 'Pasuruan' => 4, 'Jombang' => 3, 'Bangkalan' => 3, 'Jakarta' => 3, 'Semarang' => 2]),
            'email' => Str::slug($nama, '.') . mt_rand(10, 999) . '@dummy.contoh.id',
            'edu'   => $this->weighted($prof['edu']),
            'sim'   => $this->weighted($prof['sim']),
        ];
    }

    protected function buildCv($tier, $prof, $person)
    {
        switch ($tier) {
            case 'strong':
                $kw = array_merge($this->pickMany($prof['strong'], mt_rand(6, 9)), $this->pickMany($prof['weak'], mt_rand(1, 2)));
                $years = mt_rand(3, 8);
                break;
            case 'medium':
                $kw = array_merge($this->pickMany($prof['strong'], mt_rand(3, 5)), $this->pickMany($prof['weak'], 2));
                $years = mt_rand(1, 3);
                break;
            case 'weak':
                $kw = array_merge($this->pickMany($prof['strong'], mt_rand(0, 2)), $this->pickMany($prof['weak'], 3));
                $years = 0;
                break;
            default:
                $kw = array_merge($this->pickMany($this->offSkills, 4), $this->pickMany($prof['weak'], 1));
                $years = mt_rand(1, 4);
        }
        shuffle($kw);
        return 'CURRICULUM VITAE ' . $person['nama'] . '. Domisili ' . $person['dom'] . '. '
            . ($years > 0 ? "Pengalaman $years tahun. " : 'Fresh graduate. ')
            . 'Keahlian: ' . implode(', ', $kw) . '. Bersedia ditempatkan sesuai kebutuhan perusahaan.';
    }

    protected function buildExperience($tier, $prof)
    {
        if ($tier === 'weak' && $this->chance(40)) {
            return 'Belum memiliki pengalaman kerja.';
        }
        $n = $tier === 'strong' ? mt_rand(2, 3) : ($tier === 'medium' ? mt_rand(1, 2) : 1);
        $jobs  = $tier === 'off' ? $this->offJobs : ($tier === 'weak' ? $this->weakJobs : $prof['jobs']);
        $tasks = $tier === 'off' ? $this->offTasks : ($tier === 'weak' ? $this->weakTasks : $prof['tasks']);

        $year = mt_rand(2016, 2022);
        $entries = [];
        for ($k = 1; $k <= $n; $k++) {
            $start = min($year, 2025);
            $end = min(2026, $start + mt_rand(1, 3));
            $year = $end;
            $period = $start . ' - ' . (($k === $n && $this->chance(30)) ? 'sekarang' : $end);
            $entries[] = $this->pick($this->companies) . ' - ' . $this->pick($jobs) . ' (' . $period . ")\n   Tugas: "
                . implode(', ', $this->pickMany($tasks, mt_rand(2, 3))) . '.';
        }
        $entries = array_reverse($entries); // terbaru di atas
        $lines = [];
        foreach ($entries as $idx => $e) {
            $lines[] = ($idx + 1) . '. ' . $e;
        }
        return implode("\n", $lines);
    }

    protected function randomDate(Carbon $now)
    {
        $d = $now->copy()->subDays(mt_rand(0, 45))->setTime(mt_rand(8, 21), mt_rand(0, 59), 0);
        if ($d->gt($now)) {
            $d->subDay();
        }
        return $d;
    }

    /* ---------- PDF minimal ber-teks (bisa dibaca pdfparser) ---------- */

    protected function makePdf($text)
    {
        $lines = explode("\n", wordwrap($text, 78, "\n", true));
        $stream = "BT /F1 11 Tf 14 TL 50 750 Td\n";
        foreach ($lines as $l) {
            $stream .= '(' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $l) . ") Tj T*\n";
        }
        $stream .= 'ET';
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
        $pdf .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
        for ($n = 1; $n <= count($objs); $n++) {
            $pdf .= sprintf('%010d 00000 n ', $off[$n]) . "\n";
        }
        $pdf .= "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        return $pdf;
    }

    /* ---------- helper acak ---------- */

    protected function pick(array $a)
    {
        return $a[array_rand($a)];
    }

    protected function pickMany(array $a, $n)
    {
        $n = max(0, min($n, count($a)));
        if ($n === 0) {
            return [];
        }
        $keys = (array) array_rand($a, $n);
        $out = [];
        foreach ($keys as $k) {
            $out[] = $a[$k];
        }
        return $out;
    }

    protected function chance($percent)
    {
        return mt_rand(1, 100) <= $percent;
    }

    protected function weighted(array $map)
    {
        $r = mt_rand(1, array_sum($map));
        foreach ($map as $k => $w) {
            $r -= $w;
            if ($r <= 0) {
                return $k;
            }
        }
        return key($map);
    }
}