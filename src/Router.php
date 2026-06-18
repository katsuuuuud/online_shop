<?php

class Router
{
    public function __construct(private Container $c) {}

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // ── API routes (всегда JSON) ──────────────────────────────────────
        if (str_starts_with($path, '/api/')) {
            header('Content-Type: application/json');
            $this->dispatchApi($method, $path);
            return;
        }

        // ── HTML routes ───────────────────────────────────────────────────
        $this->dispatchWeb($method, $path);
    }

    // ─────────────────────────────────────────────────────────────────────
    // REST API
    // ─────────────────────────────────────────────────────────────────────
    private function dispatchApi(string $method, string $path): void
    {
        match (true) {
            // Cart
            $method === 'GET'    && $path === '/api/cart'        => $this->apiCartIndex(),
            $method === 'POST'   && $path === '/api/cart'        => $this->apiCartAdd(),
            $method === 'DELETE' && $path === '/api/cart'        => $this->apiCartClear(),
            $method === 'DELETE' && str_starts_with($path, '/api/cart/') => $this->apiCartRemove($path),

            // Auth
            $method === 'POST'   && $path === '/api/auth/login'    => $this->apiAuthLogin(),
            $method === 'POST'   && $path === '/api/auth/register' => $this->apiAuthRegister(),
            $method === 'DELETE' && $path === '/api/auth/session'  => $this->apiAuthLogout(),

            // Orders
            $method === 'POST'   && $path === '/api/orders'        => $this->apiOrderCreate(),

            // Profile
            $method === 'PATCH'  && $path === '/api/profile'       => $this->apiProfileUpdate(),

            default => $this->apiNotFound(),
        };
    }

    // ── Cart endpoints ────────────────────────────────────────────────────

    private function apiCartIndex(): void
    {
        $items = $this->c->cartController->items();
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
        echo json_encode(['data' => array_values($items), 'total' => $total]);
    }

    private function apiCartAdd(): void
    {
        $body      = $this->jsonBody();
        $productId = (int)($body['productId'] ?? 0);
        $quantity  = max(1, (int)($body['quantity'] ?? 1));

        if ($productId <= 0) {
            $this->fail('productId обязателен', 422);
            return;
        }

        $this->c->cartController->add($productId, $quantity);
        $items = $this->c->cartController->items();
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));

        http_response_code(201);
        echo json_encode(['data' => array_values($items), 'total' => $total]);
    }

    private function apiCartRemove(string $path): void
    {
        $productId = (int)substr($path, strlen('/api/cart/'));

        if ($productId <= 0) {
            $this->fail('Неверный productId', 422);
            return;
        }

        $this->c->cartController->remove($productId);
        $items = $this->c->cartController->items();
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));

        echo json_encode(['data' => array_values($items), 'total' => $total]);
    }

    private function apiCartClear(): void
    {
        $this->c->cartController->clear();
        echo json_encode(['data' => [], 'total' => 0]);
    }

    // ── Auth endpoints ────────────────────────────────────────────────────

    private function apiAuthLogin(): void
    {
        $body   = $this->jsonBody();
        $result = $this->c->authController->login($body);

        if (!$result['success']) {
            $this->fail($result['message'], 401);
            return;
        }

        echo json_encode(['data' => $_SESSION['user']]);
    }

    private function apiAuthRegister(): void
    {
        $body   = $this->jsonBody();
        $result = $this->c->authController->register($body);

        if (!$result['success']) {
            $this->fail($result['message'], 422);
            return;
        }

        http_response_code(201);
        echo json_encode(['data' => $_SESSION['user']]);
    }

    private function apiAuthLogout(): void
    {
        $this->c->authController->logout();
        echo json_encode(['data' => null]);
    }

    // ── Order endpoints ───────────────────────────────────────────────────

    private function apiOrderCreate(): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            $this->fail('Требуется авторизация', 401);
            return;
        }

        $result = $this->c->orderController->submitOrder($user, $this->jsonBody());

        if (!$result['success']) {
            $this->fail($result['message'], 422);
            return;
        }

        http_response_code(201);
        echo json_encode(['data' => ['orderId' => $result['orderId']]]);
    }

    // ── Profile endpoints ─────────────────────────────────────────────────

    private function apiProfileUpdate(): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            $this->fail('Требуется авторизация', 401);
            return;
        }

        $result = $this->c->profileController->update($this->jsonBody(), $user);

        if (!$result['success']) {
            $this->fail($result['message'], 422);
            return;
        }

        echo json_encode(['data' => $_SESSION['user']]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // HTML pages
    // ─────────────────────────────────────────────────────────────────────
    private function dispatchWeb(string $method, string $path): void
    {
        match (true) {
            $method === 'GET'  && $path === '/cart'           => $this->c->cartController->show(),
            $method === 'GET'  && $path === '/auth/logout'    => $this->c->authController->logout(),
            $method === 'GET'  && $path === '/auth'           => $this->redirectToLogin(),
            $method === 'GET'  && $path === '/auth/login'     => $this->renderAuth('login'),
            $method === 'GET'  && $path === '/auth/register'  => $this->renderAuth('register'),
            $method === 'POST' && $path === '/auth/login'     => $this->handleWebAuthLogin(),
            $method === 'POST' && $path === '/auth/register'  => $this->handleWebAuthRegister(),
            $method === 'GET'  && $path === '/profile'        => $this->renderProfile(),
            default            => $this->renderCatalog(),
        };
    }

    private function redirectToLogin(): void
    {
        $next   = $_GET['next'] ?? '/';
        $target = '/auth/login' . ($next !== '/' ? '?next=' . urlencode($next) : '');
        header('Location: ' . $target);
    }

    private function renderAuth(string $mode): void
    {
        $next = $_GET['next'] ?? '/';
        require __DIR__ . '/../views/auth.php';
    }

    private function handleWebAuthLogin(): void
    {
        $result = $this->c->authController->login($_POST);
        $next = $result['next'] ?? '/';

        if ($result['success']) {
            header('Location: ' . $next);
            return;
        }

        $query = http_build_query([
            'next' => $next !== '/' ? $next : '/', 
            'error' => $result['message'],
        ]);
        header('Location: /auth/login?' . $query);
    }

    private function handleWebAuthRegister(): void
    {
        $result = $this->c->authController->register($_POST);
        $next = $result['next'] ?? '/';

        if ($result['success']) {
            header('Location: ' . $next);
            return;
        }

        $query = http_build_query([
            'next' => $next !== '/' ? $next : '/', 
            'error' => $result['message'],
        ]);
        header('Location: /auth/register?' . $query);
    }

    private function renderProfile(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: /auth/login?next=/profile');
            return;
        }
        $tab = $_GET['tab'] ?? 'info';
        $this->c->profileController->show($user, $tab);
    }

    private function renderCatalog(): void
    {
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $this->c->catalogController->showProducts($categoryId);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────
    private function jsonBody(): array
    {
        // Принимаем и JSON и form-data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?: [];
        }
        return $_POST ?: [];
    }

    private function fail(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['error' => $message]);
    }

    private function apiNotFound(): void
    {
        $this->fail('Маршрут не найден', 404);
    }
}