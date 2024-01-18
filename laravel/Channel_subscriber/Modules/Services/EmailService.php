<?php

namespace App\Modules\Services;

use App\Mail\ChannelMessage;
use App\Models\Message;
use App\Models\ReceivedMsg;
use App\Models\User;
use App\Modules\InterFaces\MsgRecevedRepoInterFace;
use App\Modules\InterFaces\NotificationService\NotificationService;
use Illuminate\Support\Facades\Mail;

class EmailService implements NotificationService
{
    protected static $msgRecevedRepoInterFace;
    public  function __construct(MsgRecevedRepoInterFace $msgRecevedRepoInterFace)
    {
        self::$msgRecevedRepoInterFace = $msgRecevedRepoInterFace;
    }

    public static function send(User $user, Message $message)
    {
        Mail::to($user->email)->queue(new ChannelMessage($message));

    }

    public static function setMsgReceve(User $user, Message $message){
        ReceivedMsg::create([
            'user_id'=> $user->id,
            'message_id'=> $message->id,
        ]);
    }
}
