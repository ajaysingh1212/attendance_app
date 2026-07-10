<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Barryvdh\DomPDF\Facade\Pdf;

class UserMonthlyAttendanceMail extends Mailable
{
    public $user;
    public $rows;
    public $monthName;
    public $year;

    public function __construct($user, $rows, $monthName, $year)
    {
        $this->user = $user;
        $this->rows = $rows;
        $this->monthName = $monthName;
        $this->year = $year;
    }

    public function build()
    {
        $pdf = Pdf::loadView('pdfs.user_monthly_attendance', [
            'user' => $this->user,
            'rows' => $this->rows,
            'monthName' => $this->monthName,
            'year' => $this->year,
        ])->setPaper('A4', 'landscape');

        return $this->subject("Monthly Attendance - {$this->monthName} {$this->year}")
            ->view('emails.user_monthly_attendance')
            ->attachData(
                $pdf->output(),
                "monthly-attendance-{$this->monthName}-{$this->year}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
