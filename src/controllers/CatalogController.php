<?php

class CatalogController
{
    public function __construct(private CatalogRepositoryInterface $repo) {}

    public function showProducts(): void
    {
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

        $categories = $this->repo->getCategories();
        $products   = $categoryId
            ? $this->repo->getProductsByCategory($categoryId)
            : $this->repo->getProducts();

        $categoryMap = array_column($categories, 'name', 'categoryId');

        foreach ($products as &$product) {
            $price = $this->repo->getPriceByProduct($product['productId']);
            $product['price']         = $price ? $price['price']    : null;
            $product['currency']      = $price ? $price['currency'] : null;
            $product['category_name'] = $categoryMap[$product['category_id']] ?? '—';
        }
        unset($product);

        $activeCategoryId = $categoryId;

        require __DIR__ . '/../../views/products.php';
    }
}