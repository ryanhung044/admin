<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;

class CategoryNewsletter extends Controller
{
    // CRUD chuyên mục

    // Hàm trả về json khi id không hợp lệ
    public function handleInvalidId()
    {

        return response()->json([
            'message' => 'Không có chuyên mục nào!',
        ], 404);
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

            $search = $request->input('search');
            $categoryNewsletter = Category::with(['childrens' => function($category) {
                                                $category->select('cate_code', 'cate_name', 'image', 'description', 'parent_code', 'is_active');
                                            }])
                                        ->select(['cate_code', 'cate_name', 'image', 'description', 'parent_code', 'is_active'])   
                                        ->where('type', 'category')
                                        ->when($search, function ($query, $search) {
                                            // Sử dụng where để gói điều kiện để tránh nhầm lẫn khi sử dụng orWhere
                                            $query->where(function ($subQuery) use ($search) {
                                                $subQuery->where('cate_name', 'like', "%{$search}%")
                                                         ->orWhereHas('childrens', function($childrenQuery) use ($search) {
                                                             $childrenQuery->where('cate_name', 'like', "%{$search}%");
                                                         });
                                            });
                                        })
                                        ->where(function ($query) use ($search) {
                                            // Đảm bảo khi tìm kiếm trong children thì bản ghi cha sẽ không bị trùng
                                            if ($search) {
                                                $query->orWhereIn('cate_code', function($subQuery) use ($search) {
                                                    $subQuery->select('parent_code')
                                                             ->from('categories')
                                                             ->where('cate_name', 'like', "%{$search}%");
                                                });
                                            }
                                        })
                                        ->distinct() // Loại bỏ các bản ghi trùng lặp
                                        ->paginate(5);
            return response()->json($categoryNewsletter, 200);
        } catch (Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            // Lấy ra cate_code và cate_name của cha
            $parent = Category::whereNull('parent_code')
                ->where('type', '=', 'category')
                ->select('cate_code', 'cate_name')
                ->get();

            $params = $request->except('_token');
            if ($request->hasFile('image')) {
                $fileName = $request->file('image')->store('uploads/image', 'public');
            } else {
                $fileName = null;
            }

            $params['image'] = $fileName;
            Category::create($params);

            return response()->json($params, 200);
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $cate_code)
    {
        try {
            $category = Category::where('cate_code', $cate_code)->first();
            if (!$category) {

                return $this->handleInvalidId();
            } else {

                return response()->json($category, 200);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $cate_code)
    {
        
        try {
            // Lấy ra cate_code và cate_name của cha
            $parent = Category::whereNull('parent_code')
                ->where('type', '=', 'Category')
                ->select('cate_code', 'cate_name')
                ->get();

            $listCategory = Category::where('cate_code', $cate_code)->first();
            if (!$listCategory) {
                

                return $this->handleInvalidId();
            } else {
                $params = $request->except('_token', '_method');
                if ($request->hasFile('image')) {
                    if ($listCategory->image && Storage::disk('public')->exists($listCategory->image)) {
                        Storage::disk('public')->delete($listCategory->image);
                    }
                    $fileName = $request->file('image')->store('uploads/image', 'public');
                } else {
                    $fileName = $listCategory->image;
                }
                $params['image'] = $fileName;
                $listCategory->update($params);
                

                return response()->json($listCategory, 201);
            }
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
            $listCategory = Category::where('cate_code', $cate_code)->first();
            if (!$listCategory) {
                

                return $this->handleInvalidId();
            } else {
                if ($listCategory->image && Storage::disk('public')->exists($listCategory->image)) {
                    Storage::disk('public')->delete($listCategory->image);
                }
                $listCategory->delete($listCategory);
                

                return response()->json([
                    'message' => 'Xóa thành công'
                ], 200);
            }
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
