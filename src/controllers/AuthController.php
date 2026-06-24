<?php

class AuthController
{
    public function __construct(private AuthService $authService) {}

    public function apiLogin(array $body): void
    {
        $result = $this->authService->login($body);

        if (!$result['success']) {
            http_response_code(401);
            echo json_encode(['error' => $result['message']]);
            return;
        }

        echo json_encode(['data' => $_SESSION['user']]);
    }

    public function apiRegister(array $body): void
    {
        $result = $this->authService->register($body);

        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['error' => $result['message']]);
            return;
        }

        http_response_code(201);
        echo json_encode(['data' => $_SESSION['user']]);
    }

    public function apiLogout(): void
    {
        $this->authService->logout();
        echo json_encode(['data' => null]);
    }

    public function showLogin(): void
    {
        $mode = 'login';
        $next = $_GET['next'] ?? '/';

        if ($_SESSION['user'] ?? null) {
            header('Location: /');
            return;
        }

        require __DIR__ . '/../../views/auth.php';
    }

    public function showRegister(): void
    {
        $mode = 'register';
        $next = $_GET['next'] ?? '/';

        if ($_SESSION['user'] ?? null) {
            header('Location: /');
            return;
        }

        require __DIR__ . '/../../views/auth.php';
    }

    public function handleLogin(array $post): void
    {
        $result = $this->authService->login($post);
        $next   = $result['next'] ?? '/';

        if ($result['success']) {
            header('Location: ' . $next);
            return;
        }

        header('Location: /auth/login?' . http_build_query([
            'next'  => $next,
            'error' => $result['message'],
        ]));
    }

    public function handleRegister(array $post): void
    {
        $result = $this->authService->register($post);
        $next   = $result['next'] ?? '/';

        if ($result['success']) {
            header('Location: ' . $next);
            return;
        }

        header('Location: /auth/register?' . http_build_query([
            'next'  => $next,
            'error' => $result['message'],
        ]));
    }

    public function logout(): void
    {
        $this->authService->logout();
        header('Location: /');
        exit;
    }
}
