<?php

namespace App\Exports;

use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScoreExport implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    // Trả về tiêu đề cho mỗi sheet
    public function title(): string
    {
        return 'Bảng điểm lớp';
    }

    // Tạo dữ liệu cho sheet
    public function collection()
    {
        $rows = [];

        foreach ($this->data as $class) {
            $classCode = $class['class_code'];
            $scores = $class['score'];

            // Tiêu đề hàng đầu tiên
            $headerRow = [
                "STT",
                "Mã sinh viên",
                "Tên sinh viên"
            ];

            // Lấy các cột điểm
            $scoreNames = [];
            foreach ($scores[0]['scores'] as $score) {
                $scoreNames[] = $score['name'];
            }

            $headerRow = array_merge($headerRow, $scoreNames, ["Average"]);

            // Thêm hàng tiêu đề
            $rows[] = ["Bảng điểm lớp " . $classCode]; // Hàng tiêu đề lớn
            $rows[] = $headerRow;                     // Hàng tiêu đề cụ thể

            // Dữ liệu điểm của từng sinh viên
            $stt = 1;
            foreach ($scores as $student) {
                $studentRow = [
                    $stt++,
                    $student['student_code'],
                    $student['student_name'],
                ];

                foreach ($student['scores'] as $score) {
                    $studentRow[] = $score['score'];
                }

                // Thêm cột điểm trung bình
                $studentRow[] = $student['average_score'];

                $rows[] = $studentRow;
            }

            // Thêm một dòng trống giữa các lớp
            $rows[] = [];
        }

        return collect($rows); // Trả về dạng Collection
    }

    // Trả về tiêu đề sheet
    public function headings(): array
    {
        return [];
    }

    
    // Định dạng chiều rộng cột
    public function columnWidths(): array
    {
        return [
            'A' => 5, 
            'B' => 15, 
            'C' => 20, 
            'D' => 10,
            'E' => 10,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 10,
            'J' => 10,
            'K' => 10,
            'L' => 10,
            'M' => 10,
            'N' => 10,
            'O' => 10,
            'P' => 10,
            'Q' => 10,
            'R' => 10,
            'S' => 10,
            'T' => 10,
            'U' => 10,
            'V' => 10,
            'W' => 10,
        ];
    }

    // Định dạng style
    public function styles(Worksheet $sheet)
    {
        // Gộp các ô từ A1 đến H1 để chứa tiêu đề lớn
        $sheet->mergeCells('A1:W1')->getStyle('A1:W1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);;

        // In đậm hàng đầu tiên
        $sheet->getStyle('A2:W2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Căn trái các cột khác
        $sheet->getStyle('A3:W' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [];
    }
}
