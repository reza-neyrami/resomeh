<?php

namespace App\Modules\Services;

use App\Models\Message;
use App\Models\User;
use App\Modules\InterFaces\NotificationService\NotificationService;

class TelegramService implements NotificationService
{

    public static function send(User $user, Message $message)
    {
        // TODO: Implement send() method.
    }
    public function setMsgReceve($user , $message){

    }
}
