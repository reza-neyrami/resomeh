<?php

namespace App\Modules\Repository;

use App\Models\Channel;
use App\Modules\InterFaces\ChannelRepositoryInterFace;
use Illuminate\Support\LazyCollection;

class ChannelRepository implements ChannelRepositoryInterFace
{
    protected $model;
    public function __construct(Channel $channel)
    {
        $this->model = $channel;
    }

    public function model()
    {
        return $this->model;
    }
    public function all()
    {
        return LazyCollection::make(function () {
            foreach ($this->model->cursor() as $channel) {
                yield $channel;
            }
        })->chunk(200);
    }

    public function pagination($request)
    {
        return $this->model->paginate($request->per_page ?? 10);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create($request)
    {
        return $this->model->create($request);
    }

    public function update($request, $id)
    {
        $channel = $this->model->find($id);
        return $channel->update($request);
    }

    public function delete($id)
    {
        $channel = $this->model->find($id);
        return $channel->delete();
    }
}
