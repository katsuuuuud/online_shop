<?php

require_once __DIR__ . '/../Database.php';

class OrderService
{
    private OrderRepositoryInterface $orderRepository;
    private CartRepositoryInterface $cartRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, CartRepositoryInterface $cartRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    public function createOrder(?array $user, array $orderData): array
    {
        if (!$user) {
            return ['success' => false, 'message' => 'Требуется войти в систему.'];
        }

        $cartItems = $this->cartRepository->getItems();
        if (!$cartItems) {
            return ['success' => false, 'message' => 'Корзина пуста.'];
        }

        $userId = (int)($user['id'] ?? $user['userId'] ?? 0);
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Не удалось определить пользователя.'];
        }

        $address = trim((string)($user['address'] ?? ''));
        $totalAmount = 0.0;
        foreach ($cartItems as $item) {
            $price = (float)($item['price'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);
            $totalAmount += $price * $quantity;
        }

        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();
            $orderId = $this->orderRepository->saveOrder($userId, (int)round($totalAmount), $address);
            $this->orderRepository->saveOrderItems($orderId, $userId, $cartItems);
            $this->cartRepository->clear();
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