<?php

class ProfileService
{
    public function __construct(
        private AuthRepositoryInterface  $authRepository,
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function updateProfile(array $data, array $user): array
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

    public function getProfileData(array $user): array
    {
        $userId = (int)($user['id'] ?? $user['userId'] ?? 0);
        return [
            'orders' => $this->orderRepository->getOrdersByCustomer($userId),
        ];
    }
}
