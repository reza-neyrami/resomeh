<?php

namespace App\Modules\InterFaces;

interface MsgRecevedRepoInterFace
{

    public static  function model();
    public static function all();
    public static function find($id);
    public static function create($request);
    public static function update($request, $id);
    public static function delete($id);
    public static function pagination($request);

}
