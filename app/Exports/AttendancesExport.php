<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Attendance;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class AttendancesExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithCustomStartCell
{
    protected $data;

    // Constructor để nhận dữ liệu
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // Phương thức trả về dữ liệu
    public function collection()
    {
        $rows = [];
        $stt = 1;
        // Duyệt qua dữ liệu để thêm vào hàng tiếp theo
        foreach ($this->data as $class) {
            foreach ($class['attendance'] as $attendance) {
                $rows[] = [
                    $stt ++,
                    $attendance['student_code'],
                    $attendance['full_name'],
                    $attendance['status'],
                    $attendance['date'],
                    $attendance['noted'],
                ];
            }
        }

        return collect($rows);
    }

    // Định nghĩa tiêu đề cột
    public function headings(): array
    {
        return [
            'STT',
            'Student Code',
            'Full Name',
            'Status',
            'Date',
            'Noted',
        ];
    }

    // Định dạng chiều rộng cột
    public function columnWidths(): array
    {
        return [
            'A' => 5, 
            'B' => 15, 
            'C' => 25, 
            'D' => 10,
            'E' => 15,
            'F' => 30,
        ];
    }

    public function startCell(): string
    {
        return 'A2'; // Bắt đầu xuất headings từ hàng thứ 2
    }
    // Định dạng style
    public function styles(Worksheet $sheet)
    {
        // Gộp các ô từ A1 đến H1 để chứa tiêu đề lớn
        $sheet->mergeCells('A1:F1')->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);;

        // Định dạng tiêu đề lớn
        $sheet->setCellValue('A1', "Điểm danh lớp {$this->data[0]['class_code']}, Ngày {$this->data[0]['attendance'][0]['date']}");
        // In đậm hàng đầu tiên
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        // Tạo Data Validation cho cột G (Status)
        $highestRow = $sheet->getHighestRow(); // Lấy hàng cuối cùng của dữ liệu
        for ($row = 3; $row <= $highestRow; $row++) {
            $validation = $sheet->getCell("D$row")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true); // Cho phép giá trị rỗng
            $validation->setShowDropDown(true);
            $validation->setFormula1('" ,absent,present"'); // Danh sách các giá trị tùy chọn
        }

        // Căn trái các cột khác
        $sheet->getStyle('A3:F' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [];
    }
}
