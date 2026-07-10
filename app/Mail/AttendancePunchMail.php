<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttendancePunchMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $type;
    public $data;

    public function __construct($user, $type, array $data)
    {
        $this->user = $user;
        $this->type = $type; // punch_in / punch_out
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject(
            'Attendance ' . strtoupper(str_replace('_', ' ', $this->type))
        )->view('emails.attendance_punch');
    }
}
