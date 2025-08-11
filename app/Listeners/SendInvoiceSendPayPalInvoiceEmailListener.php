<?php

namespace App\Listeners;

use App\Events\PaymentCaptured;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendInvoiceSendPayPalInvoiceEmailListener
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
    public function handle(PaymentCaptured $event): void
    {
        $data = [
            'invoice_id'     => $event->captureResponse['purchase_units'][0]['payments']['captures'][0]['id'],
            'payer_name'     => $event->captureResponse['payer']['name']['given_name'] . ' ' . $event->captureResponse['payer']['name']['surname'],
            'amount'         => $event->captureResponse['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
            'currency'       => $event->captureResponse['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
            'due_date'       => (new DateTime($event->captureResponse['purchase_units'][0]['payments']['captures'][0]['create_time']))->format('Y-m-d H:i A'),
            'view_url'       => url('/'),
        ];


        Mail::send('mails.paypalInvoice', $data, function ($message) use ($data) {
            // Add recipient email for test
            $message->to(env('MAIL_RECIPIENT_TEST'))
                ->subject("Your Payment Invoice #{$data['invoice_id']}");
        });
    }
}
