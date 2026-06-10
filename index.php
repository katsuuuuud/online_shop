<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/repositories/CatalogRepositoryInterface.php';
require_once __DIR__ . '/src/repositories/CatalogRepository.php';
require_once __DIR__ . '/src/Controller.php';

$repo       = new CatalogRepository();
$controller = new CatalogController($repo);
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$controller->showProducts($categoryId);