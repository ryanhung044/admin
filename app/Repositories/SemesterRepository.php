<?php
namespace App\Repositories;

use App\Models\Category;
use App\Models\TimeSlot;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use GuzzleHttp\Psr7\Request;

class SemesterRepository implements SemesterRepositoryInterface{
    public function getAll(){
       return Category::query()->where('type','like','%semester%')->get();
    }

    public function create(Request $request){
        // $data = [
        //     $request->
        // ]
        return Category::create();
    }

    public function update(Request $data , int $id){
        return Category::findOrFail($id)->update($data);
    }

    public function delete($id){
          $model = Category::findOrFail($id);
          return $model->delete();
    }
}
