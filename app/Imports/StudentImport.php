<?php

namespace App\Imports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow
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

    public function model(array $row)
    {
        $this->number++;

        $newCode =  'FE' . str_pad($this->number, 5, 0, STR_PAD_LEFT);
        $password = 'password';
        $birthday = Carbon::createFromFormat('d/m/Y', $row['birthday'])->format('Y-m-d');
        $issueDate = Carbon::createFromFormat('d/m/Y', $row['issue_date'])->format('Y-m-d');
        $existingUser = User::where('email', $row['email'])->first();
        if ($existingUser) {
            return null; 
        }

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
            'role' => $row['role'],
            'is_active' => $row['is_active'],
            'major_code' => $row['major_code'],
            'narrow_major_code' => $row['narrow_major_code'],
            'semester_code' => $row['semester_code'],
            'course_code' => $row['course_code']
        ]);
    }
}
