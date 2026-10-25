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

        $details = [
            'gaji' => 15000000,
            'pendidikan_minimal' => 'D4/S1',
            'usia_min' => 28,
            'usia_maks' => 45,
            'butuh_lembur' => false,
            'syarat_sim' => null,
            'skill_tags' => 'strategi pemasaran:3, digital marketing:3, brand management:2, riset pasar:2, kepemimpinan:3, manajemen anggaran:2, campaign management:2, analisis data:2, komunikasi:2',
            'description' => "Merancang, mengimplementasikan, dan mengevaluasi strategi pemasaran perusahaan secara menyeluruh.\nMemimpin dan mengembangkan tim marketing agar target perusahaan tercapai.\nMengelola anggaran pemasaran serta memastikan efisiensi dan efektivitas kampanye.\nMelakukan riset pasar dan analisis kompetitor untuk menemukan peluang pertumbuhan.\nMembangun dan menjaga konsistensi brand di seluruh kanal komunikasi.\nMengawasi kampanye digital, media sosial, dan aktivitas promosi lainnya.\nMenganalisis KPI pemasaran serta menyusun laporan berkala kepada manajemen.\nBerkolaborasi dengan tim sales dan product untuk menyelaraskan strategi go-to-market.\nMenjalin hubungan dengan vendor, agensi, dan mitra strategis.",
            'requirements' => "Pendidikan minimal S1 Marketing, Manajemen, Bisnis, atau bidang terkait.\nPengalaman minimal 5 tahun di bidang marketing, dengan minimal 2 tahun sebagai Manajer atau Supervisor.\nMemahami strategi pemasaran konvensional dan digital (social media, SEO/SEM, content marketing).\nMampu menyusun dan mengelola anggaran pemasaran.\nMemiliki kemampuan analisis pasar, data, dan evaluasi KPI.\nKepemimpinan, komunikasi, dan negosiasi yang baik.\nKreatif, inisiatif, dan mampu bekerja dengan target.\nBersedia ditempatkan di Surabaya.",
            'benefits' => "BPJS Kesehatan & Ketenagakerjaan.\nGaji kompetitif + bonus kinerja bulanan.\nTunjangan transport dan komunikasi.\nCuti tahunan 12 hari.\nLingkungan kerja suportif dengan jenjang karier yang jelas.",
        ];

        if (!$position->exists) {
            $position->fill($details);
            $position->is_active = true;
        } else {
            // Lengkapi contoh lama yang masih placeholder tanpa menimpa
            // deskripsi/kualifikasi yang sudah diedit admin.
            if (trim((string) $position->requirements) === ''
                || strpos((string) $position->requirements, 'Kualifikasi menyusul') !== false) {
                $position->description = $details['description'];
                $position->requirements = $details['requirements'];
                foreach (['gaji', 'pendidikan_minimal', 'usia_min', 'usia_maks', 'skill_tags'] as $field) {
                    if (empty($position->$field)) {
                        $position->$field = $details[$field];
                    }
                }
            }
        }

        if (empty($position->benefits)) {
            $position->benefits = $details['benefits'];
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
