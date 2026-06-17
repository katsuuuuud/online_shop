<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@700&family=Mulish:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php $user = $_SESSION['user'] ?? null; ?>
<header>
    <a class="logo" href="/">SHOP<span>.</span></a>
    <span class="header-meta">КАТАЛОГ</span>
    <a class="btn-cart" href="/cart">Корзина</a>
    <?php if ($user): ?>
        <a class="btn-cart" href="/profile">Кабинет</a>
        <a class="btn-cart" href="/auth/logout">Выйти</a>
    <?php else: ?>
        <a class="btn-cart" href="/auth/login">Войти</a>
    <?php endif; ?>
</header>

<div class="wrapper">
    <aside>
        <p class="sidebar-title">Категории</p>
        <ul class="cat-list">
            <li><a href="/" class="<?= $activeCategoryId === null ? 'active' : '' ?>"><span class="cat-dot"></span>Все товары</a></li>
            <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="/?category=<?= $cat['categoryId'] ?>" class="<?= $activeCategoryId === $cat['categoryId'] ? 'active' : '' ?>">
                        <span class="cat-dot"></span>
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main>
        <div class="section-head">
            <h1>Все товары</h1>
            <span class="count"><?= count($products) ?> шт.</span>
        </div>

        <div class="grid">
            <?php foreach ($products as $product): ?>
                <div class="card">
                    <?php if ($product['has_discount']): ?>
                        <span class="tag tag-sale">SALE</span>
                    <?php endif; ?>

                    <div class="card-img"></div>

                    <div>
                        <div class="card-name"><?= htmlspecialchars($product['name']) ?></div>
                        <div style="font-size:.8rem; color:var(--muted); margin-top:4px;">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </div>
                    </div>

                    <div class="card-footer">
                        <span class="price">
                            <?php if ($product['price']): ?>
                                <?= number_format($product['price'], 2) ?> <?= $product['currency'] ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </span>
                        <button type="button" class="btn-cart add-to-cart" data-product-id="<?= (int)$product['productId'] ?>">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<footer>© 2026 Shop</footer>

<script src="/js/main.js"></script>
</body>
</html>