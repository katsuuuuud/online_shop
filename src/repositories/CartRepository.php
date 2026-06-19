<?php

class CartRepository implements CartRepositoryInterface {
    public const GUEST_COOKIE = 'guest_cart_id';
    private const COOKIE_LIFETIME = 2592000; // 30 days

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getItems(): array
    {
        $cart = $this->getCurrentCartRow();
        return $cart ? $this->decodeItems($cart['items']) : [];
    }

    public function addItem(int $productId, string $name, float $price, string $currency, int $quantity = 1): array
    {
        $items = $this->getItems();
        $key = (string)$productId;

        if (isset($items[$key])) {
            $items[$key]['quantity'] += $quantity;
        } else {
            $items[$key] = [
                'productId' => $productId,
                'name' => $name,
                'price' => $price,
                'currency' => $currency,
                'quantity' => $quantity,
            ];
        }

        $this->saveItemsToCurrentCart($items);
        return $items;
    }

    public function removeItem(int $productId): array
    {
        $items = $this->getItems();
        unset($items[(string)$productId]);

        $this->saveItemsToCurrentCart($items);
        return $items;
    }

    public function clear(): void
    {
        $this->saveItemsToCurrentCart([]);
    }

    public function mergeGuestCartToUser(int $userId, ?string $guestId): void
    {
        if (!$guestId || !$this->isValidUuid($guestId)) {
            return;
        }

        $guestCart = $this->getCartByGuestId($guestId);
        if (!$guestCart) {
            $this->clearGuestCookie();
            return;
        }

        $guestItems = $this->decodeItems($guestCart['items']);
        $userCart   = $this->getCartByUserId($userId);
        $userItems  = $this->decodeItems($userCart['items']);

        foreach ($guestItems as $productId => $item) {
            $key = (string)$productId;
            if (isset($userItems[$key])) {
                $userItems[$key]['quantity'] += $item['quantity'];
            } else {
                $userItems[$key] = $item;
            }
        }

        $this->saveItemsToCartId((int)$userCart['cartId'], $userItems);
        $this->deleteCartById((int)$guestCart['cartId']);
        $this->clearGuestCookie();
    }

    public function resetGuestCartAfterLogout(): void
    {
        $this->clearGuestCookie();
        $this->setGuestCookie($this->generateUuidV4());
    }

    private function getCurrentCartRow(): ?array
    {
        $user = $_SESSION['user'] ?? null;
        if ($user && isset($user['userId'])) {
            return $this->getCartByUserId((int)$user['userId']);
        }

        return $this->getGuestCartRow();
    }

    private function getCartByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM carts WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return $cart;
        }

        $insert = $this->db->prepare('INSERT INTO carts (user_id, guest_id, items) VALUES (?, NULL, ?)');
        $insert->execute([$userId, json_encode([], JSON_UNESCAPED_UNICODE)]);

        return $this->getCartByUserId($userId);
    }

    private function getGuestCartRow(): ?array
    {
        $guestId = $this->ensureGuestId();
        return $this->getCartByGuestId($guestId);
    }

    private function getCartByGuestId(string $guestId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM carts WHERE guest_id = ? LIMIT 1');
        $stmt->execute([$guestId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return $cart;
        }

        $insert = $this->db->prepare('INSERT INTO carts (user_id, guest_id, items) VALUES (NULL, ?, ?)');
        $insert->execute([$guestId, json_encode([], JSON_UNESCAPED_UNICODE)]);

        return $this->getCartByGuestId($guestId);
    }

    private function saveItemsToCurrentCart(array $items): void
    {
        $cart = $this->getCurrentCartRow();
        if (!$cart) {
            return;
        }

        $this->saveItemsToCartId((int)$cart['cartId'], $items);
    }

    private function saveItemsToCartId(int $cartId, array $items): void
    {
        $stmt = $this->db->prepare('UPDATE carts SET items = ? WHERE cartId = ?');
        $stmt->execute([$this->encodeItems($items), $cartId]);
    }

    private function deleteCartById(int $cartId): void
    {
        $stmt = $this->db->prepare('DELETE FROM carts WHERE cartId = ?');
        $stmt->execute([$cartId]);
    }

    private function ensureGuestId(): string
    {
        $guestId = $_COOKIE[self::GUEST_COOKIE] ?? '';
        if ($guestId && $this->isValidUuid($guestId)) {
            return $guestId;
        }

        $guestId = $this->generateUuidV4();
        $this->setGuestCookie($guestId);
        return $guestId;
    }

    private function setGuestCookie(string $guestId): void
    {
        setcookie(self::GUEST_COOKIE, $guestId, time() + self::COOKIE_LIFETIME, '/');
        $_COOKIE[self::GUEST_COOKIE] = $guestId;
    }

    private function clearGuestCookie(): void
    {
        setcookie(self::GUEST_COOKIE, '', time() - 3600, '/');
        unset($_COOKIE[self::GUEST_COOKIE]);
    }

    private function decodeItems(?string $items): array
    {
        if ($items === null || $items === '') {
            return [];
        }

        $decoded = json_decode($items, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function encodeItems(array $items): string
    {
        return json_encode($items, JSON_UNESCAPED_UNICODE);
    }

    private function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function isValidUuid(string $uuid): bool
    {
        return (bool)preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }
}
