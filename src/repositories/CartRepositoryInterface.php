<?php

interface CartRepositoryInterface {
    public function getItems(): array;
    public function addItem(int $productId, string $name, float $price, string $currency, int $quantity = 1): array;
    public function removeItem(int $productId): array;
    public function clear(): void;
    public function mergeGuestCartToUser(int $userId, ?string $guestId): void;
    public function resetGuestCartAfterLogout(): void;
}
