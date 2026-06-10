<?php
class CatalogController {
    private CatalogRepositoryInterface $repo;

    public function __construct(CatalogRepositoryInterface $repo) {
        $this->repo = $repo;
    }
    public function showProducts(?int $categoryId = null): void {
        $categories = $this->repo->getCategories();
        $products = $categoryId
            ? $this->repo->getProductsByCategory($categoryId)
            : $this->repo->getProducts();
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['categoryId']] = $cat['name'];
        }
        foreach ($products as &$product) {
            $price = $this->repo->getPriceByProduct($product['productId']);
            $product['price']         = $price ? $price['price'] : null;
            $product['currency']      = $price ? $price['currency'] : null;
            $product['category_name'] = $categoryMap[$product['category_id']] ?? '—';
        }
        unset($product);

        $activeCategoryId = $categoryId;

        require __DIR__ . '/../views/products.php';
    }

    public function addToCart(int $productId, int $quantity): void {
        
    }
}