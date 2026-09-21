<?php

namespace App\Services;

use App\Applicant;
use Illuminate\Support\Facades\Storage;
use Sastrawi\Stemmer\StemmerFactory;

class CvScreening
{
    protected static $stemmer;

    /**
     * Saran kecocokan v2: bobot + stemming Indonesia + toleransi typo + multi-format.
     * Murni saran — keputusan tetap di tangan HRD.
     * Return: ['scorable'=>bool, 'score'=>int|null, 'matched'=>[], 'missing'=>[], 'note'=>string]
     * matched/missing: ['label' => ..., 'weight' => ...]
     */
    public function score(Applicant $applicant)
    {
        $applicant->loadMissing('position');
        $position = $applicant->position;

        if (!$position) {
            return $this->result(false, null, [], [], 'Posisi tidak ditemukan.');
        }

        $points = []; // ['label'=>, 'weight'=>, 'needles'=>[], 'need'=>int|null, 'ratio'=>float|null, 'sim'=>?]
        // Bonus: skill tags bila admin (pernah) mengisinya
        foreach ($position->skill_list as $s) {
            $points[] = ['label' => $s['tag'], 'weight' => $s['weight'], 'needles' => [$s['tag']], 'need' => 1];
        }
        // Otomatis: tiap baris requirement (bobot 2)
        foreach ($position->requirement_list as $line) {
            $points[] = ['label' => $this->shorten($line), 'weight' => 2, 'needles' => $this->keywords($line), 'need' => null, 'ratio' => 0.5];
        }
        // Otomatis: kesesuaian deskripsi (bobot 2, cocok bila ≥40% kata penting kena)
        $descKeys = $this->keywords($position->description ?: '', 5, 30);
        if (!empty($descKeys)) {
            $points[] = ['label' => 'Kesesuaian deskripsi', 'weight' => 2, 'needles' => $descKeys, 'need' => null, 'ratio' => 0.4];
        }
        if (!empty($position->sim_list)) {
            $points[] = ['label' => 'SIM ' . implode(', ', $position->sim_list), 'weight' => 2, 'needles' => [], 'sim_any' => $position->sim_list];
        } elseif (!empty($position->syarat_sim)) {
            $points[] = ['label' => 'SIM ' . $position->syarat_sim, 'weight' => 2, 'needles' => [], 'sim' => $position->syarat_sim];
        }

        if (empty($points)) {
            return $this->result(false, null, [], [], 'Loker belum punya bahan screening (skill tags / requirement / SIM).');
        }

        $read = $this->readCvText($applicant);
        $cvText = $read['text'];

        $matched = [];
        $missing = [];
        $got = 0;
        $total = 0;

        foreach ($points as $p) {
            $total += $p['weight'];
            // "Tidak Punya" tidak boleh cocok dengan huruf A (substring) — anggap tidak punya SIM
            $hasSim = !empty($applicant->sim) && strtolower(trim($applicant->sim)) !== 'tidak punya';
            if (isset($p['sim_any'])) {
                $has = false;
                if ($hasSim) {
                    foreach ((array) $p['sim_any'] as $need) {
                        if (stripos($applicant->sim, $need) !== false) {
                            $has = true;
                            break;
                        }
                    }
                }
                if ($has) {
                    $matched[] = $this->chip($p);
                    $got += $p['weight'];
                } else {
                    $missing[] = $this->chip($p);
                }
                continue;
            }
            if (isset($p['sim'])) {
                if ($hasSim && stripos($applicant->sim, $p['sim']) !== false) {
                    $matched[] = $this->chip($p);
                    $got += $p['weight'];
                } else {
                    $missing[] = $this->chip($p);
                }
                continue;
            }
            if ($cvText === '') {
                $missing[] = $this->chip($p);
                continue;
            }
            $need = isset($p['need']) && $p['need'] !== null
                ? $p['need']
                : max(1, (int) ceil(count($p['needles']) * (isset($p['ratio']) ? $p['ratio'] : 0.5)));
            if ($this->hits($cvText, $p['needles'], $need)) {
                $matched[] = $this->chip($p);
                $got += $p['weight'];
            } else {
                $missing[] = $this->chip($p);
            }
        }

        $score = (int) round($got / max(1, $total) * 100);
        if ($read['partial']) {
            return $this->result(true, $score, $matched, $missing, $read['note']);
        }
        return $this->result(true, $score, $matched, $missing, '');
    }

