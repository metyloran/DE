<?php
// pages/layout.php
require_once __DIR__ . '/../helpers/db.php';

$page_title = $page_title ?? 'Портал «Стипендия»';
$current_user = $_SESSION['user'] ?? null;

// Переменная для контента страницы (будет передана из index.php, auth.php и т.д.)
$page_content = $page_content ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="../styles/style.css"> 
</head>
<body>

<!-- ===== ШАПКА ===== -->
<header class="site-header">
    <div class="header-top">
        <div class="logo">
            <img src="../media/1282150.png" alt="Логотип" class="logo-img">
        </div>

        <div class="header-title">
            <h1>Портал «Стипендия»</h1>
        </div>

        <nav class="header-nav">
            <?php if ($current_user): ?>
                <span class="user-greeting">Привет, <?= htmlspecialchars($current_user['full_name']) ?></span>
                <a href="zayavki.php" class="nav-link">Мои заявки</a>
                <?php if (($current_user['role'] ?? 0) == 1): ?>
                    <a href="admin.php" class="nav-link">Админка</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link">выйти</a>
            <?php else: ?>
                <a href="register.php" class="nav-link">регистрация</a>
                <a href="auth.php" class="nav-link">войти</a>
            <?php endif; ?>
            <span class="topic-label">Topic 4</span>
        </nav>
    </div>

    <div class="header-search">
        <form action="#" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search" class="search-input">
            <button type="submit" class="search-btn">🔍</button>
        </form>
    </div>
</header>

<!-- ===== ОСНОВНОЙ КОНТЕНТ ===== -->
<main class="site-main">
    <?= $page_content ?>
</main>

<!-- ===== ПОДВАЛ ===== -->
<footer class="site-footer">
    <div class="footer-content">
        <p>&copy; <?= date('Y') ?> Портал «Стипендия». Все права защищены.</p>
    </div>
</footer>

<script src="../scripts/main.js"></script>
</body>
</html>