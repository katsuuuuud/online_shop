<?php

require_once __DIR__ . '/../Database.php';

class OrderRepository implements OrderRepositoryInterface
{
    private const CART_COOKIE_NAME = 'market_cart';

    public function saveCustomer(array $customerData): int
    {
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $customerData['name'],
            $customerData['email'],
            $customerData['phone'],
            $customerData['address'],
            $customerData['password'],

        ]);

        return (int)$pdo->lastInsertId();
    }

    public function saveOrder(int $customerId, int $amount, string $address): int
    {
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare('INSERT INTO orders (created_at, amount, customer_id, status, address) VALUES (NOW(), ?, ?, ?, ?)');
        $stmt->execute([
            $amount,
            $customerId,
            'new',
            $address,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public function saveOrderItems(int $orderId, int $customerId, array $cartItems): void
    {
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, customer_id, quantity, price, currency) VALUES (?, ?, ?, ?, ?, ?)');

        foreach ($cartItems as $item) {
            $price = (float)($item['price'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);
            $currency = strtoupper((string)($item['currency'] ?? 'RUB'));

            $stmt->execute([
                $orderId,
                (int)($item['productId'] ?? 0),
                $customerId,
                $quantity,
                $price,
                $currency,
            ]);
        }
    }

    public function clearCart(): void
    {
        setcookie(self::CART_COOKIE_NAME, '', time() - 3600, '/');
        unset($_COOKIE[self::CART_COOKIE_NAME]);
    }

    public function getOrdersByCustomer(int $customerId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC');
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
