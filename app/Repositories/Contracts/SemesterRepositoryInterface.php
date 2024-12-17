<?php
namespace App\Repositories\Contracts;

use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Http\FormRequest;

interface SemesterRepositoryInterface{
    public function getAll();

    public function create(Request $request);

    public function update(Request $request , int $id);

    public function delete($id);
}
