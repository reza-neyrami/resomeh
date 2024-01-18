<?php

namespace App\Repositories\WebService\InterFaces;


interface ManageVideo
{
    public function course(array $request = []);

    public function videos(array $request = []);

    public static function getModel();

}
