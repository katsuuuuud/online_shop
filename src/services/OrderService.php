<?php

require_once __DIR__ . '/../Database.php';

class OrderService
{
    private const CART_COOKIE_NAME = 'market_cart';

    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function createOrder(array $orderData): array
    {
        $cartItems = isset($_COOKIE[self::CART_COOKIE_NAME])
            ? json_decode($_COOKIE[self::CART_COOKIE_NAME], true) ?: []
            : [];

        if (!$cartItems) {
            return ['success' => false, 'message' => 'Корзина пуста.'];
        }
        $name = trim((string)($orderData['name'] ?? ''));
        $email = trim((string)($orderData['email'] ?? ''));
        $phone = trim((string)($orderData['phone'] ?? ''));
        $address = trim((string)($orderData['address'] ?? ''));

        if ($name === '' || $email === '' || $phone === '' || $address === '') {
            return ['success' => false, 'message' => 'Заполните все поля формы.'];
        }

        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();

            $customerId = $this->orderRepository->saveCustomer([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
            ]);

            $totalAmount = 0.0;
            foreach ($cartItems as $item) {
                $price = (float)($item['price'] ?? 0);
                $quantity = (int)($item['quantity'] ?? 0);
                $totalAmount += $price * $quantity;
            }

            $orderId = $this->orderRepository->saveOrder($customerId, (int)round($totalAmount), $address);
            $this->orderRepository->saveOrderItems($orderId, $customerId, $cartItems);
            $this->orderRepository->clearCart();

            $pdo->commit();

            return [
                'success' => true,
                'message' => 'Заказ успешно оформлен.',
                'orderId' => $orderId,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return ['success' => false, 'message' => 'Не удалось оформить заказ: ' . $e->getMessage()];
        }
    }
}