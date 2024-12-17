<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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

        for ($i = 100 ; $i < 200 ; $i++) {
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
    }
}
