<?php
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: /auth/login?next=/profile');
    exit;
}
$error   = $_GET['error']   ?? '';
$success = $_GET['success'] ?? '';
$tab     = $_GET['tab']     ?? 'info';
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
<body class="profile-page">

<header>
    <a class="logo" href="/">SHOP<span>.</span></a>
    <span class="header-meta">ЛИЧНЫЙ КАБИНЕТ</span>
    <a class="btn-cart" href="/">Назад</a>
    <a class="btn-cart" href="/auth/logout">Выйти</a>
</header>

<main>
    <div class="profile-layout">
        <aside class="profile-nav">
            <a href="/profile?tab=info"
               class="profile-nav-link <?= $tab === 'info'   ? 'profile-nav-link--active' : 'profile-nav-link--idle' ?>">Профиль</a>
            <a href="/profile?tab=orders"
               class="profile-nav-link <?= $tab === 'orders' ? 'profile-nav-link--active' : 'profile-nav-link--idle' ?>">Мои заказы</a>
        </aside>

        <section class="profile-section">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($tab === 'orders'): ?>
                <h2>Мои заказы</h2>
                <?php if (empty($orders)): ?>
                    <p>Пока нет заказов.</p>
                <?php else: ?>
                    <ul class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <li class="order-card">
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
                <form action="/profile" method="post" class="profile-form">
                    <input type="hidden" name="tab" value="info">
                    <label>
                        Имя
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    </label>
                    <label>
                        Email
                        <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                    </label>
                    <label>
                        Телефон
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </label>
                    <label>
                        Адрес
                        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
                    </label>
                    <button type="submit" class="btn-cart">Сохранить</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>

<footer>© 2026 Shop</footer>
<script src="/js/main.js"></script>
</body>
</html>