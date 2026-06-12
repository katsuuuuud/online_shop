<?php

interface OrderRepositoryInterface
{
    public function saveCustomer(array $customerData): int;

    public function saveOrder(int $customerId, int $amount, string $address): int;

    public function saveOrderItems(int $orderId, int $customerId, array $cartItems): void;

    public function clearCart(): void;
}