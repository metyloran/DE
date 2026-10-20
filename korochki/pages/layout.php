<?php
// pages/layout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Базовый URL: /DE/korochki
$baseUrl = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

$pageTitle = $pageTitle ?? 'Корочки.есть';

// ---------- Хлебные крошки ----------
$breadcrumbMap = [
    'index.php'       => 'Главная',
    'auth.php'        => 'Авторизация',
    'register.php'    => 'Регистрация',
    'zayavki.php'     => 'Заявки',
    'new_zayavka.php' => 'Новая заявка',
    'admin.php'       => 'Админ-панель',
];

$currentFile = basename($_SERVER['SCRIPT_NAME']);
$breadcrumbs = [
    ['Главная', $baseUrl . '/pages/index.php'],
];
if ($currentFile !== 'index.php' && isset($breadcrumbMap[$currentFile])) {
    $breadcrumbs[] = [$breadcrumbMap[$currentFile], null];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/styles/style.css?v=<?php echo @filemtime(__DIR__ . '/../styles/style.css'); ?>">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo">
                <a href="<?php echo $baseUrl; ?>/pages/index.php">
                    <img src="<?php echo $baseUrl; ?>/media/image02.jpg" alt="Логотип Корочки.есть">
                </a>
                <span class="site-title">КОРОЧКИ.ЕСТЬ</span>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <input type="search" placeholder="Search">
                </div>
                <nav class="main-nav">
                    <ul>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <li><a href="<?php echo $baseUrl; ?>/pages/new_zayavka.php">новая заявка</a></li>
                            <li><a href="<?php echo $baseUrl; ?>/pages/zayavki.php">мои заявки</a></li>
                            <?php if ((int)($_SESSION['user_role_id'] ?? 0) === 2): ?>
                                <li><a href="<?php echo $baseUrl; ?>/pages/admin.php">админ-панель</a></li>
                            <?php endif; ?>
                            <li>
                                <a href="<?php echo $baseUrl; ?>/pages/logout.php">
                                    выйти (<?php echo htmlspecialchars($_SESSION['user_login'] ?? ''); ?>)
                                </a>
                            </li>
                        <?php else: ?>
                            <li><a href="<?php echo $baseUrl; ?>/pages/register.php">регистрация</a></li>
                            <li><a href="<?php echo $baseUrl; ?>/pages/auth.php">войти</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Хлебные крошки -->
        <nav class="breadcrumbs" aria-label="Навигация по сайту">
            <ol>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php $isLast = ($index === count($breadcrumbs) - 1); ?>
                    <li class="<?php echo $isLast ? 'breadcrumb-current' : ''; ?>">
                        <?php if ($crumb[1] !== null && !$isLast): ?>
                            <a href="<?php echo htmlspecialchars($crumb[1]); ?>">
                                <?php echo htmlspecialchars($crumb[0]); ?>
                            </a>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($crumb[0]); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>

        <hr class="header-divider">

        <main>
            <?php
            if (isset($content)) {
                echo $content;
            }
            ?>
        </main>

        <footer class="main-footer">
            <p>&copy; <?php echo date('Y'); ?> Корочки.есть. Все права защищены.</p>
        </footer>
    </div>
    <script src="<?php echo $baseUrl; ?>/scripts/script.js"></script>
</body>
</html>