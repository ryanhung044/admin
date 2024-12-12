<?php
namespace App\Repositories\Contracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

interface SubjectRepositoryInterface{
    public function getAll();

    public function getById(int $id);


    public function create(Request $data);

    public function update(Request $data , int $id);

    public function delete($id);
}
