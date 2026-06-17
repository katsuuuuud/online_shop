<?php

class AuthRepository implements AuthRepositoryInterface
{
    public function register(string $name, string $email, string $phone, string $address, string $password): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $name,
            $email,
            $phone,
            $address,
            password_hash($password, PASSWORD_DEFAULT),
        ]);
        return $pdo->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE userId = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function updateProfile(int $userId, string $name, string $phone, string $address): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, address = ? WHERE userId = ?');
        $stmt->execute([$name, $phone, $address, $userId]);
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }

        return null;
    }
}