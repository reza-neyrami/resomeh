<?php

namespace App\Listeners;

use App\Events\MessagePosted;
use App\Mail\ChannelMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendMessageToSubscribers implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    protected $notificationServices;
//    public $delay = 60;
    public function __construct()
    {

    }

    public function handle(MessagePosted $event)
    {
        sleep(2);

        $subscriber = $event->user;

        $service = "App\\Modules\\Services\\" . Str::ucfirst($subscriber->enabled_notification_service) . "Service";

        if ($service) {
            \Closure::fromCallable([$service, 'send'])($subscriber, $event->message);
            \Closure::fromCallable([$service, 'setMsgReceve'])($subscriber, $event->message);

        }

    }
}
