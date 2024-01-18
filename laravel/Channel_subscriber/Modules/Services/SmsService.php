<?php

namespace App\Modules\Services;

use App\Models\Message;
use App\Models\User;
use App\Modules\InterFaces\NotificationService\NotificationService;

class SmsService implements NotificationService
{

    public static function send(User $user, Message $message)
    {

    }
    public function setMsgReceve($user , $message){

    }
}
