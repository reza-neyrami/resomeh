<?php
namespace App\Interfaces;

interface ProfileRepositoryInterface
{
    public function store(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function find($id);
}
