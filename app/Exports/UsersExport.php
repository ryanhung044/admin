<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::select(
            'user_code',
            'full_name',
            'email',
            'phone_number',
            'address',
            'sex',
            'birthday',
            'citizen_card_number',
            'issue_date',
            'place_of_grant',
            'nation',
            // 'role',
            // 'is_active',
            'major_code',
            'narrow_major_code',
            'semester_code',
            'course_code'
        )->where('role', "3")->get();
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
            // 'Role',
            // 'Is Active',
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
            'F' => 5,
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
            'Q' => 10,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'F' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'G' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'H' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'I' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'J' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'K' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'L' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'M' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'N' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'O' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'P' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'Q' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            // '*' => ['alignment' =>
            //             ['horizontal' => Alignment::HORIZONTAL_LEFT]
            //         ],
            1 => ['font' => ['bold' => true]],
        ];
    }
}
