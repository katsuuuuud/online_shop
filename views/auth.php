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
<body>
<header>
    <a class="logo" href="/">SHOP<span>.</span></a>
    <span class="header-meta">АВТОРИЗАЦИЯ</span>
    <a class="btn-cart" href="/">Назад</a>
</header>

<main style="padding: 30px; display:flex; justify-content:center;">
    <div style="max-width:520px; width:100%; background:#222; padding:32px; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,.12);">
        <h1 style="margin-bottom:24px;">Авторизация</h1>

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

        <div style="display:flex; gap:12px; margin-bottom:24px;">
            <a href="/auth/login<?= $next !== '/' ? '?next=' . urlencode($next) : '' ?>" style="flex:1; text-align:center; padding:12px; border-radius:12px; text-decoration:none; color:<?= $mode === 'login' ? '#222' : '#fff' ?>; background:<?= $mode === 'login' ? '#d4f13c' : '#222' ?>; border:1px solid #444;">Вход</a>
            <a href="/auth/register<?= $next !== '/' ? '?next=' . urlencode($next) : '' ?>" style="flex:1; text-align:center; padding:12px; border-radius:12px; text-decoration:none; color:<?= $mode === 'register' ? '#222' : '#fff' ?>; background:<?= $mode === 'register' ? '#d4f13c' : '#222' ?>; border:1px solid #444;">Регистрация</a>
        </div>

        <?php if ($mode === 'login'): ?>
            <section style="padding:20px; background:#222; border-radius:14px; border:1px solid #444;">
                <h2 style="margin-bottom:16px;">Вход</h2>
                <form action="/auth/login" method="post" style="display:grid; gap:12px;">
                    <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <button type="submit" class="btn btn-cart">Войти</button>
                </form>
            </section>
        <?php else: ?>
            <section style="padding:20px; background:#222; border-radius:14px; border:1px solid #444;">
                <h2 style="margin-bottom:16px;">Регистрация</h2>
                <form action="/auth/register" method="post" style="display:grid; gap:12px;">
                    <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
                    <input type="text" name="name" placeholder="Имя" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="phone" placeholder="Телефон" required>
                    <input type="text" name="address" placeholder="Адрес" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <button type="submit" class="btn btn-cart">Зарегистрироваться</button>
                </form>
            </section>
        <?php endif; ?>
    </div>
</main>

<footer style="text-align:center; margin-top:40px;">© 2026 Shop</footer>
</body>
</html>
