<?php
// pages/auth.php

// 1. Подключаем соединение с базой данных
require_once __DIR__ . '/../helpers/db.php';

// 2. Запускаем сессию (для сохранения данных пользователя)
session_start();

// Если пользователь уже авторизован — перенаправляем на главную
if (!empty($_SESSION['user_id'])) {
    header('Location: /DE/korochki/pages/index.php');
    exit;
}

// 3. Инициализируем переменные для вывода ошибок
$errors = [];
$login = '';

// 4. Обрабатываем отправку формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    // Простая валидация
    if ($login === '') {
        $errors[] = 'Введите логин.';
    }
    if ($password === '') {
        $errors[] = 'Введите пароль.';
    }

    // Если ошибок нет, ищем пользователя в БД
    if (empty($errors)) {
        // Ищем пользователя по логину
        $stmt = $pdo->prepare(
            'SELECT p.id_polzovately, p.login, p.parol, p.fio, p.rol_id, r.rol
             FROM polzovately p
             LEFT JOIN roli r ON r.id_roli = p.rol_id
             WHERE p.login = :login
             LIMIT 1'
        );
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();

        // Пароли в базе хранятся в виде MD5-хеша — сравниваем хеши
        if ($user && md5($password) === $user['parol']) {
            // Успешный вход — сохраняем данные в сессию
            $_SESSION['user_id']      = (int)$user['id_polzovately'];
            $_SESSION['user_login']   = $user['login'];
            $_SESSION['user_fio']     = $user['fio'];
            $_SESSION['user_role_id'] = (int)$user['rol_id'];
            $_SESSION['user_role']    = $user['rol'];

            // Перенаправляем: админ → в админ-панель, обычный → на главную
            if ((int)$user['rol_id'] === 2) {
                header('Location: /DE/korochki/pages/admin.php');
            } else {
                header('Location: /DE/korochki/pages/index.php');
            }
            exit;
        } else {
            $errors[] = 'Неверный логин или пароль.';
        }
    }
}

// 5. Формируем контент для страницы
$pageTitle = 'Авторизация - Корочки.есть';

// Буферизация вывода — вставим его в layout
ob_start();
?>
<div class="auth-form-container">
    <h2>Вход в систему</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="auth.php">
        <div class="form-group">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login"
                   value="<?php echo htmlspecialchars($login); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-submit">Войти</button>
    </form>

    <p class="auth-switch">
        Еще нет аккаунта?
        <a href="/DE/korochki/pages/register.php">Регистрация</a>
    </p>
</div>
<?php
$content = ob_get_clean();

// 6. Подключаем layout для отображения страницы
require_once __DIR__ . '/layout.php';
?>