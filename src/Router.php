<?php

class Router
{
    public function __construct(private Container $c) {}

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (str_starts_with($path, '/api/')) {
            header('Content-Type: application/json');
            $this->dispatchApi($method, $path);
            return;
        }

        $this->dispatchWeb($method, $path);
    }


    private function dispatchApi(string $method, string $path): void
    {
        match (true) {
            $method === 'GET'    && $path === '/api/cart'                        => $this->c->cartController->apiIndex(),
            $method === 'POST'   && $path === '/api/cart'                        => $this->c->cartController->apiAdd($this->jsonBody()),
            $method === 'DELETE' && $path === '/api/cart'                        => $this->c->cartController->apiClear(),
            $method === 'DELETE' && str_starts_with($path, '/api/cart/')         => $this->c->cartController->apiRemove($path),
            $method === 'POST'   && $path === '/api/auth/login'                  => $this->c->authController->apiLogin($this->jsonBody()),
            $method === 'POST'   && $path === '/api/auth/register'               => $this->c->authController->apiRegister($this->jsonBody()),
            $method === 'DELETE' && $path === '/api/auth/session'                => $this->c->authController->apiLogout(),
            $method === 'POST'   && $path === '/api/orders'                      => $this->c->orderController->apiCreate($this->jsonBody()),
            $method === 'PATCH'  && $path === '/api/profile'                     => $this->c->profileController->apiUpdate($this->jsonBody()),

            default => $this->apiNotFound(),
        };
    }


    private function dispatchWeb(string $method, string $path): void
    {
        match (true) {
            $method === 'GET'  && $path === '/cart'          => $this->c->cartController->show(),
            $method === 'GET'  && $path === '/auth/logout'   => $this->c->authController->logout(),
            $method === 'GET'  && $path === '/auth'          => $this->redirectToLogin(),
            $method === 'GET'  && $path === '/auth/login'    => $this->c->authController->showLogin(),
            $method === 'GET'  && $path === '/auth/register' => $this->c->authController->showRegister(),
            $method === 'POST' && $path === '/auth/login'    => $this->c->authController->handleLogin($_POST),
            $method === 'POST' && $path === '/auth/register' => $this->c->authController->handleRegister($_POST),
            $method === 'GET'  && $path === '/profile'        => $this->c->profileController->show(),
            $method === 'POST' && $path === '/profile'        => $this->c->profileController->handleUpdate($_POST),
            default                                           => $this->c->catalogController->showProducts(),
        };
    }


    private function redirectToLogin(): void
    {
        $next   = $_GET['next'] ?? '/';
        $target = '/auth/login' . ($next !== '/' ? '?next=' . urlencode($next) : '');
        header('Location: ' . $target);
    }

    private function jsonBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?: [];
        }
        return $_POST ?: [];
    }

    private function apiNotFound(): void
    {
        http_response_code(404);
        echo json_encode(['error' => 'Маршрут не найден']);
    }
}