<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        \App\Models\User::factory(50)->create();

        // User::create([
        //     'user_code' => 'AM' . fake()->unique()->numberBetween(100, 999), // Tạo mã sinh viên ngẫu nhiên ST100 - ST999
        //     'full_name' => fake()->name,
        //     'email' => 'admin@gmail.com',
        //     'password' => bcrypt('password'), // Mật khẩu mẫu
        //     'phone_number' => fake()->phoneNumber(),
        //     'address' => fake()->address(),
        //     'sex' => fake()->randomElement(['male', 'female']),
        //     'birthday' => fake()->date(),
        //     'citizen_card_number' => fake()->unique()->numerify('###########'),
        //     'issue_date' => fake()->date(),
        //     'place_of_grant' => fake()->city,
        //     'nation' => 'Kinh',
        //     'avatar' => fake()->imageUrl(200, 200, 'people'), // URL avatar ngẫu nhiên
        //     'role' => '0',
        // ]);

        // User::create([
        //     'user_code' => 'TC' . fake()->unique()->numberBetween(100, 999),
        //     'full_name' => 'Giảng viên 1',
        //     'email' => 'teacher2@gmail.com',
        //     'password' => bcrypt('password'), // Mật khẩu mẫu
        //     'phone_number' => '0123456789',
        //     'address' => 'Hà Nội',
        //     'sex' => 'male',
        //     'birthday' => fake()->date(),
        //     'citizen_card_number' => fake()->unique()->numerify('###########'),
        //     'issue_date' => fake()->date(),
        //     'place_of_grant' => fake()->city,
        //     'nation' => 'Kinh',
        //     'avatar' => fake()->imageUrl(200, 200, 'people'), // URL avatar ngẫu nhiên
        //     'role' => '2',
        //     'major_code' => 'CN01',
        // ]);


    }
}
