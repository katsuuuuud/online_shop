<?php

class ProfileController
{
    public function __construct(
        private AuthRepositoryInterface  $authRepository,
        private OrderRepositoryInterface $orderRepository,
    ) {}


    public function apiUpdate(array $body): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Требуется авторизация']);
            return;
        }

        $result = $this->update($body, $user);

        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['error' => $result['message']]);
            return;
        }

        echo json_encode(['data' => $_SESSION['user']]);
    }


    public function show(): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            header('Location: /auth/login?next=/profile');
            return;
        }

        $tab    = $_GET['tab'] ?? 'info';
        $orders = $this->orderRepository->getOrdersByCustomer(
            (int)($user['id'] ?? $user['userId'] ?? 0)
        );

        require __DIR__ . '/../../views/profile.php';
    }

    public function handleUpdate(array $post): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            header('Location: /auth/login?next=/profile');
            return;
        }

        $result = $this->update($post, $user);
        $tab    = $post['tab'] ?? 'info';

        if (!$result['success']) {
            header('Location: /profile?tab=' . urlencode($tab) . '&error=' . urlencode($result['message']));
            return;
        }

        header('Location: /profile?tab=' . urlencode($tab) . '&success=' . urlencode($result['message']));
    }


    private function update(array $data, array $user): array
    {
        $name    = trim((string)($data['name']    ?? ''));
        $phone   = trim((string)($data['phone']   ?? ''));
        $address = trim((string)($data['address'] ?? ''));
        $userId  = (int)($user['id'] ?? $user['userId'] ?? 0);

        if ($name === '' || $phone === '' || $address === '') {
            return ['success' => false, 'message' => 'Все поля профиля должны быть заполнены.'];
        }

        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Не удалось определить пользователя.'];
        }

        try {
            $this->authRepository->updateProfile($userId, $name, $phone, $address);
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Ошибка обновления профиля: ' . $e->getMessage()];
        }

        $updatedUser = $this->authRepository->findById($userId);
        if ($updatedUser) {
            unset($updatedUser['password']);
            $_SESSION['user'] = $updatedUser;
        } else {
            $_SESSION['user'] = array_merge($user, compact('name', 'phone', 'address'));
        }

        return ['success' => true, 'message' => 'Профиль успешно обновлён.'];
    }
}