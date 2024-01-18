<?php

namespace App\Repositories\WebService\Repositories;

use App\Models\Course;
use App\Models\Episode;
use App\Repositories\WebService\InterFaces\ManageVideo;

class ManageRepositories implements ManageVideo
{

    protected static $model;


    public function __construct(Course $model)
    {
        self::$model = $model;
    }

    /**
     * @param $request
     * @return void
     */
    public function course(array $request = [])
    {
        if ($request) {
            foreach ($request as $key => $value) {
                Course::upsert($value, $value['VideoGroup_ID']);
            }
        }
    }

    /**
     * @param $request
     * @return void
     */
    public function videos($request = [])
    {
        if ($request) {
            foreach ($request as $key => $value) {
                Episode::upsert($value, $value['Video_ID']);

            }
        }
    }

    /**
     * @return Course
     */
    public static function getModel(): Course
    {
        return self::$model;
    }


}
