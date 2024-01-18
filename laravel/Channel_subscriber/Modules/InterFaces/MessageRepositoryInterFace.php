<?php

namespace App\Modules\InterFaces;

interface MessageRepositoryInterFace
{
    public  function model();
    public function all();
    public function find($id);
    public function create($request);
    public function update($request, $id);
    public function delete($id);
    public function pagination($request);

}
