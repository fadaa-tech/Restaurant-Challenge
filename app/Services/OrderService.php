<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * The order validation service.
     *
     * @var OrderValidationService
     */
    private OrderValidationService $validationService;

    /**
     * The order calculation service.
     *
     * @var OrderCalculationService
     */
    private OrderCalculationService $calculationService;

    /**
     * The payment service.
     *
     * @var PaymentService
     */
    private PaymentService $paymentService;

    /**
     * The notification service.
     *
     * @var NotificationService
     */
    private NotificationService $notificationService;

    /**
     * The invoice service.
     *
     * @var InvoiceService
     */
    private InvoiceService $invoiceService;

    /**
     * The inventory service.
     *
     * @var InventoryService
     */
    private InventoryService $inventoryService;

    /**
     * The OrderService constructor.
     *
     * @param OrderValidationService $validationService
     * @param OrderCalculationService $calculationService
     * @param PaymentService $paymentService
     * @param NotificationService $notificationService
     * @param InvoiceService $invoiceService
     * @param InventoryService $inventoryService
     */
    public function __construct(
        OrderValidationService $validationService,
        OrderCalculationService $calculationService,
        PaymentService $paymentService,
        NotificationService $notificationService,
        InvoiceService $invoiceService,
        InventoryService $inventoryService
    ) {
        $this->validationService = $validationService;
        $this->calculationService = $calculationService;
        $this->paymentService = $paymentService;
        $this->notificationService = $notificationService;
        $this->invoiceService = $invoiceService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Validates the order, calculates order details, processes the order and payment,
     * notifies the customer, and sends an invoice.
     *
     * @param Order $order
     * @return bool
     */
    public function placeOrder(Order $order): bool
    {
        try {
            // Validate the order
            $order = $this->validationService->validateOrder($order);
            
            // Calculate order details
            $order = $this->calculationService->calculateOrderDetails($order);
            
            // Store the order
            $this->storeOrder($order);
            
            // Update inventory
            $this->inventoryService->updateInventory($order);
            
            // Process payment
            if (!$this->paymentService->processPayment($order)) {
                throw new \Exception('Payment processing failed');
            }
            
            // Notify customer
            $this->notificationService->notifyCustomer($order);
            
            // Send invoice
            $this->invoiceService->sendInvoice($order);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Order placement failed: ' . $e->getMessage(), [
                'order_id' => $order->id ?? 'unknown'
            ]);
            return false;
        }
    }

    /**
     * Stores the order in the database or any other storage mechanism.
     *
     * @param Order $order
     * @return void
     */
    private function storeOrder(Order $order): void
    {
        $order->save();
    }
}
