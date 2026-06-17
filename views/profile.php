<?php
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: /auth/login?next=/profile');
    exit;
}
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$tab = $_GET['tab'] ?? 'info';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@700&family=Mulish:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header>
    <a class="logo" href="/">SHOP<span>.</span></a>
    <span class="header-meta">ЛИЧНЫЙ КАБИНЕТ</span>
    <a class="btn-cart" href="/">Назад</a>
    <a class="btn-cart" href="/auth/logout">Выйти</a>
</header>

<main style="padding:30px;">
    <div style="display:flex; gap:20px; max-width:1000px; margin:0 auto;">
        <aside style="min-width:220px;">
            <nav style="display:flex; flex-direction:column; gap:10px;">
                <a href="/profile?tab=info" style="display:block; padding:12px 16px; background:<?= $tab === 'info' ? '#d4f13c' : '#222' ?>; color:<?= $tab === 'info' ? '#222' : '#fff' ?>; border-radius:12px; text-decoration:none;">Профиль</a>
                <a href="/profile?tab=orders" style="display:block; padding:12px 16px; background:<?= $tab === 'orders' ? '#d4f13c' : '#222' ?>; color:<?= $tab === 'orders' ? '#222' : '#fff' ?>; border-radius:12px; text-decoration:none;">Мои заказы</a>
            </nav>
        </aside>

        <section style="flex:1; background:#222; padding:24px; border-radius:20px; box-shadow:0 20px 50px rgba(0,0,0,.08);">
            <?php if ($error): ?>
                <div style="margin-bottom:16px; color:#c53030; background:#ffe7e7; padding:12px 16px; border-radius:10px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="margin-bottom:16px; color:#276749; background:#e6fffa; padding:12px 16px; border-radius:10px;">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'orders'): ?>
                <h2>Мои заказы</h2>
                <?php if (empty($orders)): ?>
                    <p>Пока нет заказов.</p>
                <?php else: ?>
                    <ul style="list-style:none; padding:0; display:grid; gap:14px; margin-top:20px;">
                        <?php foreach ($orders as $order): ?>
                            <li style="border:1px solid #ddd; border-radius:16px; padding:18px;">
                                <div><strong>Заказ #</strong><?= htmlspecialchars($order['orderId'] ?? $order['id'] ?? '') ?></div>
                                <div><strong>Дата:</strong> <?= htmlspecialchars($order['created_at'] ?? '') ?></div>
                                <div><strong>Сумма:</strong> <?= number_format((float)($order['amount'] ?? 0), 2) ?> <?= htmlspecialchars($order['currency'] ?? 'RUB') ?></div>
                                <div><strong>Статус:</strong> <?= htmlspecialchars($order['status'] ?? '—') ?></div>
                                <div><strong>Адрес:</strong> <?= htmlspecialchars($order['address'] ?? '') ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php else: ?>
                <h2>Профиль</h2>
                <form action="/profile/update" method="post" style="display:grid; gap:16px; margin-top:20px;">
                    <input type="hidden" name="tab" value="info">
                    <label style="display:flex; flex-direction:column; gap:8px;">
                        Имя
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    </label>
                    <label style="display:flex; flex-direction:column; gap:8px;">
                        Email
                        <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                    </label>
                    <label style="display:flex; flex-direction:column; gap:8px;">
                        Телефон
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </label>
                    <label style="display:flex; flex-direction:column; gap:8px;">
                        Адрес
                        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
                    </label>
                    <button type="submit" class="btn btn-cart">Сохранить</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>

<footer style="text-align:center; margin-top:40px;">© 2026 Shop</footer>
</body>
</html>
