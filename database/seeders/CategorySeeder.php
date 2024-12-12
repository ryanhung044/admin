<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            // Khoá học
            // [
            //     'cate_code' => '1',
            //     'cate_name' => '2022-2025',
            //     'type' => 'course',
            //     'value' => null
            // ],
            // Kì học
            // [
            //     'cate_code' => 'S01',
            //     'cate_name' => "Kỳ 1",
            //     'type' => 'semester',
            //     'value' => 1
            // ],
            // [
            //     'cate_code' => 'S02',
            //     'cate_name' => "Kỳ 2",
            //     'type' => 'semester',
            //     'value' => 2
            // ],
            // [
            //     'cate_code' => 'S03',
            //     'cate_name' => "Kỳ 3",
            //     'type' => 'semester',
            //     'value' => 3
            // ],
            // [
            //     'cate_code' => 'S04',
            //     'cate_name' => "Kỳ 4",
            //     'type' => 'semester',
            //     'value' => 4
            // ],
            // [
            //     'cate_code' => 'S05',
            //     'cate_name' => "Kỳ 5",
            //     'type' => 'semester',
            //     'value' => 5
            // ],
            // [
            //     'cate_code' => 'S06',
            //     'cate_name' => "Kỳ 6",
            //     'type' => 'semester',
            //     'value' => 6
            // ],
            // [
            //     'cate_code' => 'S07',
            //     'cate_name' => "Kỳ 7",
            //     'type' => 'semester',
            //     'value' => 7
            // ],

            // Chuyên ngành
            [
                'cate_code' => 'CN01',
                'cate_name' => 'Lập trình Web',
                'type' => 'major',
                'value' => null
            ],
            [
                'cate_code' => 'CN02',
                'cate_name' => 'Lập trình mobile',
                'type' => 'major',
                'value' => null
            ],
            [
                'cate_code' => 'CN03',
                'cate_name' => 'Digital marketing',
                'type' => 'major',
                'value' => null
            ],
            [
                'cate_code' => 'CN04',
                'cate_name' => 'Cơ điện tử',
                'type' => 'major',
                'value' => null
            ],
            [
                'cate_code' => 'CN05',
                'cate_name' => 'Tự động hoá',
                'type' => 'major',
                'value' => null
            ],

            // Ca học

            // [
            //     'cate_code' => 'TS1',
            //     'cate_name' => "Ca 1",
            //     'value' => json_encode([
            //         'start' => '07:00',
            //         'end' => '09:00',
            //     ]),
            //     'type' => 'session'
            // ],
            // [
            //     'cate_code' => 'TS2',
            //     'cate_name' => "Ca 2",
            //     'value' => json_encode([
            //         'start' => '09:15',
            //         'end' => '11:15',

            //     ]),
            //     'type' => 'session',
            // ],
            // [
            //     'cate_code' => 'TS3',
            //     'cate_name' => "Ca 3",
            //     'value' => json_encode([
            //         'start' => '14:00',
            //         'end' => '16:00',

            //     ]),
            //     'type' => 'session',
            // ],
            // [
            //     'cate_code' => 'TS4',
            //     'cate_name' => "Ca 4",
            //     'value' => json_encode([
            //         'start' => '16:15',
            //         'end' => '18:15',

            //     ]),
            //     'type' => 'session',
            // ],
            // [
            //     'cate_code' => 'TS5',
            //     'cate_name' => "Ca 5",
            //     'value' => json_encode([
            //         'start' => '18:30',
            //         'end' => '20:30',

            //     ]),
            //     'type' => 'session',
            // ]





        ]);

        for ($floor = 1; $floor <= 4; $floor++) {
            for ($room = 1; $room <= 10; $room++) {
                $roomNumber = sprintf("P%d%02d", $floor, $room);

                Category::create([
                    'cate_code' => $roomNumber,
                    'cate_name' => $roomNumber,
                    'value' => 40,
                    'type' => 'school_room'
                ]);
            }
        }
    }
}
