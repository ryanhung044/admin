<?php
namespace App\Repositories\Contracts;

use Illuminate\Foundation\Http\FormRequest;

interface SemesterRepositoryInterface{
    public function getAll();

    public function create(array $data);

    public function update(array $data , int $id);

    public function delete($id);
}
