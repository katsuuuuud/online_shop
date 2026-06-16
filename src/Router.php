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

        if ($method === 'POST' && $path === '/cart/add') {
            $productId = (int)($_POST['productId'] ?? 0);
            $this->c->cartController->add($productId, 1);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            return;
        }

        if ($method === 'POST' && $path === '/cart/remove') {
            $productId = (int)($_POST['productId'] ?? 0);
            $this->c->cartController->remove($productId);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            return;
        }

        if ($method === 'POST' && $path === '/cart/clear') {
            $this->c->cartController->clear();
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            return;
        }

        if ($method === 'GET' && $path === '/cart') {
            $this->c->cartController->show();
            return;
        }

        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $this->c->catalogController->showProducts($categoryId);
    }
}