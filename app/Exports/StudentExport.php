<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::select( 'user_code', 'full_name', 'email','phone_number', 'address', 'sex',
            'birthday', 'citizen_card_number', 'issue_date', 'place_of_grant', 'nation', 'major_code', 'narrow_major_code', 'semester_code', 'course_code'
        )->where('role', '3')->get();
    }

    public function headings(): array
    {
        return [
            'User Code',
            'Full Name',
            'Email',
            'Phone Number',
            'Address',
            'Sex',
            'Birthday',
            'Citizen Card Number',
            'Issue Date',
            'Place of Grant',
            'Nation',
            'Major Code',
            'Narrow Major Code',
            'Semester Code',
            'Course Code'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 10,
            'G' => 10,
            'H' => 20,
            'I' => 10,
            'J' => 20,
            'K' => 10,
            'L' => 10,
            'M' => 10,
            'N' => 10,
            'O' => 10,
            'P' => 10,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $margin_left = ['horizontal' => Alignment::HORIZONTAL_LEFT];

        $styles = [];
        foreach(range("A", "P") as $column){
            $styles[$column] = ['alignment' => $margin_left];
        }

        $styles[1] = ['font' => ['bold' => true]];

        return $styles;
       
    }
}
