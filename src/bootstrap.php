<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/RedisClient.php';
require_once __DIR__ . '/repositories/AuthRepositoryInterface.php';
require_once __DIR__ . '/repositories/AuthRepository.php';
require_once __DIR__ . '/repositories/CatalogRepositoryInterface.php';
require_once __DIR__ . '/repositories/CatalogRepository.php';
require_once __DIR__ . '/repositories/CartRepositoryInterface.php';
require_once __DIR__ . '/repositories/CartRepository.php';
require_once __DIR__ . '/repositories/OrderRepositoryInterface.php';
require_once __DIR__ . '/repositories/OrderRepository.php';
require_once __DIR__ . '/services/OrderService.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/CatalogController.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/ProfileController.php';