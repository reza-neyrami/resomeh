<?php

namespace App\Repositories\Profile;

use App\Interfaces\ProfileRepositoryInterface;
use App\Models\Sending;

class ProfileRepository implements ProfileRepositoryInterface
{
    protected $model;

    public function model()
    {
        return $this->model;
    }

    public function __construct(Sending $model)
    {
        $this->model = $model;
    }

    public function store(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->find($id);
        return $record->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }
}
