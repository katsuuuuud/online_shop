<?php

class OrderController
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function submitOrder(?array $user, array $orderData): array
    {
        return $this->orderService->createOrder($user, $orderData);
    }
}
