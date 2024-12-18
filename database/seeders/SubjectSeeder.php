<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('categories')->insert([
        //     [
        //         'cate_code' => 'CNTT011',
        //         'cate_name' => 'Lập trình',
        //         'value' => 1,
        //         'image' => 'uploads/image/cong_nghe_thong_tin.jpg',
        //         'description' => 'Chuyên ngành Lập trình',
        //         'parent_code' => null,
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT021',
        //         'cate_name' => 'Lập trình phần mềm',
        //         'value' => 2,
        //         'image' => 'uploads/image/lap_trinh_phan_mem.jpg',
        //         'description' => 'Chuyên ngành lập trình phần mềm',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT03',
        //         'cate_name' => 'Lập trình web HTML',
        //         'value' => 3,
        //         'image' => 'uploads/image/lap_trinh_web.jpg',
        //         'description' => 'Chuyên ngành lập trình web',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT04',
        //         'cate_name' => 'Lập trình di động',
        //         'value' => 4,
        //         'image' => 'uploads/image/lap_trinh_di_dong.jpg',
        //         'description' => 'Chuyên ngành lập trình di động',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT05',
        //         'cate_name' => 'Trí tuệ nhân tạo',
        //         'value' => 5,
        //         'image' => 'uploads/image/tri_tue_nhan_tao.jpg',
        //         'description' => 'Chuyên ngành trí tuệ nhân tạo',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT06',
        //         'cate_name' => 'Cơ sở dữ liệu',
        //         'value' => 6,
        //         'image' => 'uploads/image/co_so_du_lieu.jpg',
        //         'description' => 'Chuyên ngành cơ sở dữ liệu',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT07',
        //         'cate_name' => 'An toàn thông tin',
        //         'value' => 7,
        //         'image' => 'uploads/image/an_toan_thong_tin.jpg',
        //         'description' => 'Chuyên ngành an toàn thông tin',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT08',
        //         'cate_name' => 'Phát triển phần mềm',
        //         'value' => 8,
        //         'image' => 'uploads/image/phat_trien_phan_mem.jpg',
        //         'description' => 'Chuyên ngành phát triển phần mềm',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT09',
        //         'cate_name' => 'Phân tích dữ liệu',
        //         'value' => 9,
        //         'image' => 'uploads/image/phan_tich_du_lieu.jpg',
        //         'description' => 'Chuyên ngành phân tích dữ liệu',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'cate_code' => 'CNTT10',
        //         'cate_name' => 'Mạng máy tính',
        //         'value' => 10,
        //         'image' => 'uploads/image/mang_may_tinh.jpg',
        //         'description' => 'Chuyên ngành mạng máy tính',
        //         'parent_code' => 'CNTT01',
        //         'type' => 'major',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        // ]);

        // DB::table('subjects')->insert([
        //     // Kỳ S1 - Cơ bản lập trình
        //     [
        //         'subject_code' => 'MOB01',
        //         'subject_name' => 'Nhập môn lập trình Mobile',
        //         'tuition' => 1000000,
        //         're_study_fee' => 500000,
        //         'credit_number' => 3,
        //         'total_sessions' => 20,
        //         'description' => 'Học các khái niệm cơ bản về lập trình, sử dụng ngôn ngữ lập trình như C hoặc Python.',
        //         'image' => null,
        //         'semester_code' => 'S1',
        //         'major_code' => 'MOB',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'subject_code' => 'MOB02',
        //         'subject_name' => 'Lập trình hướng đối tượng',
        //         'tuition' => 1200000,
        //         're_study_fee' => 600000,
        //         'credit_number' => 3,
        //         'total_sessions' => 22,
        //         'description' => 'Khám phá các nguyên tắc OOP như kế thừa, đa hình, đóng gói, và trừu tượng.',
        //         'image' => null,
        //         'semester_code' => 'S2',
        //         'major_code' => 'MOB',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     // Kỳ S2 - Cơ sở dữ liệu và phân tích hệ thống
        //     [
        //         'subject_code' => 'MOB03',
        //         'subject_name' => 'Cơ sở dữ liệu',
        //         'tuition' => 1300000,
        //         're_study_fee' => 650000,
        //         'credit_number' => 4,
        //         'total_sessions' => 24,
        //         'description' => 'Học cách thiết kế, triển khai và quản lý cơ sở dữ liệu bằng SQL.',
        //         'image' => null,
        //         'semester_code' => 'S3',
        //         'major_code' => 'MOB',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'subject_code' => 'MOB04',
        //         'subject_name' => 'Phân tích và thiết kế hệ thống',
        //         'tuition' => 1400000,
        //         're_study_fee' => 700000,
        //         'credit_number' => 3,
        //         'total_sessions' => 25,
        //         'description' => 'Học cách phân tích yêu cầu, thiết kế hệ thống phần mềm với UML.',
        //         'image' => null,
        //         'semester_code' => 'S4',
        //         'major_code' => 'MOB',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     // Kỳ S3 - Ứng dụng web
        //     [
        //         'subject_code' => 'MOB05',
        //         'subject_name' => 'Phát triển ứng dụng web',
        //         'tuition' => 1500000,
        //         're_study_fee' => 750000,
        //         'credit_number' => 4,
        //         'total_sessions' => 28,
        //         'description' => 'Xây dựng ứng dụng web với HTML, CSS, JavaScript và framework như Laravel hoặc Django.',
        //         'image' => null,
        //         'semester_code' => 'S4',
        //         'major_code' => 'MOB',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'subject_code' => 'MOB06',
        //         'subject_name' => 'Phát triển ứng dụng di động',
        //         'tuition' => 1600000,
        //         're_study_fee' => 800000,
        //         'credit_number' => 4,
        //         'total_sessions' => 30,
        //         'description' => 'Phát triển ứng dụng di động với Android (Java/Kotlin) hoặc iOS (Swift).',
        //         'image' => null,
        //         'semester_code' => 'S5',
        //         'major_code' => 'MOB',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     // Kỳ S4 - Kiểm thử phần mềm
        //     [
        //         'subject_code' => 'MOB07',
        //         'subject_name' => 'Kiểm thử phần mềm',
        //         'tuition' => 1700000,
        //         're_study_fee' => 850000,
        //         'credit_number' => 3,
        //         'total_sessions' => 24,
        //         'description' => 'Học cách kiểm thử phần mềm, viết unit test và kiểm thử tích hợp.',
        //         'image' => null,
        //         'semester_code' => 'S6',
        //         'major_code' => 'MOB',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'subject_code' => 'MOB08',
        //         'subject_name' => 'Triển khai ứng dụng phần mềm',
        //         'tuition' => 1800000,
        //         're_study_fee' => 900000,
        //         'credit_number' => 4,
        //         'total_sessions' => 30,
        //         'description' => 'Triển khai phần mềm lên các nền tảng cloud như AWS, Azure hoặc Heroku.',
        //         'image' => null,
        //         'semester_code' => 'S7',
        //         'major_code' => 'MOB',
        //         'is_active' => 1,
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        // ]);


        $baseTuition = 1000000;
$baseRestudyFee = 500000;
$baseCreditNumber = 3;

$additionalSubjects = [
    'S01' => [
        'Quản lý thương hiệu',
        'Marketing quốc tế',
        'Hành vi người tiêu dùng',
    ],
    'S02' => [
        'Quản lý chiến dịch quảng cáo',
        'Marketing sản phẩm công nghệ',
        'Nghiên cứu thị trường',
    ],
    'S03' => [
        'Marketing dịch vụ',
        'Chiến lược định giá',
        'Marketing tích hợp',
    ],
    'S04' => [
        'Quản trị bán hàng',
        'Kỹ năng viết quảng cáo',
        'Quản lý marketing trong thời đại số',
    ],
    'S05' => [
        'Quảng cáo đa kênh',
        'Chiến lược marketing bền vững',
        'Marketing cho doanh nghiệp nhỏ',
    ],
    'S06' => [
        'Truyền thông tích hợp',
        'Phân tích dữ liệu nâng cao',
        'Marketing theo địa phương',
    ],
    'S07' => [
        'Tối ưu hóa ngân sách marketing',
        'Chiến lược nội dung trong thời đại số',
        'Lập kế hoạch và đo lường ROI',
    ],
];

$majorCode = 'MKT';
$counter = 9; // Bắt đầu từ MKT09

foreach ($additionalSubjects as $semesterCode => $subjectNames) {
    foreach ($subjectNames as $subjectName) {
        DB::table('subjects')->insert([
            'subject_code' => $majorCode . sprintf('%02d', $counter++),
            'subject_name' => $subjectName,
            'tuition' => $baseTuition,
            'total_session' => 20,
            're_study_fee' => $baseRestudyFee,
            'credit_number' => $baseCreditNumber,
            'assessments' => "",
            'description' => "Môn học $subjectName trong chuyên ngành $majorCode.",
            'image' => null,
            'semester_code' => $semesterCode,
            'major_code' => $majorCode,
            'is_active' => 1,
            'deleted_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}





        // Thêm admin
        // DB::table('users')->insert([
        //     [
        //         'user_code' => 'admin',
        //         'full_name' => 'Admin',
        //         'email' => 'admin@gmail.com',
        //         'email_verified_at' => now(),
        //         'password' => bcrypt('password'),
        //         'phone_number' => '0123456789',
        //         'address' => '123 Admin Street',
        //         'sex' => 'Nam',
        //         'birthday' => '1990-01-01',
        //         'citizen_card_number' => '123456789',
        //         'issue_date' => '2020-01-01',
        //         'place_of_grant' => 'Hà Nội',
        //         'nation' => 'Kinh',
        //         'avatar' => null,
        //         'role' => '1',
        //         'is_active' => 1,
        //         'major_code' => null,
        //         'narrow_major_code' => null,
        //         'semester_code' => null,
        //         'course_code' => null,
        //         'remember_token' => Str::random(10),
        //         'deleted_at' => null,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        // ]);


        $majorCodes = ['WEB', 'PTPM', 'UDPM','MOB'];
        // $narrowMajorCodes = ['CN04', 'CNTT01'];
        // $semesterCodes = ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7'];
        // // Thêm 10 giáo viên
        // for ($i = 1; $i <= 15; $i++) {
        //     DB::table('users')->insert([
        //         [
        //             'user_code' => 'teacher' . sprintf('%02d', $i), // teacher01, teacher02, ...
        //             'full_name' => 'Teacher ' . $i,
        //             'email' => 'teacher' . $i . '@example.com',
        //             'email_verified_at' => now(),
        //             'password' => bcrypt('password123'),
        //             'phone_number' => '01234567' . sprintf('%02d', $i),
        //             'address' => '123 Teacher Street',
        //             'sex' => $i % 2 == 0 ? 'Nữ' : 'Nam', // Đặt giới tính ngẫu nhiên
        //             'birthday' => '1985-01-0' . ($i % 10 + 1), // Ngày sinh ngẫu nhiên
        //             'citizen_card_number' => '12345678' . $i,
        //             'issue_date' => '2020-01-01',
        //             'place_of_grant' => 'Hà Nội',
        //             'nation' => 'Kinh',
        //             'avatar' => null,
        //             'role' => '2',
        //             'is_active' => 1,
        //             'major_code' => $majorCodes[array_rand($majorCodes)], // Chọn ngẫu nhiên mã ngành
        //             'narrow_major_code' => $narrowMajorCodes[array_rand($narrowMajorCodes)], // Chọn ngẫu nhiên narrow_major_code
        //             'semester_code' => $semesterCodes[array_rand($semesterCodes)],
        //             'course_code' => 'k18',
        //             'remember_token' => Str::random(10),
        //             'deleted_at' => null,
        //             'created_at' => Carbon::now(),
        //             'updated_at' => Carbon::now(),
        //         ],
        //     ]);
        // }

        // Thêm 20 sinh viên

        // for ($i = 1; $i <= 7; $i++) {
        //     DB::table('subjects')->insert([
        //         [
        //             'subject_code' => 'CNTT04' . sprintf('%03d', $i),
        //             'subject_name' => 'Môn học CNTT04 -' . $i,
        //             'tuition' => rand(5000000, 7000000), // Học phí ngẫu nhiên trong khoảng từ 5-7 triệu
        //             're_study_fee' => rand(2000000, 3000000), // Phí học lại ngẫu nhiên
        //             'credit_number' => rand(3, 5), // Số tín chỉ ngẫu nhiên từ 3 đến 5
        //             'total_sessions' => rand(15, 20), // Tổng số buổi học ngẫu nhiên
        //             'description' => 'Mô tả môn học ' . $i,
        //             'image' => 'mon_hoc_' . $i . '.jpg', // Tên ảnh cho mỗi môn học
        //             'semester_code' => 'S0' . $i,
        //             // 'semester_code' => $semesterCodes[array_rand($semesterCodes)],
        //             'major_code' => 'CN0'. rand(1,5), // Chọn ngẫu nhiên mã ngành
        //             'is_active' => 1,
        //             'deleted_at' => null,
        //             'created_at' => Carbon::now(),
        //             'updated_at' => Carbon::now(),
        //         ],
        //     ]);
        // }

        for ($i = 1001; $i <= 5000; $i++) {
            DB::table('users')->insert([
                [
                    'user_code' => 'FE0' . sprintf('%02d', $i), // FE01, FE02, ...
                    'full_name' => 'Student ' . $i,
                    'email' => 'FE' . $i . '@gmail.com',
                    'email_verified_at' => now(),
                    'password' => bcrypt('password123'),
                    'phone_number' => '01234567' . sprintf('%02d', $i),
                    'address' => '123 Student Street',
                    'sex' => $i % 2 == 0 ? 'male' : 'female', // Ngẫu nhiên: Nữ nếu $i chẵn, Nam nếu $i lẻ
                    'birthday' => '2000-01-0' . (($i % 9) + 1), // Sinh ngày từ 01-01 đến 01-09
                    'citizen_card_number' => '12345678' . $i,
                    'issue_date' => '2020-01-01',
                    'place_of_grant' => 'Hà Nội',
                    'nation' => 'Kinh',
                    'avatar' => null,
                    'role' => "3", // Quyền 3 (Sinh viên)
                    'is_active' => 1,
                    'major_code' => $majorCodes[array_rand($majorCodes)], // Lấy ngẫu nhiên từ danh sách mã ngành
                    'narrow_major_code' => null, // Bạn có thể thêm narrow_major_code nếu cần
                    'semester_code' => 'S1', // Lấy ngẫu nhiên từ danh sách kỳ học
                    'course_code' => 'K1',
                    'remember_token' => Str::random(10),
                    'deleted_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }

        // for ($i = 31; $i <= 70; $i++) {
        //     DB::table('users')->insert([
        //         [
        //             'user_code' => 'TC0' . sprintf('%02d', $i), // FE01, FE02, ...
        //             'full_name' => 'teacher ' . $i,
        //             'email' => 'TC0' . $i . '@gmail.com',
        //             'email_verified_at' => now(),
        //             'password' => bcrypt('password123'),
        //             'phone_number' => '01234567' . sprintf('%02d', $i),
        //             'address' => '123 Student Street',
        //             'sex' => $i % 2 == 0 ? 'Nữ' : 'Nam', // Ngẫu nhiên: Nữ nếu $i chẵn, Nam nếu $i lẻ
        //             'birthday' => '2000-01-0' . (($i % 9) + 1), // Sinh ngày từ 01-01 đến 01-09
        //             'citizen_card_number' => '12345678' . $i,
        //             'issue_date' => '2020-01-01',
        //             'place_of_grant' => 'Hà Nội',
        //             'nation' => 'Kinh',
        //             'avatar' => null,
        //             'role' => "2", // Quyền 3 (Sinh viên)
        //             'is_active' => 1,
        //             'major_code' => $majorCodes[array_rand($majorCodes)], // Lấy ngẫu nhiên từ danh sách mã ngành
        //             'narrow_major_code' => null, // Bạn có thể thêm narrow_major_code nếu cần
        //             'semester_code' => null, // Lấy ngẫu nhiên từ danh sách kỳ học
        //             'course_code' => null,
        //             'remember_token' => Str::random(10),
        //             'deleted_at' => null,
        //             'created_at' => Carbon::now(),
        //             'updated_at' => Carbon::now(),
        //         ],
        //     ]);
        // }

        // for ($i = 0; $i <= 30; $i++) {
        //     DB::table('categories')->insert([
        //         [
        //             'cate_code' => 'P' . sprintf('%02d', $i), // FE01, FE02, ...
        //             'cate_name' => 'P' . sprintf('%02d', $i),
        //             'value' => 40,
        //             'image' => null,
        //             'description' => "",
        //             'parent_code' => null,
        //             'type' => 'school_room',
        //             'is_active' => 1,
        //             'deleted_at' => null,
        //             'created_at' => Carbon::now(),
        //             'updated_at' => Carbon::now(),
        //         ],
        //     ]);
        // }
    }
}
