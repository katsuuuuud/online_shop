<?php
$user = $_SESSION['user'] ?? null;
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$next = $_GET['next'] ?? '/';
$mode = isset($mode)
    ? $mode
    : (in_array($_GET['mode'] ?? 'login', ['login', 'register'], true) ? $_GET['mode'] : 'login');

if ($user) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@700&family=Mulish:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-page">

<header>
    <a class="logo" href="/">SHOP<span>.</span></a>
    <span class="header-meta">АВТОРИЗАЦИЯ</span>
    <a class="btn-cart" href="/">Назад</a>
</header>

<main>
    <div class="auth-box">
        <h1>Авторизация</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="auth-tabs">
            <a href="/auth/login<?= $next !== '/' ? '?next=' . urlencode($next) : '' ?>"
               class="auth-tab <?= $mode === 'login' ? 'auth-tab--active' : 'auth-tab--idle' ?>">Вход</a>
            <a href="/auth/register<?= $next !== '/' ? '?next=' . urlencode($next) : '' ?>"
               class="auth-tab <?= $mode === 'register' ? 'auth-tab--active' : 'auth-tab--idle' ?>">Регистрация</a>
        </div>

        <?php if ($mode === 'login'): ?>
            <div class="auth-section">
                <h2>Вход</h2>
                <form action="/auth/login" method="post" class="auth-form" data-mode="login">
                    <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <button type="submit" class="btn-cart">Войти</button>
                </form>
            </div>
        <?php else: ?>
            <div class="auth-section">
                <h2>Регистрация</h2>
                <form action="/auth/register" method="post" class="auth-form" data-mode="register">
                    <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
                    <input type="text" name="name" placeholder="Имя" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="phone" placeholder="Телефон" required>
                    <input type="text" name="address" placeholder="Адрес" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <button type="submit" class="btn-cart">Зарегистрироваться</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>© 2026 Shop</footer>
<script src="/js/main.js"></script>
</body>
</html>