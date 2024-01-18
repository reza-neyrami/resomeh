<?php

namespace App\Interfaces;


interface UserRepositoryInterface
{
    public function store(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function find($id);
}

