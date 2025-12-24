<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayrollExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($payrolls)
    {
        $this->data = $payrolls;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($payroll) {
            return [
                $payroll->employee->name,
                $payroll->month,
                $payroll->net_salary,
                $payroll->paid_days,
                $payroll->absent_days,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Month',
            'Net Salary',
            'Paid Days',
            'Absent Days',
        ];
    }
}
