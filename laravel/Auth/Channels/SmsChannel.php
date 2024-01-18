<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable) ;
        // اینجا می‌توانید کدی بنویسید که با استفاده از API KavehNegar پیامک را ارسال کند
        // ...
    }
}
