<?php

namespace App\Modules\Repository;

use App\Models\Message;
use App\Modules\InterFaces\MessageRepositoryInterFace;
use Illuminate\Support\LazyCollection;

class MessageRepository implements MessageRepositoryInterFace
{

    protected $model;

    public function __construct(Message $user)
    {
        $this->model = $user;
    }

    public function model()
    {
        return $this->model;
    }
    public function all()
    {
        return LazyCollection::make(function () {
            foreach ($this->model->cursor() as $user) {
                yield $user;
            }
        })->chunk(200);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function pagination($request)
    {
        return $this->model->paginate($request->per_page ?? 10);
    }

    public function create($request)
    {
        return $this->model->create($request);
    }

    public function update($request, $id)
    {
        $user = $this->model->find($id);
        return $user->update($request);
    }

    public function delete($id)
    {
        $user = $this->model->find($id);
        return $user->delete();
    }
}
