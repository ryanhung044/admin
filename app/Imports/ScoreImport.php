<?php

namespace App\Imports;

use App\Models\Classroom;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScoreImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected $classCode;

    public function __construct($classCode)
    {
        $this->classCode = $classCode;
    }

    public function startRow(): int
    {
        return 2; // Bắt đầu đọc từ dòng 3
    }
    /**
     * Xử lý dữ liệu từ file Excel
     */
    public function collection(Collection $rows)
    {
        $listClassroom = Classroom::where('class_code', $this->classCode)->first();
        // Lấy dòng tiêu đề từ hàng đầu tiên
        $headings = $rows->shift()->toArray(); // Xóa dòng tiêu đề khỏi $rows

        // Xác định các cột liên quan đến điểm
        $scoreColumns = array_filter($headings, function ($heading) {
            return str_starts_with(strtolower($heading), 'lab');
        });

        $data = []; // Lưu trữ dữ liệu import

        foreach ($rows as $row) {
            $row = $row->toArray();

            $studentCode = $row[array_search('Mã sinh viên', $headings)] ?? null;
            $studentName = $row[array_search('Tên sinh viên', $headings)] ?? null;
            $averageScore = $row[array_search('Average', $headings)] ?? null;

            if ($studentCode) {
                // Tạo danh sách điểm
                $scores = [];
                foreach ($scoreColumns as $col) {
                    $index = array_search($col, $headings);
                    $scores[] = [
                        'name' => $col,
                        'score' => $row[$index] ?? null,
                    ];
                }

                // Định dạng dữ liệu cho từng sinh viên
                $data[] = [
                    'student_code' => $studentCode,
                    'student_name' => $studentName,
                    'average_score' => $averageScore,
                    'scores' => $scores,
                ];
            }
        }

        // Cập nhật hoặc thêm mới vào bảng classrooms
        Classroom::updateOrCreate(
            ['class_code' => $this->classCode],
            ['score' => json_encode($data)] // Lưu trữ điểm vào trường score
        );
        // dd($this->classCode, $data);
    }
}
