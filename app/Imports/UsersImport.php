<?php

namespace App\Imports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    protected $number;

    public function __construct()
    {
        // Lấy user_code có số lớn nhất
        $lastUserCode = User::where('user_code', 'LIKE', "FE%")
            ->orderBy('user_code', 'desc')->value('user_code');
        //  Nếu không có -> gán $number = 0;
        $this->number = $lastUserCode ? (int) substr($lastUserCode, 2) : 0;
    }

    // public function model(array $row)
    // {
    //     $this->number++;

    //     $newCode =  'FE' . str_pad($this->number, 5, 0, STR_PAD_LEFT);
    //     $password = 'password';
    //     $birthday = Carbon::createFromFormat('d/m/Y', $row['birthday'])->format('Y-m-d');
    //     $issueDate = Carbon::createFromFormat('d/m/Y', $row['issue_date'])->format('Y-m-d');
    //     $existingUser = User::where('email', $row['email'])->first();
    //     if ($existingUser) {
    //         return null; 
    //     }

    //     return new User([
    //         'user_code' => $newCode,
    //         'full_name' => $row['full_name'],
    //         'email' => $row['email'],
    //         'password' => $password,
    //         'phone_number' => $row['phone_number'],
    //         'address' => $row['address'],
    //         'sex' => $row['sex'],
    //         'birthday' => $birthday,
    //         'citizen_card_number' => $row['citizen_card_number'],
    //         'issue_date' => $issueDate,
    //         'place_of_grant' => $row['place_of_grant'],
    //         'nation' => $row['nation'],
    //         'role' => "3",
    //         'is_active' => true,
    //         'major_code' => $row['major_code'],
    //         'narrow_major_code' => $row['narrow_major_code'],
    //         'semester_code' => $row['semester_code'],
    //         'course_code' => $row['course_code']
    //     ]);
    // }

    public function model(array $rows)
    {
        foreach ($rows as $row) {

            // Kiểm tra nếu các trường quan trọng thiếu
            if (empty($row['full_name']) || empty($row['email']) || empty($row['phone_number'])) {
                return null; // Bỏ qua dòng này nếu thiếu các trường quan trọng
            }

            // Kiểm tra tính hợp lệ của email và số điện thoại
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                return null; // Nếu email không hợp lệ, bỏ qua dòng này
            }

            // if (!preg_match('/^\d{10}$/', $row['phone_number'])) {
            //     return null; // Nếu số điện thoại không hợp lệ, bỏ qua dòng này
            // }

            // Tăng mã user_code
            $this->number++;

            $newCode =  'FE' . str_pad($this->number, 5, 0, STR_PAD_LEFT);
            $password = 'password';  // Mật khẩu mặc định
            try {
                // Chuyển đổi ngày tháng từ d/m/Y sang Y-m-d
                $birthday = Carbon::createFromFormat('d/m/Y', $row['birthday'])->format('Y-m-d');
                $issueDate = Carbon::createFromFormat('d/m/Y', $row['issue_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                return null; // Nếu ngày tháng không hợp lệ, bỏ qua dòng này
            }

            // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu chưa
            $existingUser = User::where('email', $row['email'])->first();
            if ($existingUser) {
                return null;  // Nếu đã có người dùng với email này, bỏ qua dòng này
            }

            // Lưu thông tin vào cơ sở dữ liệu
            try {
                return new User([
                    'user_code' => $newCode,
                    'full_name' => $row['full_name'],
                    'email' => $row['email'],
                    'password' => $password,
                    'phone_number' => $row['phone_number'],
                    'address' => $row['address'],
                    'sex' => $row['sex'],
                    'birthday' => $birthday,
                    'citizen_card_number' => $row['citizen_card_number'],
                    'issue_date' => $issueDate,
                    'place_of_grant' => $row['place_of_grant'],
                    'nation' => $row['nation'],
                    'role' => "3",
                    'is_active' => true,
                    'major_code' => $row['major_code'],
                    'narrow_major_code' => $row['narrow_major_code'],
                    'semester_code' => $row['semester_code'],
                    'course_code' => $row['course_code']
                ]);
            } catch (\Exception $e) {
                return null; // Nếu có lỗi khi lưu, bỏ qua dòng này
            }
            # code...
        }
    }
}
