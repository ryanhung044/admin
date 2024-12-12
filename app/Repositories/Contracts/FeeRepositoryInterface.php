<?php
namespace App\Repositories\Contracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

interface FeeRepositoryInterface{
    public function getAll($status);

    public function createAll();

}
