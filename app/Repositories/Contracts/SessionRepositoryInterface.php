<?php
namespace App\Repositories\Contracts;

interface SessionRepositoryInterface {
    public function getAll();

    public function create(array $data);

    public function update(array $data , string $code);

    public function delete(string $code);

    public function getModel();
}
