<?php

class ProfileController
{
    public function __construct(private ProfileService $profileService) {}

    public function apiUpdate(array $body): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Требуется авторизация']);
            return;
        }

        $result = $this->profileService->updateProfile($body, $user);

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

        $tab     = $_GET['tab'] ?? 'info';
        $profile = $this->profileService->getProfileData($user);
        $orders  = $profile['orders'];

        require __DIR__ . '/../../views/profile.php';
    }

    public function handleUpdate(array $post): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            header('Location: /auth/login?next=/profile');
            return;
        }

        $result = $this->profileService->updateProfile($post, $user);
        $tab    = $post['tab'] ?? 'info';

        if (!$result['success']) {
            header('Location: /profile?tab=' . urlencode($tab) . '&error=' . urlencode($result['message']));
            return;
        }

        header('Location: /profile?tab=' . urlencode($tab) . '&success=' . urlencode($result['message']));
    }
}
