<?php

class CartController {
    private CartRepositoryInterface $repo;
    private CatalogRepositoryInterface $catalogRepo;

    public function __construct(CartRepositoryInterface $repo, CatalogRepositoryInterface $catalogRepo) {
        $this->repo = $repo;
        $this->catalogRepo = $catalogRepo;
    }

    public function add(int $productId, int $quantity = 1): void {
        $product = $this->catalogRepo->getProductById($productId);
        if (!$product) {
            return;
        }

        $priceData = $this->catalogRepo->getPriceByProduct($productId);
        $price = $priceData ? (float)$priceData['price'] : 0;
        $currency = $priceData ? $priceData['currency'] : 'RUB';

        $this->repo->addItem($productId, $product['name'], $price, $currency, $quantity);
    }

    public function items(): array {
        return $this->repo->getItems();
    }

    public function remove(int $productId): array {
        return $this->repo->removeItem($productId);
    }

    public function clear(): void {
        $this->repo->clear();
    }

    public function show(): void {
        $items = $this->items();
        require __DIR__ . '/../views/cart.php';
    }
}
