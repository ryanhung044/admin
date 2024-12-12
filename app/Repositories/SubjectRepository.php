<?php
namespace App\Repositories;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class SubjectRepository implements SubjectRepositoryInterface {


    public function getAll(){
        return Subject::all();
    }

    public function getById($id){
        return Subject::with('assessmentItems')->findOrFail($id);
    }

    public function create(Request $data){
        $subject = Subject::create($data->except('assessment_items'));
        return $subject->subjectAssessment()->attach($data->assessment_items);
    }

    public function update($data, $id) {
        $subject = Subject::findOrFail($id); // Sử dụng findOrFail để đảm bảo tìm thấy

        // Cập nhật thông tin môn học, loại bỏ 'assessment_items' ra khỏi dữ liệu
        $subject->update($data->except('assessment_items'));

        // Xóa tất cả các assessment_items hiện tại
        $subject->subjectAssessment()->detach();

        // Thêm các assessment_items mới từ request nếu có
        if ($data->has('assessment_items')) {
            $subject->subjectAssessment()->attach($data->assessment_items);
        }

    }

    public function delete($id){
        return Subject::findOrFail($id)->delete();
    }

}
