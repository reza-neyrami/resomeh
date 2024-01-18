<?php

namespace App\Http\Controllers;

use App\Events\MessagePosted;
use App\Http\Requests\Channels\CreateUniqeChannelRequest;
use App\Http\Requests\Messages\MseesageStoreRequest;
use App\Models\Channel;
use App\Models\ReceivedMsg;
use App\Modules\InterFaces\ChannelRepositoryInterFace;
use App\Modules\InterFaces\MessageRepositoryInterFace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChannelController extends Controller
{
    protected $channelRepository;
    protected $messageRepository;

    public function __construct(ChannelRepositoryInterFace $channelRepository, MessageRepositoryInterFace $messageRepository)
    {
        $this->channelRepository = $channelRepository;
        $this->messageRepository = $messageRepository;
    }

    public function index()
    {
        $channels = $this->channelRepository->all();
        return response()->json($channels, 200);
    }

    public function store(CreateUniqeChannelRequest $request)
    {

        $channel = $this->channelRepository->create([
            'user_id' => auth('api')->user()->id,
            'name' => $request->name,
            'identifier' => $this->createIdentifireUniqeChannel($request->name),
            'image' => $request->image,
        ]);
        return response()->json($channel, 201);
    }

    protected function createIdentifireUniqeChannel($name)
    {
        $identifire = str_replace(' ', '-', $name);
        $identifire = preg_replace('/[^A-Za-z0-9\-]/', '', $identifire);
        $identifire = strtolower($identifire);
        $identifire = substr($identifire, 0, 20);
        $identifire = $identifire . '-' . uniqid();
        return $identifire;
    }

    public function getChannelByIdentifire($identifire)
    {
        $channel = $this->channelRepository->model()->where('identifire', $identifire)->first();
        return response()->json($channel, 200);
    }

    public function getChannelById($id)
    {
        $channel = $this->channelRepository->find($id);
        return response()->json($channel, 200);
    }

    public function uploadeImageWidthStorage(Request $request)
    {
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('images', $image, $imageName);
        return response()->json(['image' => $imageName], 200);
    }

    //subscribe to a channel

    public function subscribe(Request $request, Channel $channel)
    {
        $channel = $this->channelRepository->model()->where('id', $channel->id)->first();

        $channel->subscribers()->attach(auth('api')->user()->id);
        return response()->json(['message' => 'You have subscribed to this channel'], 200);
    }

    public function unSubscribe(Request $request, Channel $channel)
    {
        $channel = $this->channelRepository->model()->where('id', $channel->id)->first();

        $channel->subscribers()->detach(auth('api')->user()->id);
        return response()->json(['message' => 'You have subscribed to this channel'], 200);
    }


    public function storeMessage(MseesageStoreRequest $request, Channel $channel)
    {
        if ($channel->user_id != auth('api')->user()->id) {
            return response()->json(['message' => 'شما صاحب این کانال نیستید'], 403);
        }

        // بررسی که آیا پیامی با همین متن قبلاً در کانال ارسال شده است یا خیر
        $existingMessage = $this->messageRepository->model()->where('channel_id', $channel->id)
            ->where('body', $request->body)
            ->first();

        if ($existingMessage) {
            $usersWhoReceived = ReceivedMsg::where('message_id', $existingMessage->id)
                ->pluck('user_id');

            // پیدا کردن کاربرانی که پیام قبلی را دریافت نکرده‌اند
            $usersWhoDidNotReceive = $channel->subscribers()
                ->whereNotIn('id', $usersWhoReceived)
                ->get();

            // ارسال پیام جدید به کاربرانی که پیام قبلی را دریافت نکرده‌اند
            foreach ($usersWhoDidNotReceive as $user) {
                event(new MessagePosted($existingMessage, $user));
            }

            return response()->json(['message' => 'پیام با موفقیت ارسال شد.'], 201);
        } else {


            $message = $this->messageRepository->create([
                'user_id' => auth('api')->user()->id,
                'channel_id' => $channel->id,
                'title' => $request->title,
                'body' => $request->body,
            ]);

            // ارسال پیام جدید به تمام سابسکرایبرهای کانال
            foreach ($channel->subscribers as $subscriber) {
                event(new MessagePosted($message, $subscriber));
            }
        }
        return response()->json(['message' => 'پیام با موفقیت ایجاد و ارسال شد.'], 201);
    }


}
