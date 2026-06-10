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
                       
class CatalogRepository implements CatalogRepositoryInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getProducts(): array {
        return $this->db->query('SELECT * FROM products')->fetchAll();
    }

    public function getProductById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategories(): array {
        return $this->db->query('SELECT * FROM categories')->fetchAll();
    }

    public function getCategoryById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getPrices(): array {
        return $this->db->query('SELECT * FROM prices')->fetchAll();
    }

    public function getPriceByProduct(int $product_id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM prices WHERE product_id = ?');
        $stmt->execute([$product_id]);
        return $stmt->fetch();
    }

    public function getProductsByCategory(int $categoryId): array {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
}