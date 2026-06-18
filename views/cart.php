<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@700&family=Mulish:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php $user = $_SESSION['user'] ?? null; ?>

<header>
    <a class="logo" href="/">SHOP<span>.</span></a>
    <span class="header-meta"><?= $user ? 'Привет, ' . htmlspecialchars($user['name']) : 'КОРЗИНА' ?></span>
    <a class="btn-cart" href="/">Назад</a>
    <?php if ($user): ?>
        <a class="btn-cart" href="/profile">Кабинет</a>
        <a class="btn-cart" href="/auth/logout">Выйти</a>
    <?php else: ?>
        <a class="btn-cart" href="/auth/login?next=/cart">Войти</a>
    <?php endif; ?>
</header>

<div class="wrapper">
    <main>
        <div class="section-head">
            <h1>Корзина</h1>
        </div>

        <?php if (empty($items)): ?>
            <p>Корзина пуста.</p>
        <?php else: ?>
        <div class="cart-content">
            <ul class="cart-list">
                <?php foreach ($items as $item): ?>
                    <li class="cart-item">
                        <span><?= htmlspecialchars($item['name']) ?> — <?= (int)$item['quantity'] ?> шт.</span>
                        <button type="button" class="btn-cart remove-from-cart"
                                data-product-id="<?= (int)$item['productId'] ?>">Удалить</button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="cart-actions">
                <button type="button" class="btn-cart clear-cart">Очистить корзину</button>
                <?php if ($user): ?>
                    <button type="button" class="btn-cart make-order">Оформить заказ</button>
                <?php else: ?>
                    <a class="btn-cart" href="/auth/login?next=/cart">Войти для оформления</a>
                <?php endif; ?>
            </div>
        </div><?php /* .cart-content */ ?>

            <?php if ($user): ?>
                <div class="cart-modal order-form-modal">
                    <div class="cart-modal-inner">
                        <h2>Оформление заказа</h2>
                        <p>Ваш заказ будет создан для <?= htmlspecialchars($user['name']) ?>.</p>
                        <form id="order-form">
                            <button type="submit" class="btn-cart">Подтвердить заказ</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<footer>© 2026 Shop</footer>

<script src="/js/main.js"></script>
</body>
</html>