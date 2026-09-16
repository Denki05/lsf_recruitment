<?php

namespace App\Services;

use App\Applicant;
use GuzzleHttp\Client;

/**
 * Evaluasi kandidat via OpenRouter (AI eksternal, OpenAI-compatible).
 * Dipanggil on-demand dari tombol admin, hasilnya di-cache di DB.
 * Tanpa API key / tanpa internet: kembalikan pesan ramah, bukan error.
 */
class AiScreening
{
    public function available()
    {
        return !empty(config('services.openrouter.key'));
    }

    /**
     * Return ['ok'=>bool, 'score'=>int|null, 'summary'=>string,
     *          'strengths'=>[], 'gaps'=>[], 'error'=>string|null]
     */
    public function evaluate(Applicant $applicant, $cvText)
    {
        if (!$this->available()) {
            return $this->fail('API key OpenRouter belum diisi. Tambahkan OPENROUTER_API_KEY di file .env server.');
        }
        $applicant->loadMissing('position');
        $pos = $applicant->position;

        $prompt = $this->prompt($applicant, $pos, $cvText);

        try {
            $client = new Client(['timeout' => (int) config('services.openrouter.timeout', 60)]);
            $res = $client->post('https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.openrouter.key'),
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => 'Recruitment LSF Screening',
                ],
                'json' => [
                    'model' => config('services.openrouter.model'),
                    'temperature' => 0.2,
                    'max_tokens' => 800,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda asisten HRD. Jawab HANYA berupa JSON valid tanpa teks lain.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);
            $body = json_decode((string) $res->getBody(), true);
            $content = isset($body['choices'][0]['message']['content']) ? $body['choices'][0]['message']['content'] : '';
            if (isset($body['error'])) {
                return $this->fail('OpenRouter: ' . (isset($body['error']['message']) ? $body['error']['message'] : 'unknown error'));
            }
            return $this->parse($content);
        } catch (\Exception $e) {
            \Log::warning('AI screening gagal: ' . $e->getMessage());
            return $this->fail('Gagal menghubungi AI: ' . $e->getMessage());
        }
    }

    protected function prompt($applicant, $pos, $cvText)
    {
        $loker = $pos
            ? "Posisi: {$pos->title} ({$pos->location})\nDeskripsi: " . ($pos->description ?: '-') . "\nRequirement:\n" . ($pos->requirements ?: '-')
            : 'Posisi: -';
        $cv = mb_substr($cvText, 0, 6000);
        return "Nilai kecocokan kandidat berikut untuk lowongan ini (Bahasa Indonesia).\n\n"
            . "=== LOWONGAN ===\n$loker\n\n"
            . "=== DATA KANDIDAT ===\nNama: {$applicant->nama_lengkap}\nDomisili: " . ($applicant->domisili ?: '-') . "\nSIM: " . ($applicant->sim ?: '-') . "\n\n"
            . "=== ISI CV ===\n$cv\n\n"
            . 'Balas HANYA JSON: {"score": 0-100, "summary": "2 kalimat", "strengths": ["maks 5"], "gaps": ["maks 5"]}';
    }

    protected function parse($content)
    {
        if (!preg_match('/\{.*\}/s', $content, $m)) {
            return $this->fail('AI tidak mengembalikan JSON yang valid.');
        }
        $data = json_decode($m[0], true);
        if (!is_array($data) || !isset($data['score'])) {
            return $this->fail('Format jawaban AI tidak dikenali.');
        }
        return [
            'ok' => true,
            'score' => max(0, min(100, (int) $data['score'])),
            'summary' => isset($data['summary']) ? (string) $data['summary'] : '',
            'strengths' => array_slice(array_values((array) (isset($data['strengths']) ? $data['strengths'] : [])), 0, 5),
            'gaps' => array_slice(array_values((array) (isset($data['gaps']) ? $data['gaps'] : [])), 0, 5),
            'error' => null,
        ];
    }

    protected function fail($msg)
    {
        return ['ok' => false, 'score' => null, 'summary' => '', 'strengths' => [], 'gaps' => [], 'error' => $msg];
    }
}
