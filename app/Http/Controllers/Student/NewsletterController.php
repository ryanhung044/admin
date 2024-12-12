<?php

namespace App\Http\Controllers\Student;

use Throwable;
use App\Models\Category;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use App\Models\ClassroomUser;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Lấy user_code từ FE
            $userCode = $request->user()->user_code;
            // $userCode = 'student05';

            if (!$userCode) {

                return response()->json('Không có user', 200);
            }
            // Lấy ra student từ những lớp có mã lớp giống bên newsletters
            $listClass = ClassroomUser::where('user_code', $userCode)->pluck('class_code');
            if (!$listClass) {

                return response()->json('Không có user', 200);
            }
            // dd($listClass);
            $listCategory = Category::where('type', 'category')
                ->with(['newsletter' => function ($query) use ($listClass) {
                    $query->where(function ($query) use ($listClass) {
                        foreach ($listClass as $classCode) {
                            $query->orWhereJsonContains('notification_object', ['class_code' => $classCode]);
                        }
                        $query->orWhereNull('notification_object');
                    });
                }])
                ->get();

            return response()->json($listCategory, 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!'
            ], 500);
        }
    }
    public function show(Request $request, string $code)
    {
        try {
            // Lấy user_code từ FE
            $userCode = $request->user()->user_code;
            // $userCode = 'student05';    
            if (!$userCode) {

                return response()->json('Không có user', 200);
            } else {
                // Lấy ra student từ những lớp có mã lớp giống bên newsletters
                $listClass = ClassroomUser::where('user_code', $userCode)->pluck('class_code');

                // Lấy ra các newsletters thuộc lớp học của user_code
                $newsletters = Newsletter::where('code', $code)->where(function ($query) use ($listClass) {
                    foreach ($listClass as $classCode) {
                        $query->orWhereJsonContains('notification_object', ['class_code' => $classCode]);
                    }
                    $query->orWhereNull('notification_object');
                })
                    ->with(['category', 'user'])
                    ->get()->map(function ($newsletter) {
                        return [
                            'id' => $newsletter->id,
                            'code' => $newsletter->code,
                            'title' => $newsletter->title,
                            'content' => $newsletter->content,
                            'image' => $newsletter->image,
                            'type' => $newsletter->type,
                            'expiry_date' => $newsletter->expiry_date,
                            'is_active' => $newsletter->is_active,
                            'created_at' => $newsletter->created_at,
                            'cate_name' => $newsletter->category ? $newsletter->category->cate_name : null,
                            'full_name' => $newsletter->user ? $newsletter->user->full_name : null,
                        ];
                    });
            }

            return response()->json($newsletters, 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!'
            ], 500);
        }
    }
    public function showCategory(Request $request, string $cateCode)
    {
        try {
            // Lấy user_code từ FE
            $userCode = $request->user()->user_code;

            if (!$userCode) {

                return response()->json('Không có user', 200);
            }
            // Lấy ra student từ những lớp có mã lớp giống bên newsletters
            $listClass = ClassroomUser::where('user_code', $userCode)->pluck('class_code');
            if (!$listClass) {

                return response()->json('Không có user', 200);
            }
            // dd($listClass);
            $listCategory = Category::where('cate_code', $cateCode)->where('type', 'category')
                ->with(['newsletter' => function ($query) use ($listClass) {
                    $query->where(function ($query) use ($listClass) {
                        foreach ($listClass as $classCode) {
                            $query->orWhereJsonContains('notification_object', ['class_code' => $classCode]);
                        }
                        $query->orWhereNull('notification_object');
                    });
                }])
                ->get();

            return response()->json($listCategory, 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!'
            ], 500);
        }
    }
    public function showNoti(Request $request)
    {
        try {
            // Lấy user_code từ FE
            $userCode = $request->user()->user_code;
            // $userCode = 'student05';

            if (!$userCode) {
                return response()->json('Không có user', 200);
            }

            // Lấy ra student từ những lớp có mã lớp giống bên newsletters
            $listClass = ClassroomUser::where('user_code', $userCode)->pluck('class_code');
            if ($listClass->isEmpty()) {
                $listNotification = Newsletter::where('type', 'notification')->orWhereNull('notification_object')
                    ->select('code', 'title', 'created_at', 'updated_at')
                    ->get();

                return response()->json([
                    'data' => $listNotification,
                    'count' => $listNotification->count()
                ], 200);
            }

            // Lấy danh sách thông báo
            $listNotification = Newsletter::where('type', 'notification')
                ->where(function ($query) use ($listClass) {
                    foreach ($listClass as $classCode) {
                        $query->orWhereJsonContains('notification_object', ['class_code' => $classCode]);
                    }
                    $query->orWhereNull('notification_object');
                })
                ->select('code', 'title', 'created_at', 'updated_at')
                ->get();

            return response()->json([
                'data' => $listNotification,
                'count' => $listNotification->count()
            ], 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!'
            ], 500);
        }
    }
}
