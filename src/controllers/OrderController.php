<?php

class OrderController
{
    public function __construct(private OrderService $orderService) {}


    public function apiCreate(array $body): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Требуется авторизация']);
            return;
        }

        $result = $this->orderService->createOrder($user, $body);

        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['error' => $result['message']]);
            return;
        }

        http_response_code(201);
        echo json_encode(['data' => ['orderId' => $result['orderId']]]);
    }
}