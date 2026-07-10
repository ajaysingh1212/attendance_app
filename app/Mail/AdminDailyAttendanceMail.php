<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminDailyAttendanceMail extends Mailable
{
    public $date;
    public $rows;
    public $summary;

    public function __construct($date, $rows, $summary)
    {
        $this->date = $date;
        $this->rows = $rows;
        $this->summary = $summary;
    }

    public function build()
    {
        // 🧾 Generate PDF
        $pdf = Pdf::loadView('pdfs.admin_daily_attendance', [
                'date' => $this->date,
                'rows' => $this->rows,
                'summary' => $this->summary,
            ])
            ->setPaper('A4', 'portrait'); // 👈 A4 size

        return $this->subject('Daily Attendance Report - ' . $this->date)
            ->view('emails.admin_daily_attendance')
            ->attachData(
                $pdf->output(),
                'attendance-report-' . $this->date . '.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }
}
