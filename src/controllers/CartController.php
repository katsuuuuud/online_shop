<?php

class CartController
{
    public function __construct(private CartService $cartService) {}

    public function apiIndex(): void
    {
        $result = $this->cartService->getCart();
        $this->jsonOk(['data' => $result['data'], 'total' => $result['total']]);
    }

    public function apiAdd(array $body): void
    {
        $result = $this->cartService->addItem($body);

        if (!$result['success']) {
            $this->jsonFail($result['message'], $result['status'] ?? 422);
            return;
        }

        http_response_code(201);
        $this->jsonOk(['data' => $result['data'], 'total' => $result['total']]);
    }

    public function apiRemove(string $path): void
    {
        $result = $this->cartService->removeItem($path);

        if (!$result['success']) {
            $this->jsonFail($result['message'], $result['status'] ?? 422);
            return;
        }

        $this->jsonOk(['data' => $result['data'], 'total' => $result['total']]);
    }

    public function apiClear(): void
    {
        $result = $this->cartService->clearCart();
        $this->jsonOk(['data' => $result['data'], 'total' => $result['total']]);
    }

    public function show(): void
    {
        $cartData = $this->cartService->getCartView();
        $items    = $cartData['items'];
        require __DIR__ . '/../../views/cart.php';
    }

    private function jsonOk(array $payload): void
    {
        echo json_encode($payload);
    }

    private function jsonFail(string $message, int $code): void
    {
        http_response_code($code);
        echo json_encode(['error' => $message]);
    }
}