    protected function chip($p)
    {
        return ['label' => $p['label'], 'weight' => $p['weight']];
    }

    protected function result($scorable, $score, $matched, $missing, $note)
    {
        return compact('scorable', 'score', 'matched', 'missing', 'note');
    }

    /**
     * Baca teks CV: PDF teks, DOCX, atau PDF di dalam ZIP.
     * Return ['text' => stemmed string ('' bila gagal), 'partial' => bool, 'note' => string]
     */
    public function getCvText(Applicant $applicant)
    {
        return $this->readCvText($applicant);
    }

    /** Teks CV mentah (untuk AI) — '' bila tidak bisa dibaca. */
    public function getCvRawText(Applicant $applicant)
    {
        $ext = strtolower(pathinfo($applicant->file_original, PATHINFO_EXTENSION));
        $mime = strtolower($applicant->file_mime ?: '');
        if ($ext === 'zip') {
            return $this->extractZipPdf($applicant);
        }
        if ($ext === 'docx' || $mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            return $this->extractDocx($applicant);
        }
        if ($ext === 'pdf' || $mime === 'application/pdf') {
            return $this->extractPdf($applicant);
        }
        return '';
    }

    protected function readCvText(Applicant $applicant)
    {
        $ext = strtolower(pathinfo($applicant->file_original, PATHINFO_EXTENSION));
        $mime = strtolower($applicant->file_mime ?: '');

        if ($ext === 'zip') {
            $text = $this->extractZipPdf($applicant);
            if ($text === '') {
                return ['text' => '', 'partial' => true, 'note' => 'ZIP tidak berisi PDF berteks — nilai dari field SIM saja.'];
            }
            return ['text' => $this->stem($text), 'partial' => false, 'note' => ''];
        }

        if ($ext === 'docx' || $mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $text = $this->extractDocx($applicant);
            if ($text === '') {
                return ['text' => '', 'partial' => true, 'note' => 'Teks DOCX tidak terbaca — nilai dari field SIM saja.'];
            }
            return ['text' => $this->stem($text), 'partial' => false, 'note' => ''];
        }

        if ($ext === 'pdf' || $mime === 'application/pdf') {
            $text = $this->extractPdf($applicant);
            if ($text === '') {
                return ['text' => '', 'partial' => true, 'note' => 'Teks CV tidak terbaca (kemungkinan hasil scan) — nilai dari field SIM saja.'];
            }
            return ['text' => $this->stem($text), 'partial' => false, 'note' => ''];
        }

        return ['text' => '', 'partial' => true, 'note' => 'Berkas bukan PDF/DOCX/ZIP berteks — nilai dari field SIM saja.'];
    }

    /** Batas ukuran isi file setelah di-ekstrak dari ZIP/DOCX (anti zip bomb). */
    const MAX_UNZIPPED_BYTES = 15 * 1024 * 1024;

    protected function zipEntryIsSafe(\ZipArchive $zip, $name)
    {
        $stat = $zip->statName($name);
        return $stat && $stat['size'] <= self::MAX_UNZIPPED_BYTES;
    }

    protected function fullPath(Applicant $applicant)
    {
        $full = Storage::disk('local')->path($applicant->file_path);
        if (!is_file($full) || filesize($full) > 8 * 1024 * 1024) {
            return null;
        }
        return $full;
    }

