<?php

namespace App\Http\Controllers\Admin;

use App\Models\AssessmentItem;
use Throwable;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PointHead\StorePointHeadRequest;
use App\Http\Requests\PointHead\UpdatePointHeadRequest;

class PointHeadController extends Controller
{
    // Hàm trả về json khi id không hợp lệ
    public function handleInvalidId()
    {

        return response()->json([
            'message' => 'Không có đầu điểm nào!',
        ], 200);
    }

    //  Hàm trả về json khi lỗi không xác định (500)
    public function handleErrorNotDefine($th)
    {
        Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

        return response()->json([
            'message' => 'Lỗi không xác định!'
        ], 500);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);

            $search = $request->input('search');
            $data = AssessmentItem::when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->paginate($perPage);

            $data->getCollection()->transform(function ($item) {
                return [
                    'cate_code' => $item->assessment_code,
                    'cate_name' => $item->name,
                    'value' => $item->weight,
                ];
            });

            if ($data->isEmpty()) {

                return $this->handleInvalidId();
            }
            return response()->json($data, 200);

        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePointHeadRequest $request )
    {
        try {

            $data = [
                'assessment_code' => $request->cate_code,
                'name'            => $request->cate_name,
                'weight'          => $request->value
            ];

            AssessmentItem::create($data);
            return response()->json($data, 200);

            return response()->json($assessmentItem, 200);
        } catch (Throwable $th) {
            return response()->json(['message'=> $th->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $cate_code)
    {
        try {
            $pointHead = AssessmentItem::where('assessment_code',$cate_code)->first();

            $data = [
                'cate_code' => $pointHead->assessment_code,
                'cate_name' => $pointHead->name,
                'value'     => $pointHead->weight
            ];
            if (!$data) {
                return $this->handleInvalidId();
            } else {
                return response()->json($data, 200);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePointHeadRequest $request, string $cate_code)
    {
        try {
            // Lấy ra cate_code và cate_name của cha
            $data = [
                'assessment_code' => $request->cate_code,
                'name'            => $request->cate_name,
                'weight'          => $request->value
            ];

            $PointHead = AssessmentItem::where('assessment_code', $cate_code)->first();
            $PointHead->update($data);


            return response()->json($PointHead, 201);

        } catch (Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cate_code)
    {
        try {
            $isUsed = DB::table('subject_assessment')
            ->where('assessment_code', $cate_code) // Thay đổi 'assessment_code' thành cột tương ứng trong bảng subject_assessment
            ->exists();

            if ($isUsed) {
                return response()->json([
                    'message' => 'Không thể xóa vì mã đầu điểm đang được sử dụng.'
                ], 400); // 400: Bad Request
            }

            $PointHead = AssessmentItem::where('assessment_code', $cate_code)->first();

            if (!$PointHead) {
                return response()->json([
                    'message' => 'Mã đầu điểm không tồn tại.'
                ], 404); // 404: Not Found
            }
            
            $PointHead->delete();

            return response()->json([
                    'message' => 'Xóa thành công'
            ], 200);

        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    public function bulkUpdateType(Request $request)
    {
        try {
            $activies = $request->input('is_active'); // Lấy dữ liệu từ request

                foreach ($activies as $cate_code => $active) {
                    // Tìm category theo cate_code và áp dụng lock for update

                    $category = Category::where('cate_code', $cate_code)->first(); 

                    if ($category) {
                        $category->is_active = $active; // Sửa lại đúng field
                        $category->save();
                    }
                };

            return response()->json([
                'message' => 'Trạng thái đã được cập nhật thành công!'
            ], 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
}
