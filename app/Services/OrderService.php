<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderService
{
    protected $validator;
    protected $calculator;
    protected $paymentProcessor;
    protected $inventoryManager;
    protected $notificationService;
    protected $invoiceService;

    public function __construct(
        OrderValidator $validator,
        OrderCalculator $calculator,
        PaymentProcessor $paymentProcessor,
        InventoryManager $inventoryManager,
        NotificationService $notificationService,
        InvoiceService $invoiceService
    ) {
        $this->validator = $validator;
        $this->calculator = $calculator;
        $this->paymentProcessor = $paymentProcessor;
        $this->inventoryManager = $inventoryManager;
        $this->notificationService = $notificationService;
        $this->invoiceService = $invoiceService;
    }

    public function placeOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $this->validator->validate($order);

            $order = $this->calculator->calculate($order);

            $order->save();

            $this->paymentProcessor->process($order->total_amount);

            $this->inventoryManager->updateInventory($order);

            $this->notificationService->notifyCustomer($order);

            $this->invoiceService->sendInvoice($order);
        });
    }
    
}