    protected function extractPdf(Applicant $applicant)
    {
        try {
            if (!$full = $this->fullPath($applicant)) {
                return '';
            }
            $parser = new \Smalot\PdfParser\Parser();
            return trim(preg_replace('/\s+/', ' ', $parser->parseFile($full)->getText()));
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function extractDocx(Applicant $applicant)
    {
        try {
            if (!$full = $this->fullPath($applicant)) {
                return '';
            }
            $zip = new \ZipArchive();
            if ($zip->open($full) !== true) {
                return '';
            }
            $xml = $this->zipEntryIsSafe($zip, 'word/document.xml')
                ? $zip->getFromName('word/document.xml', self::MAX_UNZIPPED_BYTES)
                : false;
            $zip->close();
            if (!$xml) {
                return '';
            }
            $text = html_entity_decode(strip_tags(str_replace(['</w:p>', '</w:t>'], ' ', $xml)));
            return trim(preg_replace('/\s+/', ' ', $text));
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function extractZipPdf(Applicant $applicant)
    {
        try {
            if (!$full = $this->fullPath($applicant)) {
                return '';
            }
            $zip = new \ZipArchive();
            if ($zip->open($full) !== true) {
                return '';
            }

            if ($zip->numFiles > 100) { // arsip mencurigakan
                $zip->close();
                return '';
            }

            $target = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'pdf' && $this->zipEntryIsSafe($zip, $name)) {
                    $target = $name;
                    break;
                }
            }
            if (!$target) {
                $zip->close();
                return '';
            }
            $tmp = tempnam(sys_get_temp_dir(), 'cvzip') . '.pdf';
            file_put_contents($tmp, $zip->getFromName($target, self::MAX_UNZIPPED_BYTES));
            $zip->close();
            $parser = new \Smalot\PdfParser\Parser();
            $text = trim(preg_replace('/\s+/', ' ', $parser->parseFile($tmp)->getText()));
            @unlink($tmp);
            return $text;
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function stem($text)
    {
        if (!static::$stemmer) {
            static::$stemmer = (new StemmerFactory())->createStemmer();
        }
        $text = mb_strtolower($text);
        // Stem per kata agar konsisten dua sisi
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as &$w) {
            $w = static::$stemmer->stem($w);
        }
        return implode(' ', $words);
    }

    /** Kata kunci signifikan (abaikan stopword, opsional batas jumlah). */
    protected function keywords($line, $minLen = 4, $max = 0)
    {
        static $stop = ['yang','dan','untuk','dengan','dari','pada','adalah','ini','itu','dalam','sebagai','atau','juga','akan','oleh','karena','agar','supaya','jika','kalau','tidak','bisa','harus','dapat','telah','sudah','sangat','lebih','kurang','para','saja','bila','saat','kepada','terhadap','antara','setelah','sebelum','selama','serta','tetapi','namun','melalui','tanpa','nya'];
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($line));
        $out = [];
        foreach ($words as $w) {
            if (mb_strlen($w) >= $minLen && !in_array($w, $stop, true) && !in_array($w, $out, true)) {
                $out[] = $w;
            }
        }
        if ($max > 0) {
            $out = array_slice($out, 0, $max);
        }
        return array_values($out);
    }

    /** Cocok bila ≥$need kata kunci kena (substring / stem / typo 1-2 huruf). */
    protected function hits($cvText, array $needles, $need = 1)
    {
        $stemmer = static::$stemmer;
        $cvWords = preg_split('/\s+/u', $cvText, -1, PREG_SPLIT_NO_EMPTY);
        $hit = 0;
        foreach ($needles as $n) {
            // Stem per kata agar frasa multi-kata konsisten
            $parts = [];
            foreach (preg_split('/\s+/u', $n, -1, PREG_SPLIT_NO_EMPTY) as $w) {
                $parts[] = $stemmer->stem($w);
            }
            $sn = implode(' ', array_filter($parts));
            if ($sn === '') {
                continue;
            }
            if (mb_strpos($cvText, $sn) !== false) {
                $hit++;
            } else {
                foreach ($cvWords as $w) {
                    if (abs(mb_strlen($w) - mb_strlen($sn)) <= 2 && mb_strlen($sn) >= 5
                        && levenshtein($w, $sn) <= max(1, (int) floor(mb_strlen($sn) / 5))) {
                        $hit++;
                        break;
                    }
                }
            }
            if ($hit >= $need) {
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
