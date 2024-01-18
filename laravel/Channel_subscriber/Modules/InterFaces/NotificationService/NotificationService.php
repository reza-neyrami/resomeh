<?php

namespace App\Modules\InterFaces\NotificationService;

use App\Models\Message;
use App\Models\User;

interface NotificationService
{
    public static function send(User $user, Message $message);

}
