<?php
class CatalogController {
    private CatalogRepositoryInterface $repo;

    public function __construct(CatalogRepositoryInterface $repo) {
        $this->repo = $repo;
    }

    public function showProducts(): void {
        $products = $this->repo->getProducts();
        require __DIR__ . '/../views/products.php';
    }

    public function showProduct(int $id): void {
        $product  = $this->repo->getProductById($id);
        $price    = $this->repo->getPriceByProduct($id);
        require __DIR__ . '/../views/product.php';
    }

    public function showCategories(): void {
        $categories = $this->repo->getCategories();
        require __DIR__ . '/../views/categories.php';
    }

    public function showCategory(int $id): void {
        $category = $this->repo->getCategoryById($id);
        $products = $this->repo->getProductsByCategory($id);
        require __DIR__ . '/../views/category.php';
    }

    public function showPrices(): void {
        $prices = $this->repo->getPrices();
        require __DIR__ . '/../views/prices.php';
    }
}