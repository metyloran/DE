<?php
// pages/layout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Базовый URL: динамически определяется на основе пути скрипта (например, /stipendia)
$baseUrl = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

// Заголовок страницы по умолчанию
$pageTitle = $pageTitle ?? 'Стипендиальный портал';

// ---------- Хлебные крошки ----------
$breadcrumbMap = [
    'index.php'       => 'Главная',
    'auth.php'        => 'Авторизация',
    'register.php'    => 'Регистрация',
    'zayavki.php'     => 'Мои заявки',
    'new_zayavka.php' => 'Новая заявка на стипендию',
    'profile.php'     => 'Личный кабинет',
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
                    <!-- Замените logo.png на актуальный файл логотипа вашего проекта -->
                    <img src="<?php echo $baseUrl; ?>/media/logo.png" alt="Логотип Стипендиальный портал">
                </a>
                <span class="site-title">СТИПЕНДИЯ</span>
            </div>
            
            <div class="header-right">
                <!-- Поиск можно убрать, если он не нужен на портале заявок -->
                <div class="search-box">
                    <input type="search" placeholder="Поиск по сайту" aria-label="Поиск по сайту">
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <li><a href="<?php echo $baseUrl; ?>/pages/new_zayavka.php">Подать заявку</a></li>
                            <li><a href="<?php echo $baseUrl; ?>/pages/zayavki.php">Мои заявки</a></li>
                            
                            <?php if ((int)($_SESSION['user_role_id'] ?? 0) === 2): ?>
                                <li><a href="<?php echo $baseUrl; ?>/pages/admin.php">Админ-панель</a></li>
                            <?php endif; ?>
                            
                            <li>
                                <a href="<?php echo $baseUrl; ?>/pages/logout.php">
                                    Выйти (<?php echo htmlspecialchars($_SESSION['user_login'] ?? 'Пользователь'); ?>)
                                </a>
                            </li>
                        <?php else: ?>
                            <li><a href="<?php echo $baseUrl; ?>/pages/register.php">Регистрация</a></li>
                            <li><a href="<?php echo $baseUrl; ?>/pages/auth.php">Войти</a></li>
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
            } else {
                echo '<p>Содержимое страницы не передано.</p>';
            }
            ?>
        </main>

        <footer class="main-footer">
            <p>&copy; <?php echo date('Y'); ?> Стипендиальный портал. Все права защищены.</p>
        </footer>
    </div>
    
    <script src="<?php echo $baseUrl; ?>/scripts/script.js"></script>
</body>
</html>