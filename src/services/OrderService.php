<?php

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CartRepositoryInterface  $cartRepository,
    ) {}

    public function createOrder(?array $user, array $orderData): array
    {
        if (!$user) {
            return ['success' => false, 'message' => 'Требуется войти в систему.'];
        }

        $userId = (int)($user['id'] ?? $user['userId'] ?? 0);
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Не удалось определить пользователя.'];
        }

        $cartItems = $this->cartRepository->getItems();
        if (!$cartItems) {
            return ['success' => false, 'message' => 'Корзина пуста.'];
        }

        $address     = trim((string)($user['address'] ?? ''));
        $totalAmount = array_sum(
            array_map(fn($item) => (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0), $cartItems)
        );

        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();
            $orderId = $this->orderRepository->saveOrder($userId, (int)round($totalAmount), $address);
            $this->orderRepository->saveOrderItems($orderId, $userId, $cartItems);
            $this->cartRepository->clear();
            $pdo->commit();

            return ['success' => true, 'message' => 'Заказ успешно оформлен.', 'orderId' => $orderId];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Не удалось оформить заказ: ' . $e->getMessage()];
        }
    }
}