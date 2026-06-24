<?php

class CartService
{
    public function __construct(
        private CartRepositoryInterface    $repo,
        private CatalogRepositoryInterface $catalogRepo,
    ) {}

    public function getCart(): array
    {
        $items = $this->repo->getItems();
        return ['data' => array_values($items), 'total' => $this->calcTotal($items)];
    }

    public function addItem(array $body): array
    {
        $productId = (int)($body['productId'] ?? 0);
        $quantity  = max(1, (int)($body['quantity'] ?? 1));

        if ($productId <= 0) {
            return ['success' => false, 'message' => 'productId обязателен', 'status' => 422];
        }

        $product = $this->catalogRepo->getProductById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Товар не найден', 'status' => 404];
        }

        $priceData = $this->catalogRepo->getPriceByProduct($productId);
        $price     = $priceData ? (float)$priceData['price']    : 0.0;
        $currency  = $priceData ? $priceData['currency']        : 'RUB';

        $items = $this->repo->addItem($productId, $product['name'], $price, $currency, $quantity);
        return ['success' => true, 'data' => array_values($items), 'total' => $this->calcTotal($items)];
    }

    public function removeItem(string $path): array
    {
        $productId = (int)substr($path, strlen('/api/cart/'));

        if ($productId <= 0) {
            return ['success' => false, 'message' => 'Неверный productId', 'status' => 422];
        }

        $items = $this->repo->removeItem($productId);
        return ['success' => true, 'data' => array_values($items), 'total' => $this->calcTotal($items)];
    }

    public function clearCart(): array
    {
        $this->repo->clear();
        return ['success' => true, 'data' => [], 'total' => 0];
    }

    public function getCartView(): array
    {
        return ['items' => $this->repo->getItems()];
    }

    private function calcTotal(array $items): float
    {
        return array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
    }
}
