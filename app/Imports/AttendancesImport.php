<?php

namespace App\Imports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;

class AttendancesImport implements ToCollection, WithHeadingRow, WithStartRow, WithEvents
{
    protected $classCode; // Thuộc tính không tĩnh để lưu class_code

    /**
     * Xử lý sự kiện trước khi import để lấy tiêu đề.
     */
    public function beforeImport(BeforeImport $event)
    {
        $worksheet = $event->reader->getActiveSheet();
        $title = $worksheet->getCell('A1')->getValue(); // Lấy giá trị tiêu đề từ ô A1

        if ($title) {
            // Sử dụng regex để lấy mã lớp từ tiêu đề
            preg_match('/lớp\s(.+?),/i', $title, $matches);
            $this->classCode = $matches[1] ?? null;
        }
    }

    /**
     * Trả về class_code sau khi import
     */
    public function getClassCode()
    {
        return $this->classCode;
    }

    /**
     * Định nghĩa dòng bắt đầu để bỏ qua tiêu đề.
     */
    public function startRow(): int
    {
        return 3; // Bắt đầu đọc từ dòng 3
    }

    /**
     * Xử lý từng dòng dữ liệu trong file Excel.
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $mappedRow = [
                'student_code' => $row[1], // Mã sinh viên
                'date' => $row[4],         // Ngày
                'status' => $row[3],       // Trạng thái
                'noted' => $row[5],        // Ghi chú
            ];

            Attendance::create([
                'class_code' => $this->classCode,
                'student_code' => $mappedRow['student_code'],
                'date' => $mappedRow['date'],
                'status' => $mappedRow['status'],
                'noted' => $mappedRow['noted'],
            ]);
        }
    }

    /**
     * Đăng ký sự kiện.
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $this->beforeImport($event); // Gọi phương thức beforeImport
            },
        ];
    }

}

