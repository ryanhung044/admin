<?php
namespace App\Repositories;

use App\Models\Category;
use App\Models\TimeSlot;
use App\Repositories\Contracts\SessionRepositoryInterface;

class SessionRepository implements SessionRepositoryInterface {
    protected $model;

    public function __construct(Category $model) {
        $this->model = $model;
    }

    public function getAll() {
        return $this->model->query()
                           ->where('type', 'session')
                           ->whereNull('deleted_at')
                           ->get();
    }

    public function create(array $data) {
        return $this->model->create($data);
    }

    public function update(array $data, string $code) {
        $model = $this->model->where('cate_code', $code);
        return $model->update($data);
    }

    public function delete(string $code) {
        return $this->model->where('cate_code', $code)->delete();
    }

    public function getModel() {
        return $this->model;
    }
}
