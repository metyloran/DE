<?php
// pages/register.php
require_once __DIR__ . '/../helpers/db.php';
session_start();

// Если уже авторизован — на главную
if (!empty($_SESSION['user_id'])) {
    header('Location: /DE/korochki/pages/index.php');
    exit;
}

$errors = [];
$login    = '';
$fio      = '';
$telephon = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $fio      = trim($_POST['fio'] ?? '');
    $telephon = trim($_POST['telephon'] ?? '');

    // ---------- Валидация логина ----------
    if ($login === '') {
        $errors[] = 'Введите логин.';
    } elseif (!preg_match('/^[A-Za-z0-9]{6,}$/', $login)) {
        $errors[] = 'Логин: только латиница и цифры, не менее 6 символов.';
    }

    // ---------- Валидация пароля ----------
    if ($password === '') {
        $errors[] = 'Введите пароль.';
    } elseif (mb_strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов.';
    }

    // ---------- Валидация ФИО ----------
    if ($fio === '') {
        $errors[] = 'Введите ФИО.';
    } elseif (!preg_match('/^[А-Яа-яЁё\s\-]+$/u', $fio)) {
        $errors[] = 'ФИО: используйте только кириллицу, пробелы и дефис.';
    }

    // ---------- Валидация телефона ----------
    if ($telephon === '') {
        $errors[] = 'Введите номер телефона.';
    } elseif (!preg_match('/^[0-9\s\-\(\)\+]{10,20}$/', $telephon)) {
        $errors[] = 'Телефон: неверный формат.';
    }

    // ---------- Проверка уникальности логина ----------
    if (empty($errors)) {
        $check = $pdo->prepare('SELECT id_polzovately FROM polzovately WHERE login = :login LIMIT 1');
        $check->execute(['login' => $login]);
        if ($check->fetch()) {
            $errors[] = 'Такой логин уже занят.';
        }
    }

    // ---------- Вставка пользователя ----------
    if (empty($errors)) {
        $hash = md5($password); // ⚠️ учебный вариант, см. примечание в конце

        $stmt = $pdo->prepare(
            'INSERT INTO polzovately (login, parol, fio, telephon, rol_id)
             VALUES (:login, :parol, :fio, :telephon, :rol_id)'
        );
        $stmt->execute([
            'login'    => $login,
            'parol'    => $hash,
            'fio'      => $fio,
            'telephon' => $telephon,
            'rol_id'   => 1, // 1 = пользователь
        ]);

        // Автоматический вход после регистрации
        $_SESSION['user_id']      = (int)$pdo->lastInsertId();
        $_SESSION['user_login']   = $login;
        $_SESSION['user_fio']     = $fio;
        $_SESSION['user_role_id'] = 1;

        header('Location: /DE/korochki/pages/index.php');
        exit;
    }
}

$pageTitle = 'Регистрация - Корочки.есть';

ob_start();
?>
<div class="auth-form-container">
    <h2>Регистрация</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
        <div class="form-group">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login"
                   value="<?php echo htmlspecialchars($login); ?>" required>
            <small class="hint">Латиница и цифры, не менее 6 символов</small>
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>
            <small class="hint">Минимум 8 символов</small>
        </div>

        <div class="form-group">
            <label for="fio">ФИО</label>
            <input type="text" id="fio" name="fio"
                   value="<?php echo htmlspecialchars($fio); ?>" required>
            <small class="hint">Используйте только символы кириллицы и пробелы</small>
        </div>

        <div class="form-group">
            <label for="telephon">Телефон</label>
            <input type="text" id="telephon" name="telephon"
                   placeholder="8(ххх)ххх-хх-хх"
                   value="<?php echo htmlspecialchars($telephon); ?>" required>
            <small class="hint">Формат: 8(999)123-45-67</small>
        </div>

        <button type="submit" class="btn-submit">Зарегистрироваться</button>
    </form>

    <p class="auth-switch">
        Уже есть аккаунт?
        <a href="/DE/korochki/pages/auth.php">Войти</a>
    </p>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';