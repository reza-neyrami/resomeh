<?php

namespace App\Modules\Repository;

use App\Models\Message;
use App\Models\ReceivedMsg;
use App\Modules\InterFaces\MsgRecevedRepoInterFace;
use Illuminate\Support\LazyCollection;

class MessageRecivedRepository implements MsgRecevedRepoInterFace
{
    protected static $model;

    public function __construct(ReceivedMsg $msg){
        $this->model = $msg;
    }

    public static function model()
    {
        return self::$model;
    }
    public static function all()
    {
        return LazyCollection::make(function () {
            foreach ($this->model->cursor() as $msg) {
                yield $msg;
            }
        })->chunk(200);
    }

    public static function find($id)
    {
        return self::$model->find($id);
    }

    public static function pagination($request)
    {
        return self::$model->paginate($request->per_page ?? 10);
    }

    public static function create($request)
    {
        return self::$model->create($request);
    }

    public static function update($request, $id)
    {
        $msg = self::$model->find($id);
        return $msg->update($request);
    }

    public static function delete($id)
    {
        $msg = self::$model->find($id);
        return $msg->delete();
    }
}
