<?php

class CartRepository implements CartRepositoryInterface {
    private const COOKIE_NAME = 'market_cart';

    public function getItems(): array {
        return isset($_COOKIE[self::COOKIE_NAME])
            ? json_decode($_COOKIE[self::COOKIE_NAME], true) ?: []
            : [];
    }

    public function addItem(int $productId, string $name, float $price, string $currency, int $quantity = 1): array {
        $items = $this->getItems();

        if (isset($items[$productId])) {
            $items[$productId]['quantity'] += $quantity;
        } else {
            $items[$productId] = [
                'productId' => $productId,
                'name' => $name,
                'price' => $price,
                'currency' => $currency,
                'quantity' => $quantity,
            ];
        }

        setcookie(self::COOKIE_NAME, json_encode($items, JSON_UNESCAPED_UNICODE), time() + 60 * 60 * 24 * 30, '/');
        $_COOKIE[self::COOKIE_NAME] = json_encode($items, JSON_UNESCAPED_UNICODE);

        return $items;
    }

    public function removeItem(int $productId): array {
        $items = $this->getItems();
        unset($items[$productId]);

        setcookie(self::COOKIE_NAME, json_encode($items, JSON_UNESCAPED_UNICODE), time() + 60 * 60 * 24 * 30, '/');
        $_COOKIE[self::COOKIE_NAME] = json_encode($items, JSON_UNESCAPED_UNICODE);

        return $items;
    }

    public function clear(): void {
        setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
        unset($_COOKIE[self::COOKIE_NAME]);
    }
}
