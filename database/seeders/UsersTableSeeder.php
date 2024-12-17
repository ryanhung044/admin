<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ'];
        $middleNames = ['Văn', 'Thị', 'Hữu', 'Minh', 'Quang', 'Ngọc', 'Gia', 'Anh', 'Thanh', 'Tuấn'];
        $firstNames = ['Hưng', 'Hạnh', 'Dũng', 'Trang', 'Hải', 'Linh', 'Tuấn', 'Lan', 'Thảo', 'Nam'];

        for ($i = 201 ; $i < 300 ; $i++) {
            $fullName = $lastNames[array_rand($lastNames)] . ' '
                . $middleNames[array_rand($middleNames)] . ' '
                . $firstNames[array_rand($firstNames)];

            User::create([
                'user_code' => 'FE' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'full_name' => $fullName,
                'email' => Str::slug($fullName) . rand(1, 99) . '@gmail.com',
                'password' => bcrypt('123456'),
                'phone_number' => '0398' . rand(100000, 999999),
                'address' => 'Địa chỉ ' . ($i + 1),
                'sex' => ['male', 'female'][rand(0, 1)],
                'birthday' => now()->subYears(rand(18, 22))->format('Y-m-d'),
                'citizen_card_number' => '0012040' . rand(10000, 99999),
                'issue_date' => now()->subYears(rand(1, 5))->format('Y-m-d'),
                'place_of_grant' => 'Hà Nội',
                'nation' => 'Kinh',
                'role' => "3",
                'is_active' => 1,
                'major_code' => 'WEB',
                'narrow_major_code' => ['FE', 'BE'][rand(0, 1)],
                'semester_code' => 'S1',
                'course_code' => 'K1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ'];
        // $middleNames = ['Văn', 'Thị', 'Hữu', 'Minh', 'Quang', 'Ngọc', 'Gia', 'Anh', 'Thanh', 'Tuấn'];
        // $firstNames = ['Hưng', 'Hạnh', 'Dũng', 'Trang', 'Hải', 'Linh', 'Tuấn', 'Lan', 'Thảo', 'Nam'];

        // for ($i = 301; $i <= 400; $i++) {
        //     // Tạo tên thật ngẫu nhiên
        //     $fullName = $lastNames[array_rand($lastNames)] . ' '
        //         . $middleNames[array_rand($middleNames)] . ' '
        //         . $firstNames[array_rand($firstNames)];

        //     DB::table('users')->insert([
        //         'user_code' => 'TC' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
        //         'full_name' => $fullName, // Tên thật
        //         'email' => Str::slug($fullName) . rand(1, 99) . '@gmail.com',
        //         'email_verified_at' => now(),
        //         'password' => bcrypt('123456'),
        //         'phone_number' => '01234567' . sprintf('%02d', $i % 100), // Giới hạn số đuôi phone 2 chữ số
        //         'address' => '123 Teacher Street',
        //         'sex' => ['male', 'female'][rand(0, 1)],
        //         'birthday' => now()->subYears(rand(30, 50))->format('Y-m-d'), // Sinh từ 30-50 tuổi
        //         'citizen_card_number' => '12345678' . $i,
        //         'issue_date' => now()->subYears(rand(2, 10))->format('Y-m-d'),
        //         'place_of_grant' => 'Hà Nội',
        //         'nation' => 'Kinh',
        //         'avatar' => null,
        //         'role' => '2', // Quyền teacher
        //         'is_active' => 1,
        //         'major_code' => 'ALL',
        //         'narrow_major_code' => "ALL_TN",
        //         'remember_token' => Str::random(10),
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ]);
        // }
    }
}
