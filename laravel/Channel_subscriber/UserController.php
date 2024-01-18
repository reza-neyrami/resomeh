<?php

namespace App\Http\Controllers;

use App\Http\Requests\Channel\ChannelSubscriber;
use App\Http\Requests\Channel\ChannelUnSubscriber;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Modules\InterFaces\ChannelRepositoryInterFace;
use App\Modules\InterFaces\UserRepositoryInterface;
use App\Modules\Repository\ChannelRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository ) {
        $this->userRepository = $userRepository;
    }

    public function all()
    {
        return $this->userRepository->all();
    }

    public function index(Request $request)
    {
        return $this->userRepository->pagination($request);
    }

    public function find($id)
    {

        return $this->userRepository->find($id);
    }

    public function update(UserUpdateRequest $request , $id)
    {
        $user = $this->userRepository->find($id);
        return $user->update($request->validated());
    }


}
