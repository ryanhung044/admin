<?php
namespace App\Repositories\Contracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

interface GradeRepositoryInterface{
    public function getAll();

    public function getByParam(Request $request);

    public function update(Request $data , int $id);

    public function delete($id);
}
