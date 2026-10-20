<?php
// pages/new_zayavka.php
require_once __DIR__ . '/../helpers/db.php';
session_start();

// ---------- Защита: только для авторизованных ----------
if (empty($_SESSION['user_id'])) {
    header('Location: /DE/korochki/pages/auth.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$errors  = [];
$success = '';

// ---------- Списки для формы ----------
$kurсы  = $pdo->query('SELECT id_kyrs, kyrs FROM kyrsi ORDER BY id_kyrs')->fetchAll();
$oplati = $pdo->query('SELECT `id_oplati(int)`, `oplata VARCHAR(255)` FROM oplati ORDER BY `id_oplati(int)`')->fetchAll();

// ---------- Обработка POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kyrsId    = (int)($_POST['kyrs_id']    ?? 0);
    $oplataId  = (int)($_POST['oplata_id']  ?? 0);
    $nachalo   = trim($_POST['nachalo']     ?? '');

    // ---------- Валидация ----------
    if ($kyrsId <= 0) {
        $errors[] = 'Выберите курс.';
    }
    if ($oplataId <= 0) {
        $errors[] = 'Выберите способ оплаты.';
    }
    if ($nachalo === '') {
        $errors[] = 'Укажите желаемую дату начала обучения.';
    } else {
        // Проверка формата даты
        $d = DateTime::createFromFormat('Y-m-d', $nachalo);
        if (!$d || $d->format('Y-m-d') !== $nachalo) {
            $errors[] = 'Неверный формат даты.';
        } elseif ($d < new DateTime('today')) {
            $errors[] = 'Дата начала не может быть в прошлом.';
        }
    }

    // ---------- Вставка заявки ----------
    if (empty($errors)) {
        // Получим id статуса "Новая" (первый в таблице statusi)
        $statusRow = $pdo->query('SELECT id_statusi FROM statusi ORDER BY id_statusi LIMIT 1')->fetch();
        $statusId  = $statusRow ? (int)$statusRow['id_statusi'] : 1;

        $stmt = $pdo->prepare(
            'INSERT INTO zayavki (kyrs_id, oplata_id, nachalo, status_id, polzovatel_id, otzyv_id)
             VALUES (:kyrs_id, :oplata_id, :nachalo, :status_id, :polzovatel_id, NULL)'
        );
        $stmt->execute([
            'kyrs_id'        => $kyrsId,
            'oplata_id'      => $oplataId,
            'nachalo'        => $nachalo,
            'status_id'      => $statusId,
            'polzovatel_id'  => $userId,
        ]);

        $success = 'Заявка успешно создана!';
    }
}

// ---------- Формирование страницы ----------
$pageTitle = 'Новая заявка - Корочки.есть';

ob_start();
?>
<div class="auth-form-container">
    <h2>Новая заявка</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message-success"><?php echo htmlspecialchars($success); ?></div>
        <p class="auth-switch">
            <a href="/DE/korochki/pages/zayavki.php">Перейти к моим заявкам →</a>
        </p>
    <?php else: ?>
        <form method="POST" action="new_zayavka.php">
            <div class="form-group">
                <label for="kyrs_id">Наименование курса</label>
                <select id="kyrs_id" name="kyrs_id" required>
                    <option value="">— выберите —</option>
                    <?php foreach ($kurсы as $k): ?>
                        <option value="<?php echo (int)$k['id_kyrs']; ?>"
                            <?php echo ((int)($_POST['kyrs_id'] ?? 0) === (int)$k['id_kyrs']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($k['kyrs']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="oplata_id">Способ оплаты</label>
                <select id="oplata_id" name="oplata_id" required>
                    <option value="">— выберите —</option>
                    <?php foreach ($oplati as $o): ?>
                        <option value="<?php echo (int)$o['id_oplati(int)']; ?>"
                            <?php echo ((int)($_POST['oplata_id'] ?? 0) === (int)$o['id_oplati(int)']) ? 'selected' : ''; ?>>
                            <?php
                                $label = trim($o['oplata VARCHAR(255)']);
                                echo htmlspecialchars($label !== '' ? $label : ('Способ №' . (int)$o['id_oplati(int)']));
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($oplati)): ?>
                    <small class="hint">В таблице oplati нет данных. Добавьте способы оплаты.</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="nachalo">Желаемая дата начала обучения</label>
                <input type="date" id="nachalo" name="nachalo"
                       min="<?php echo date('Y-m-d'); ?>"
                       value="<?php echo htmlspecialchars($_POST['nachalo'] ?? ''); ?>" required>
            </div>

            <button type="submit" class="btn-submit">Отправить заявку</button>
        </form>

        <p class="auth-switch">
            <a href="/DE/korochki/pages/index.php">← На главную</a>
        </p>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';