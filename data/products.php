<?php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'online_shop');
define('DB_USER', 'root');
define('DB_PASS', '123456');

function getConnection(): PDO {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    try {
        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        die("Ошибка подключения к БД: {$e->getMessage()}");
    }
}

function loadCatalog(): array {
    $pdo = getConnection();

    $rows = $pdo->query("
        SELECT
            c.name  AS menu_item_name,
            p.name  AS dish,
            pr.price
        FROM categories c
        JOIN products p  ON c.categoryid  = p.category_id
        JOIN prices  pr  ON p.productid   = pr.product_id
        ORDER BY c.name, p.name
    ")->fetchAll();

    $categories = [];
    $products   = [];

    foreach ($rows as $row) {
        $cat   = $row['menu_item_name'];
        $name  = $row['dish'];
        $price = (int) $row['price'];

        if (!in_array($cat, $categories, true)) {
            $categories[] = $cat;
        }

        $products[$cat][] = [
            'name'  => $name,
            'price' => $price,
            'tag'   => '',       
        ];
    }

    return [$categories, $products];
}

[$categories, $products] = loadCatalog();

$emojis = [];