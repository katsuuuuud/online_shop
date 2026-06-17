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
            <ul style="list-style:none; display:flex; flex-direction:column; gap:12px; width:300px;">
                <?php foreach ($items as $item): ?>
                    <li style="padding:10px 20px; border:1px solid #2a2a2a; border-radius:10px; background:#161616; display:flex; justify-content:space-between; align-items:center; gap:12px;">
                        <span>
                            <?= htmlspecialchars($item['name']) ?> — <?= (int)$item['quantity'] ?> шт.
                        </span>
                        <button type="button" class="btn-cart remove-from-cart" data-product-id="<?= (int)$item['productId'] ?>">Удалить</button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div style="margin-top:16px;">
                <button type="button" class="btn-cart clear-cart">Очистить корзину</button>
                <?php if ($user): ?>
                    <button type="button" class="btn-cart make-order">Оформить заказ</button>
                <?php else: ?>
                    <a class="btn-cart" href="/auth/login?next=/cart">Войти для оформления</a>
                <?php endif; ?>
            </div>
            <?php if ($user): ?>
                <div class="order-form-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
                    <div style="background:#fff; padding:20px; border-radius:10px; width:300px;">
                        <h2>Оформление заказа</h2>
                        <p>Ваш заказ будет создан для <?= htmlspecialchars($user['name']) ?>.</p>
                        <form id="order-form">
                            <button type="submit" class="btn-cart" style="margin-top:16px;">Подтвердить заказ</button>
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