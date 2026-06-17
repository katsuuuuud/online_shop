<?php

class Router
{
    public function __construct(private Container $c) {}

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($method === 'POST' && $path === '/order/create') {
            $user = $_SESSION['user'] ?? null;
            if (!$user) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Требуется войти в систему.',
                    'redirect' => '/auth/login?next=/cart'
                ]);
                return;
            }

            $input = $_POST;
            if (!$input) {
                $input = json_decode(file_get_contents('php://input'), true) ?: [];
            }
            header('Content-Type: application/json');
            echo json_encode($this->c->orderController->submitOrder($user, $input));
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

        if ($method === 'GET' && $path === '/auth/logout') {
            $this->c->authController->logout();
            return;
        }

        if ($method === 'GET' && $path === '/auth') {
            $next = $_GET['next'] ?? '/';
            $target = '/auth/login';
            if ($next !== '/') {
                $target .= '?next=' . urlencode($next);
            }
            header('Location: ' . $target);
            return;
        }

        if ($method === 'GET' && $path === '/auth/login') {
            $mode = 'login';
            $next = $_GET['next'] ?? '/';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        if ($method === 'GET' && $path === '/auth/register') {
            $mode = 'register';
            $next = $_GET['next'] ?? '/';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        if ($method === 'GET' && $path === '/profile') {
            $user = $_SESSION['user'] ?? null;
            if (!$user) {
                header('Location: /auth/login?next=/profile');
                return;
            }
            $tab = $_GET['tab'] ?? 'info';
            $this->c->profileController->show($user, $tab);
            return;
        }

        if ($method === 'POST' && $path === '/profile/update') {
            $user = $_SESSION['user'] ?? null;
            if (!$user) {
                header('Location: /auth/login?next=/profile');
                return;
            }
            $result = $this->c->profileController->update($_POST, $user);
            if ($result['success']) {
                header('Location: /profile?tab=' . urlencode($result['tab']) . '&success=' . urlencode($result['message']));
                return;
            }
            header('Location: /profile?tab=' . urlencode($result['tab']) . '&error=' . urlencode($result['message']));
            return;
        }

        if ($method === 'POST' && $path === '/auth/login') {
            $result = $this->c->authController->login($_POST);
            if ($result['success']) {
                header('Location: ' . ($result['next'] ?? '/'));
                return;
            }
            header('Location: /auth/login?error=' . urlencode($result['message']) . '&next=' . urlencode($result['next'] ?? '/'));
            return;
        }

        if ($method === 'POST' && $path === '/auth/register') {
            $result = $this->c->authController->register($_POST);
            if ($result['success']) {
                header('Location: ' . ($result['next'] ?? '/'));
                return;
            }
            header('Location: /auth/register?error=' . urlencode($result['message']) . '&next=' . urlencode($result['next'] ?? '/'));
            return;
        }

        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $this->c->catalogController->showProducts($categoryId);
    }
}