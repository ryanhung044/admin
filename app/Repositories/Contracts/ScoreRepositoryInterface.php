<?php
namespace App\Repositories\Contracts;
interface ScoreRepositoryInterface {
    public function getById(int $id);

    public function create($id);

    public function addStudent();

    public function update();

    public function delete();
}
