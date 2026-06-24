<?php

class CatalogService
{
    public function __construct(private CatalogRepositoryInterface $repo) {}

    public function getProductsForCatalog(?int $categoryId): array
    {
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

        return [
            'categories'       => $categories,
            'products'         => $products,
            'activeCategoryId' => $categoryId,
        ];
    }
}
