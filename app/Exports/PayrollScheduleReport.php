<?php

namespace App\Exports;

use App\Models\Payslip;
use App\Models\Profile;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayrollScheduleReport implements FromArray, WithHeadings
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function array(): array
    {
        $payslips = Payslip::where('status', 'PAID')
        ->where('payment_date', $this->date)
        ->get();

        $report = [];

        foreach ($payslips as $index => $row) {
            $user = User::find($row->user_id);
            $profile = Profile::where('user_id', $row->user_id)->first();

            $report[] = [
                '#' => $index + 1,
                'Employee' => $user->f_name . ' ' . $user->l_name,
                'Bank' => $profile->bank_name,
                'Account No' => $profile->account_no,
                'Gross' => $row->gross,
                'Deductions' => $row->deductions,
                'Net' => $row->net,
                'Month' => $row->payment_date,
            ];
        }

        return $report;

    }

    public function headings(): array
    {
        return [
            '#',
            'Employee',
            'Bank',
            'Account No',
            'Gross',
            'Deductions',
            'Net',
            'Month',
        ];
    }


    
}
