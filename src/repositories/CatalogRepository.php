<?php
class CatalogRepository implements CatalogRepositoryInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getProducts(): array {
        return $this->db->query('SELECT * FROM products')->fetchAll();
    }

    public function getProductById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE productId = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategories(): array {
        return $this->db->query('SELECT * FROM categories')->fetchAll();
    }

    public function getCategoryById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE categoryId = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getPrices(): array {
        return $this->db->query('SELECT * FROM prices')->fetchAll();
    }

    public function getPriceById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM prices WHERE priceauditId = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getPriceByProduct(int $product_id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM prices WHERE product_id = ? AND is_active = 1');
        $stmt->execute([$product_id]);
        return $stmt->fetch();
    }

    public function getProductsByCategory(int $categoryId): array {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
}