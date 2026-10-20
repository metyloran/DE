<?php
// pages/logout.php
// Завершение сессии пользователя

// Запускаем сессию, чтобы иметь к ней доступ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Очищаем все данные сессии
$_SESSION = [];

// Удаляем cookie сессии, если она используется браузером
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Уничтожаем сессию на сервере
session_destroy();

// Перенаправляем пользователя на главную
header('Location: /DE/korochki/pages/index.php');
exit;