<?php

namespace App\Mail;

use App\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;

    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant->load('position');
    }

    public function build()
    {
        return $this->subject('[Lamaran Baru] ' . $this->applicant->nama_lengkap . ' - ' . $this->applicant->position->full_title)
            ->view('emails.application_received');
    }
}
