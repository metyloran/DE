<?php
// pages/auth.php
$page_title = 'Вход в систему';

ob_start(); // Начинаем буферизацию контента


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login     = trim($_POST['login'] ?? '');
    $password  = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if (!$login || !$password || !$full_name || !$email) {
        $error = 'Заполните обязательные поля';
    } else {
        // Проверка уникальности логина
        $check = $pdo->prepare("SELECT id_user FROM users WHERE login = ?");
        $check->execute([$login]);
        if ($check->fetch()) {
            $error = 'Пользователь с таким логином уже существует';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (login, password, full_name, phone, email, role) 
                 VALUES (?, ?, ?, ?, ?, 0)"
            );
            $stmt->execute([$login, $hash, $full_name, $phone, $email]);

            $success = 'Регистрация успешна! Теперь вы можете войти.';
        }
    }
}
?>

<section class="auth-section">
    <div class="auth-card">
        <h2>Регистрация</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="login">Логин *</label>
                <input type="text" id="login" name="login" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль *</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="full_name">ФИО *</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
        </form>

        <p class="auth-link">
            Уже есть аккаунт? <a href="/pages/auth.php">Войти</a>
        </p>
    </div>
</section>

<?php 
// 3. Сохраняем весь "запомненный" HTML в переменную $page_content
$page_content = ob_get_clean(); 

// 4. Подключаем единый layout, который выведет шапку, наш контент и подвал
require_once __DIR__ . '/layout.php'; 
?>