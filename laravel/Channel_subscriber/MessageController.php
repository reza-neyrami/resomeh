<?php

namespace App\Http\Controllers;

use App\Events\MessagePosted;
use App\Http\Requests\Messages\MseesageStoreRequest;
use App\Models\Channel;
use App\Modules\InterFaces\MessageRepositoryInterFace;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $messageRepository;

    public function __construct(MessageRepositoryInterFace $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function index(Request $request, Channel $channel)
    {
        if ($channel->user_id != auth('api')->user()->id) {
            return response()->json(['message' => 'شما صاحب این کانال نیستید'], 403);
        }

        $messages = $this->messageRepository->pagination($request);

        return response()->json(['messages' => $messages], 200);
    }



    public function getMessageByChannelId(Request $request, Channel $channel)
    {
        if ($channel->user_id != auth('api')->user()->id) {
            return response()->json(['message' => 'شما صاحب این کانال نیستید'], 403);
        }

        $messages = $this->messageRepository->model()->where('channel_id', $channel->id)->paginate($request->per_page ?? 10);

        return response()->json(['messages' => $messages], 200);
    }



}
