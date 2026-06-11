<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/repositories/CatalogRepositoryInterface.php';
require_once __DIR__ . '/src/repositories/CatalogRepository.php';
require_once __DIR__ . '/src/repositories/CartRepositoryInterface.php';
require_once __DIR__ . '/src/repositories/CartRepository.php';
require_once __DIR__ . '/src/Controller.php';
require_once __DIR__ . '/src/CartController.php';

$catalogRepo = new CatalogRepository();
$catalogController = new CatalogController($catalogRepo);
$cartRepo = new CartRepository();
$cartController = new CartController($cartRepo, $catalogRepo);

if (isset($_GET['action']) && $_GET['action'] === 'add_to_cart') {
    $productId = (int)($_GET['productId'] ?? 0);
    $cartController->add($productId, 1);

    echo json_encode(['ok' => true]);
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'cart') {
    if (isset($_GET['remove'])) {
        $cartController->remove((int)$_GET['remove']);
        header('Location: /?page=cart');
        exit;
    }

    if (isset($_GET['clear'])) {
        $cartController->clear();
        header('Location: /?page=cart');
        exit;
    }

    $cartController->show();
    exit;
}

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$catalogController->showProducts($categoryId);