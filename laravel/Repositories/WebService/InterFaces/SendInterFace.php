<?php

namespace App\Repositories\WebService\InterFaces;

interface SendInterFace
{
    public static function get($options = []);

    public static function post($options = []);
}
