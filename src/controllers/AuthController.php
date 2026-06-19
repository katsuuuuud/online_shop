<?php

class AuthController
{
    public function __construct(
        private AuthRepositoryInterface $authRepository,
        private CartRepositoryInterface $cartRepository,
    ) {}


    public function apiLogin(array $body): void
    {
        $result = $this->login($body);

        if (!$result['success']) {
            http_response_code(401);
            echo json_encode(['error' => $result['message']]);
            return;
        }

        echo json_encode(['data' => $_SESSION['user']]);
    }

    public function apiRegister(array $body): void
    {
        $result = $this->register($body);

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
        $this->destroySession();
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
        $result = $this->login($post);
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
        $result = $this->register($post);
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
        $this->destroySession();
        header('Location: /');
        exit;
    }


    private function login(array $data): array
    {
        $email    = trim((string)($data['email']    ?? ''));
        $password = trim((string)($data['password'] ?? ''));
        $next     = trim((string)($data['next']     ?? '/'));

        if ($email === '' || $password === '') {
            return ['success' => false, 'message' => 'Введите email и пароль.', 'next' => $next];
        }

        $user = $this->authRepository->login($email, $password);
        if (!$user) {
            return ['success' => false, 'message' => 'Неверный email или пароль.', 'next' => $next];
        }

        $this->cartRepository->mergeGuestCartToUser((int)$user['userId'], $_COOKIE[CartRepository::GUEST_COOKIE] ?? null);
        $_SESSION['user'] = $user;
        return ['success' => true, 'message' => 'Вы успешно вошли.', 'next' => $next];
    }

    private function register(array $data): array
    {
        $name     = trim((string)($data['name']     ?? ''));
        $email    = trim((string)($data['email']    ?? ''));
        $phone    = trim((string)($data['phone']    ?? ''));
        $address  = trim((string)($data['address']  ?? ''));
        $password = trim((string)($data['password'] ?? ''));
        $next     = trim((string)($data['next']     ?? '/'));

        if ($name === '' || $email === '' || $phone === '' || $address === '' || $password === '') {
            return ['success' => false, 'message' => 'Заполните все поля для регистрации.', 'next' => $next];
        }

        if ($this->authRepository->findByEmail($email)) {
            return ['success' => false, 'message' => 'Пользователь с таким email уже зарегистрирован.', 'next' => $next];
        }

        try {
            $this->authRepository->register($name, $email, $phone, $address, $password);
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Ошибка регистрации: ' . $e->getMessage(), 'next' => $next];
        }

        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Не удалось создать пользователя.', 'next' => $next];
        }

        unset($user['password']);
        $this->cartRepository->mergeGuestCartToUser((int)$user['userId'], $_COOKIE[CartRepository::GUEST_COOKIE] ?? null);
        $_SESSION['user'] = $user;

        return ['success' => true, 'message' => 'Регистрация прошла успешно.', 'next' => $next];
    }

    private function destroySession(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly'],
            );
        }
        session_destroy();
        $this->cartRepository->resetGuestCartAfterLogout();
    }
}