<?php
namespace App\Services;

use App\Models\Order;

class InvoiceService
{
    public function sendInvoice(Order $order): void
    {
        // Email invoice or SMS with invoice link
    }
}
