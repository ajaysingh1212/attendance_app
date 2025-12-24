<?php

namespace App\Helpers;

class PayrollHelper
{
    public static function finalSalary($payroll)
    {
        // Agar remaining_salary hai to wahi show kare
        if (!is_null($payroll->remaining_salary)) {
            return $payroll->remaining_salary;
        }

        // Otherwise net_salary show kare
        return $payroll->net_salary;
    }
}
