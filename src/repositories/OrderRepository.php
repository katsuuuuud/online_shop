<?php

class OrderRepository implements OrderRepositoryInterface
{
    public function saveOrder(int $customerId, int $amount, string $address): int
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO orders (created_at, amount, customer_id, status, address)
             VALUES (NOW(), ?, ?, ?, ?)'
        );
        $stmt->execute([$amount, $customerId, 'new', $address]);

        return (int)$pdo->lastInsertId();
    }

    public function saveOrderItems(int $orderId, int $customerId, array $cartItems): void
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, customer_id, quantity, price, currency)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        foreach ($cartItems as $item) {
            $stmt->execute([
                $orderId,
                (int)($item['productId'] ?? 0),
                $customerId,
                (int)($item['quantity']  ?? 0),
                (float)($item['price']   ?? 0),
                strtoupper((string)($item['currency'] ?? '')),
            ]);
        }
    }

    public function getOrdersByCustomer(int $customerId): array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            'SELECT o.*, (
                 SELECT currency
                 FROM order_items oi
                 WHERE oi.order_id = o.orderId
                 LIMIT 1
             ) AS currency
             FROM orders o
             WHERE o.customer_id = ?
             ORDER BY o.created_at DESC'
        );
        $stmt->execute([$customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}