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
<header>
    <a class="logo" href="/">SHOP<span>.</span></a>
    <span class="header-meta">КОРЗИНА</span>
    <a class="btn-cart" href="/">Назад</a>
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
                        <a class="btn-cart" href="/?page=cart&remove=<?= (int)$item['productId'] ?>">Удалить</a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p style="margin-top:16px;">
                <a class="btn-cart" href="/?page=cart&clear=1">Очистить корзину</a>
            </p>
        <?php endif; ?>
    </main>
</div>

<footer>© 2026 Shop</footer>
</body>
</html>
