<?php

namespace App\Mail;

use App\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;

    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant->load('position');
    }

    public function build()
    {
        return $this->subject('Update Status Lamaran Anda: ' . $this->applicant->status)
            ->view('emails.status_changed');
    }
}
