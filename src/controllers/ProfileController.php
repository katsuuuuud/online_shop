<?php

class ProfileController
{
    private AuthRepositoryInterface $authRepository;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(AuthRepositoryInterface $authRepository, OrderRepositoryInterface $orderRepository)
    {
        $this->authRepository = $authRepository;
        $this->orderRepository = $orderRepository;
    }

    public function show(array $user, string $tab = 'info'): void
    {
        $orders = $this->orderRepository->getOrdersByCustomer((int)($user['id'] ?? $user['userId'] ?? 0));
        require __DIR__ . '/../../views/profile.php';
    }

    public function update(array $data, array $user): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $address = trim((string)($data['address'] ?? ''));
        $tab = $data['tab'] ?? 'info';
        $userId = (int)($user['id'] ?? $user['userId'] ?? 0);

        if ($name === '' || $phone === '' || $address === '') {
            return ['success' => false, 'message' => 'Все поля профиля должны быть заполнены.', 'tab' => $tab];
        }

        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Не удалось определить пользователя.', 'tab' => $tab];
        }

        try {
            $this->authRepository->updateProfile($userId, $name, $phone, $address);
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Ошибка обновления профиля: ' . $e->getMessage(), 'tab' => $tab];
        }

        $updatedUser = $this->authRepository->findById($userId);
        if ($updatedUser) {
            unset($updatedUser['password']);
            $_SESSION['user'] = $updatedUser;
        } else {
            $_SESSION['user'] = array_merge($user, ['name' => $name, 'phone' => $phone, 'address' => $address]);
        }

        return ['success' => true, 'message' => 'Профиль успешно обновлён.', 'tab' => $tab];
    }
}
