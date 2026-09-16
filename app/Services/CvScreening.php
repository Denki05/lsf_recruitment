<?php

namespace App\Services;

use App\Applicant;
use Illuminate\Support\Facades\Storage;

class CvScreening
{
    /**
     * Hitung saran kecocokan kandidat vs input loker.
     * Murni saran — keputusan tetap di tangan HRD.
     * Return: ['scorable'=>bool, 'score'=>int|null, 'matched'=>[], 'missing'=>[], 'note'=>string]
     */
    public function score(Applicant $applicant)
    {
        $applicant->loadMissing('position');
        $position = $applicant->position;

        if (!$position) {
            return $this->result(false, null, [], [], 'Posisi tidak ditemukan.');
        }

        $points = []; // ['label' => 'tag'|'req'|'sim', 'text' => ..., 'needles' => [...]]

        foreach ($position->skill_list as $tag) {
            $points[] = ['label' => $tag, 'needles' => [$tag]];
        }
        foreach ($position->requirement_list as $line) {
            $points[] = ['label' => $this->shorten($line), 'needles' => $this->keywords($line)];
        }
        if (!empty($position->syarat_sim)) {
            $points[] = ['label' => 'SIM ' . $position->syarat_sim, 'needles' => [], 'sim' => $position->syarat_sim];
        }

        if (empty($points)) {
            return $this->result(false, null, [], [], 'Loker belum punya bahan screening (skill tags / requirement / SIM).');
        }

        $ext = strtolower(pathinfo($applicant->file_original, PATHINFO_EXTENSION));
        $isPdf = $ext === 'pdf' || $applicant->file_mime === 'application/pdf';

        $cvText = '';
        if ($isPdf) {
            $cvText = mb_strtolower($this->extractPdf($applicant));
        }

        $matched = [];
        $missing = [];
        $scored = 0;
        $total = 0;

        foreach ($points as $p) {
            // Poin SIM dinilai dari field form (bukan teks CV)
            if (isset($p['sim'])) {
                $total++;
                if (!empty($applicant->sim) && stripos($applicant->sim, $p['sim']) !== false) {
                    $matched[] = $p['label'];
                    $scored++;
                } else {
                    $missing[] = $p['label'];
                }
                continue;
            }
            // Poin teks butuh CV terbaca
            if ($cvText === '') {
                $missing[] = $p['label'];
                $total++;
                continue;
            }
            $total++;
            $need = max(1, (int) ceil(count($p['needles']) / 2)); // min. separuh kata kunci
            if ($this->hits($cvText, $p['needles'], $need)) {
                $matched[] = $p['label'];
                $scored++;
            } else {
                $missing[] = $p['label'];
            }
        }

        if ($cvText === '' && $isPdf) {
            return $this->result(true, (int) round($scored / max(1, $total) * 100), $matched, $missing,
                'Teks CV tidak terbaca (kemungkinan hasil scan). Nilai dihitung dari field SIM saja — baca CV manual.');
        }
        if (!$isPdf) {
            return $this->result(true, (int) round($scored / max(1, $total) * 100), $matched, $missing,
                'Berkas bukan PDF teks — nilai dihitung dari field SIM saja. Untuk nilai penuh, minta CV PDF.');
        }

        return $this->result(true, (int) round($scored / max(1, $total) * 100), $matched, $missing, '');
    }

    protected function result($scorable, $score, $matched, $missing, $note)
    {
        return compact('scorable', 'score', 'matched', 'missing', 'note');
    }

    protected function extractPdf(Applicant $applicant)
    {
        try {
            $full = Storage::disk('public')->path($applicant->file_path);
            if (!is_file($full) || filesize($full) > 8 * 1024 * 1024) {
                return '';
            }
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($full);
            return trim(preg_replace('/\s+/', ' ', $pdf->getText()));
        } catch (\Exception $e) {
            return '';
        }
    }

    /** Kata kunci signifikan dari baris requirement (kata ≥4 huruf). */
    protected function keywords($line)
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($line));
        $out = [];
        foreach ($words as $w) {
            if (mb_strlen($w) >= 4) {
                $out[] = $w;
            }
        }
        return array_values(array_unique($out));
    }

    protected function hits($cvText, array $needles, $need = 1)
    {
        $hit = 0;
        foreach ($needles as $n) {
            if ($n !== '' && mb_strpos($cvText, $n) !== false && ++$hit >= $need) {
                return true;
            }
        }
        return false;
    }

    protected function shorten($line, $len = 42)
    {
        return mb_strlen($line) > $len ? mb_substr($line, 0, $len) . '…' : $line;
    }
}
