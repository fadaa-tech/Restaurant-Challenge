<?php
namespace App\Services;

use App\Models\Order;

class NotificationService
{
    public function notifyCustomer(Order $order): void
    {
        // e.g. send email, sms, push notification
        // Notification::send($order->customer, new OrderPlacedNotification($order));
    }
}
