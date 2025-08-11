<?php

namespace App\Listeners\Notifications;

use App\Events\CustomerOrderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class OrderCreatedNotificationsListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CustomerOrderNotification $event): void
    {
        /**
         * It can be SMS, Email, Website Socket Notification using Socket
         * I've just printed in laravel log for simple test
         */

        Log::info('Customer order notification event fired', ['order' => $event->order]);
    }
}
