<?php

interface OrderRepositoryInterface
{
    public function saveOrder(int $customerId, int $amount, string $address): int;

    public function saveOrderItems(int $orderId, int $customerId, array $cartItems): void;

    public function getOrdersByCustomer(int $customerId): array;
}