<?php

class CartRepository implements CartRepositoryInterface {
    public const GUEST_COOKIE = 'guest_cart_id';
    private const COOKIE_LIFETIME  = 2592000;
    private const GUEST_KEY_PREFIX = 'cart:guest:';

    private PDO   $db;
    private Redis $redis;

    public function __construct()
    {
        $this->db    = Database::getInstance();
        $this->redis = RedisClient::getInstance()->getRedis();
    }

    public function getItems(): array
    {
        $user = $_SESSION['user'] ?? null;
        if ($user && isset($user['userId'])) {
            $cartId = $this->ensureCartForUser((int)$user['userId']);
            return $this->getItemsByCartId($cartId);
        }

        return $this->getGuestItems();
    }

    public function addItem(int $productId, string $name, float $price, string $currency, int $quantity = 1): array
    {
        $user = $_SESSION['user'] ?? null;

        if ($user && isset($user['userId'])) {
            $cartId = $this->ensureCartForUser((int)$user['userId']);

            $stmt = $this->db->prepare(
                'INSERT INTO cart_items (cart_id, product_id, quantity, price, currency)
                 VALUES (:cart_id, :product_id, :quantity, :price, :currency)
                 ON DUPLICATE KEY UPDATE quantity = quantity + :quantity2'
            );
            $stmt->execute([
                'cart_id'    => $cartId,
                'product_id' => $productId,
                'quantity'   => $quantity,
                'price'      => $price,
                'currency'   => $currency,
                'quantity2'  => $quantity,
            ]);

            return $this->getItemsByCartId($cartId);
        }

        $items = $this->getGuestItems();
        $key   = (string)$productId;

        if (isset($items[$key])) {
            $items[$key]['quantity'] += $quantity;
        } else {
            $items[$key] = [
                'productId' => $productId,
                'name'      => $name,
                'price'     => $price,
                'currency'  => $currency,
                'quantity'  => $quantity,
            ];
        }

        $this->saveGuestItems($items);
        return $items;
    }

    public function removeItem(int $productId): array
    {
        $user = $_SESSION['user'] ?? null;

        if ($user && isset($user['userId'])) {
            $cartId = $this->ensureCartForUser((int)$user['userId']);

            $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?');
            $stmt->execute([$cartId, $productId]);

            return $this->getItemsByCartId($cartId);
        }

        $items = $this->getGuestItems();
        unset($items[(string)$productId]);
        $this->saveGuestItems($items);
        return $items;
    }

    public function clear(): void
    {
        $user = $_SESSION['user'] ?? null;

        if ($user && isset($user['userId'])) {
            $cartId = $this->ensureCartForUser((int)$user['userId']);
            $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = ?');
            $stmt->execute([$cartId]);
            return;
        }

        $this->saveGuestItems([]);
    }

    public function mergeGuestCartToUser(int $userId, ?string $guestId): void
    {
        if (!$guestId || !$this->isValidUuid($guestId)) {
            return;
        }

        $guestItems = $this->getGuestItemsByGuestId($guestId);

        if ($guestItems) {
            $cartId = $this->ensureCartForUser($userId);

            $stmt = $this->db->prepare(
                'INSERT INTO cart_items (cart_id, product_id, quantity, price, currency)
                 VALUES (:cart_id, :product_id, :quantity, :price, :currency)
                 ON DUPLICATE KEY UPDATE quantity = quantity + :quantity2'
            );

            foreach ($guestItems as $item) {
                $stmt->execute([
                    'cart_id'    => $cartId,
                    'product_id' => $item['productId'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'currency'   => $item['currency'],
                    'quantity2'  => $item['quantity'],
                ]);
            }
        }

        $this->redis->del(self::GUEST_KEY_PREFIX . $guestId);
        $this->clearGuestCookie();
    }

    public function resetGuestCartAfterLogout(): void
    {
        $this->clearGuestCookie();
        $this->setGuestCookie($this->generateUuidV4());
    }


    private function ensureCartForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT cartId FROM carts WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return (int)$cart['cartId'];
        }

        $insert = $this->db->prepare('INSERT INTO carts (user_id) VALUES (?)');
        $insert->execute([$userId]);

        return (int)$this->db->lastInsertId();
    }

    private function getItemsByCartId(int $cartId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ci.product_id, ci.quantity, ci.price, ci.currency, p.name
             FROM cart_items ci
             JOIN products p ON p.productId = ci.product_id
             WHERE ci.cart_id = ?'
        );
        $stmt->execute([$cartId]);

        $items = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $items[(string)$row['product_id']] = [
                'productId' => (int)$row['product_id'],
                'name'      => $row['name'],
                'price'     => (float)$row['price'],
                'currency'  => $row['currency'],
                'quantity'  => (int)$row['quantity'],
            ];
        }

        return $items;
    }

    private function getGuestItems(): array
    {
        return $this->getGuestItemsByGuestId($this->ensureGuestId());
    }

    private function getGuestItemsByGuestId(string $guestId): array
    {
        $raw = $this->redis->get(self::GUEST_KEY_PREFIX . $guestId);
        return $raw ? $this->decodeItems($raw) : [];
    }

    private function saveGuestItems(array $items): void
    {
        $guestId = $this->ensureGuestId();
        $this->redis->setex(
            self::GUEST_KEY_PREFIX . $guestId,
            self::COOKIE_LIFETIME,
            $this->encodeItems($items)
        );
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