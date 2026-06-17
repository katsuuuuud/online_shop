<?php

interface AuthRepositoryInterface
{
    public function register(string $name, string $email, string $phone, string $address, string $password): int;
    public function findByEmail(string $email): ?array;
    public function findById(int $id): ?array;
    public function updateProfile(int $userId, string $name, string $phone, string $address): void;
    public function login(string $email, string $password): ?array;
}
    