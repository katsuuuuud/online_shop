<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CatalogRepositoryInterface.php';
require_once __DIR__ . '/CatalogRepository.php';

$repo = new CatalogRepository();
$products   = $repo->getProducts();
$categories = $repo->getCategories();
$prices = $repo->getPrices();
$controller = new CatalogController($repo);