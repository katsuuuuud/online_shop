<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/repositories/CatalogRepositoryInterface.php';
require_once __DIR__ . '/src/repositories/CatalogRepository.php';
require_once __DIR__ . '/src/repositories/CartRepositoryInterface.php';
require_once __DIR__ . '/src/repositories/CartRepository.php';
require_once __DIR__ . '/src/repositories/OrderRepositoryInterface.php';
require_once __DIR__ . '/src/repositories/OrderRepository.php';
require_once __DIR__ . '/src/services/OrderService.php';
require_once __DIR__ . '/src/controllers/CatalogController.php';
require_once __DIR__ . '/src/controllers/CartController.php';
require_once __DIR__ . '/src/controllers/OrderController.php';

$catalogRepo = new CatalogRepository();
$catalogController = new CatalogController($catalogRepo);
$cartRepo = new CartRepository();
$cartController = new CartController($cartRepo, $catalogRepo);
$orderController = new OrderController(new OrderService(new OrderRepository(), new CartRepository()));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/order/create') {
    $input = $_POST;
    if (!$input) {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
    }

    $result = $orderController->submitOrder($input);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

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