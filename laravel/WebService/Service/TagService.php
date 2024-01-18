<?php

namespace App\Services;

use App\Http\Requests\Tag\CreateTagRequest;
use App\Http\Requests\Tag\ListTagRequest;
use App\Tag;

class TagService extends BaseService
{
    public static function index(ListTagRequest $request)
    {
        $tags = Tag::all();
        return $tags;
    }

    public static function create(CreateTagRequest $request)
    {
        $data = $request->validated();
        return Tag::create($data);
    }
}
