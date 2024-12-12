<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NewsletterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('newsletters')->insert([
            [
                'code' => 'test1',
                'title' => 'test1',
                'content' => 'test1',
                'type' => 'news',
                'user_code' => 'AM533',
                'cate_code' => 'CN01'
            ],
            [
                'code' => 'test2',
                'title' => 'test2',
                'content' => 'test2',
                'type' => 'article',
                'user_code' => 'AM533',
                'cate_code' => 'CN03'
            ],
            [
                'code' => 'test3',
                'title' => 'test3',
                'content' => 'test3',
                'type' => 'notification',
                'notification_object' => ['CLS101'],
                'user_code' => 'AM533',
                'cate_code' => 'CN04'
            ],
            [
                'code' => 'test4',
                'title' => 'test4',
                'content' => 'test4',
                'type' => 'notification',
                'notification_object' => ['CLS101'],
                'user_code' => 'AM533',
                'cate_code' => 'CN04'
            ]
        ]);
    }
}
