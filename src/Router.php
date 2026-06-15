<?php

class Router
{
    public function __construct(private Container $c) {}

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($method === 'POST' && $path === '/order/create') {
            $input = $_POST;
            if (!$input) {
                $input = json_decode(file_get_contents('php://input'), true) ?: [];
            }
            header('Content-Type: application/json');
            echo json_encode($this->c->orderController->submitOrder($input));
            return;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'add_to_cart') {
            $this->c->cartController->add((int)($_GET['productId'] ?? 0), 1);
            echo json_encode(['ok' => true]);
            return;
        }

        if (isset($_GET['page']) && $_GET['page'] === 'cart') {
            if (isset($_GET['remove'])) {
                $this->c->cartController->remove((int)$_GET['remove']);
                header('Location: /?page=cart');
                return;
            }
            if (isset($_GET['clear'])) {
                $this->c->cartController->clear();
                header('Location: /?page=cart');
                return;
            }
            $this->c->cartController->show();
            return;
        }

        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $this->c->catalogController->showProducts($categoryId);
    }
}