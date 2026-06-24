<?php

class AuthService
{
    public function __construct(
        private AuthRepositoryInterface $authRepository,
        private CartRepositoryInterface $cartRepository,
    ) {}

    public function login(array $data): array
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

    public function register(array $data): array
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

    public function logout(): void
    {
        $this->destroySession();
    }

    private function destroySession(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        $this->cartRepository->resetGuestCartAfterLogout();
    }
}
