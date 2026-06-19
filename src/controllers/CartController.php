<?php

class CartController
{
    public function __construct(
        private CartRepositoryInterface    $repo,
        private CatalogRepositoryInterface $catalogRepo,
    ) {}


    public function apiIndex(): void
    {
        $items = $this->repo->getItems();
        $this->jsonOk(['data' => array_values($items), 'total' => $this->calcTotal($items)]);
    }

    public function apiAdd(array $body): void
    {
        $productId = (int)($body['productId'] ?? 0);
        $quantity  = max(1, (int)($body['quantity'] ?? 1));

        if ($productId <= 0) {
            $this->jsonFail('productId обязателен', 422);
            return;
        }

        $product = $this->catalogRepo->getProductById($productId);
        if (!$product) {
            $this->jsonFail('Товар не найден', 404);
            return;
        }

        $priceData = $this->catalogRepo->getPriceByProduct($productId);
        $price     = $priceData ? (float)$priceData['price']    : 0.0;
        $currency  = $priceData ? $priceData['currency']        : 'RUB';

        $items = $this->repo->addItem($productId, $product['name'], $price, $currency, $quantity);

        http_response_code(201);
        $this->jsonOk(['data' => array_values($items), 'total' => $this->calcTotal($items)]);
    }

    public function apiRemove(string $path): void
    {
        $productId = (int)substr($path, strlen('/api/cart/'));

        if ($productId <= 0) {
            $this->jsonFail('Неверный productId', 422);
            return;
        }

        $items = $this->repo->removeItem($productId);
        $this->jsonOk(['data' => array_values($items), 'total' => $this->calcTotal($items)]);
    }

    public function apiClear(): void
    {
        $this->repo->clear();
        $this->jsonOk(['data' => [], 'total' => 0]);
    }


    public function show(): void
    {
        $items = $this->repo->getItems();
        require __DIR__ . '/../../views/cart.php';
    }


    private function calcTotal(array $items): float
    {
        return array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
    }

    private function jsonOk(array $payload): void
    {
        echo json_encode($payload);
    }

    private function jsonFail(string $message, int $code): void
    {
        http_response_code($code);
        echo json_encode(['error' => $message]);
    }
}