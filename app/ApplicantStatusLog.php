<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantStatusLog extends Model
{
    protected $fillable = ['applicant_id', 'user_id', 'from_status', 'to_status', 'note'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Catat 1 perubahan status (dilewati bila status tidak berubah). */
    public static function record($applicantId, $from, $to, $note = null)
    {
        if ($from === $to) {
            return;
        }
        static::create([
            'applicant_id' => $applicantId,
            'user_id'      => auth()->id(),
            'from_status'  => $from,
            'to_status'    => $to,
            'note'         => $note,
        ]);
    }

    /** Catat banyak perubahan sekaligus. $rows = koleksi Applicant dengan status LAMA. */
    public static function recordMany($rows, $to, $note = null)
    {
        $now = now();
        $data = [];
        foreach ($rows as $r) {
            if ($r->status === $to) {
                continue;
            }
            $data[] = [
                'applicant_id' => $r->id,
                'user_id'      => auth()->id(),
                'from_status'  => $r->status,
                'to_status'    => $to,
                'note'         => $note,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }
        foreach (array_chunk($data, 500) as $chunk) {
            static::insert($chunk);
        }
    }
}