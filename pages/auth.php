<?php
// pages/auth.php
$page_title = 'Вход в систему';

ob_start(); // Начинаем буферизацию контента
?>

<section class="auth-section">
    <div class="auth-card">
        <h2>Вход в систему</h2>
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Войти</button>
        </form>
        <p class="auth-link">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>
</section>

<?php 
$page_content = ob_get_clean();
require_once __DIR__ . '/layout.php'; 
?>