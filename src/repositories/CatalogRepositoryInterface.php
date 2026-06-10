<?php
interface CatalogRepositoryInterface {
    public function getProducts(): array;
    public function getProductById(int $id): array|false;
    public function getCategories(): array;
    public function getPrices(): array;
    public function getCategoryById(int $id): array|false;
    public function getPriceById(int $id): array|false;
    public function getPriceByProduct(int $product_id): array|false;
    public function getProductsByCategory(int $categoryId): array;
}