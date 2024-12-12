<?php
namespace App\Repositories;

use App\Models\Category;
use App\Models\TimeSlot;
use App\Repositories\Contracts\SemesterRepositoryInterface;

class SemesterRepository implements SemesterRepositoryInterface{
    public function getAll(){
       return Category::query()->where('type','like','%semester%')->get();
    }

    public function create(array $data){
        return Category::create($data);
    }

    public function update(array $data , int $id){
        return Category::findOrFail($id)->update($data);
    }

    public function delete($id){
          $model = Category::findOrFail($id);
          return $model->delete();
    }
}
