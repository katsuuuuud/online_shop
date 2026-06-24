<?php

class CatalogController
{
    public function __construct(private CatalogService $catalogService) {}

    public function showProducts(): void
    {
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $catalogData = $this->catalogService->getProductsForCatalog($categoryId);

        $categories       = $catalogData['categories'];
        $products         = $catalogData['products'];
        $activeCategoryId = $catalogData['activeCategoryId'];

        require __DIR__ . '/../../views/products.php';
    }
}
