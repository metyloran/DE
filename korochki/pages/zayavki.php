<?php
// pages/zayavki.php

require_once __DIR__ . '/../helpers/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /DE/korochki/pages/auth.php');
    exit;
}

$userId  = (int)$_SESSION['user_id'];
$errors  = [];
$success = '';

// ============ ОБРАБОТКА ОТЗЫВА ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zayavka_id'])) {
    $zayavkaId = (int)$_POST['zayavka_id'];
    $comment   = trim($_POST['commentary'] ?? '');
    $zvezdy    = (int)($_POST['zvezdy'] ?? 0);

    // 1. Валидация
    if ($comment === '') {
        $errors[] = 'Введите текст отзыва.';
    } elseif (mb_strlen($comment) > 255) {
        $errors[] = 'Отзыв не должен превышать 255 символов.';
    }
    if ($zvezdy < 1 || $zvezdy > 5) {
        $errors[] = 'Оценка должна быть от 1 до 5.';
    }

    // 2. Проверка: заявка принадлежит пользователю, статус = "Обучение завершено", отзыва ещё нет
    $check = $pdo->prepare('
        SELECT z.id_zayavky, z.otzyv_id, s.status
        FROM zayavki z
        LEFT JOIN statusi s ON s.id_statusi = z.status_id
        WHERE z.id_zayavky = :zid AND z.polzovatel_id = :uid
        LIMIT 1
    ');
    $check->execute(['zid' => $zayavkaId, 'uid' => $userId]);
    $row = $check->fetch();

    if (!$row) {
        $errors[] = 'Заявка не найдена.';
    } elseif (mb_strtolower(trim($row['status'] ?? '')) !== 'обучение завершено') {
        $errors[] = 'Отзыв можно оставить только после завершения обучения.';
    } elseif (!empty($row['otzyv_id'])) {
        $errors[] = 'Вы уже оставляли отзыв по этой заявке.';
    }

    // 3. Сохранение
    if (empty($errors)) {
        $ins = $pdo->prepare('INSERT INTO otzyvy (commentary, zvezdy) VALUES (:c, :z)');
        $ins->execute(['c' => $comment, 'z' => $zvezdy]);
        $otzyvId = (int)$pdo->lastInsertId();

        $upd = $pdo->prepare('UPDATE zayavki SET otzyv_id = :oid WHERE id_zayavky = :zid');
        $upd->execute(['oid' => $otzyvId, 'zid' => $zayavkaId]);

        $success = 'Спасибо! Ваш отзыв сохранён.';
    }
}

// ============ СПИСОК ЗАЯВОК ПОЛЬЗОВАТЕЛЯ ============
$stmt = $pdo->prepare('
    SELECT
        z.id_zayavky,
        z.nachalo,
        z.otzyv_id,
        k.kyrs                    AS kurs,
        o.`oplata VARCHAR(255)`   AS oplata,
        s.id_statusi              AS status_id,
        s.status                  AS status,
        ot.commentary             AS otzyv,
        ot.zvezdy                 AS zvezdy
    FROM zayavki z
    LEFT JOIN kyrsi  k  ON k.id_kyrs = z.kyrs_id
    LEFT JOIN oplati o  ON o.`id_oplati(int)` = z.oplata_id
    LEFT JOIN statusi s ON s.id_statusi = z.status_id
    LEFT JOIN otzyvy ot ON ot.id_otzyvy = z.otzyv_id
    WHERE z.polzovatel_id = :uid
    ORDER BY z.id_zayavky DESC
');
$stmt->execute(['uid' => $userId]);
$zayavki = $stmt->fetchAll();

$pageTitle = 'Мои заявки - Корочки.есть';

ob_start();
?>
<h1 class="page-title">Мои заявки</h1>

<?php if ($success): ?>
    <div class="message-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $e): ?>
            <p class="error-text"><?php echo htmlspecialchars($e); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (empty($zayavki)): ?>
    <p class="empty-text">
        У вас пока нет заявок.
        <a href="/DE/korochki/pages/new_zayavka.php">Создать первую →</a>
    </p>
<?php else: ?>
    <div class="zayavki-grid">
        <?php foreach ($zayavki as $z): ?>
            <?php
                $statusClass = 'status-' . (int)$z['status_id'];
                $statusLower = mb_strtolower(trim($z['status'] ?? ''));
                $isCompleted = ($statusLower === 'обучение завершено');
                $hasOtzyv    = !empty($z['otzyv_id']);
            ?>
            <div class="zayavka-card">
    <div class="zayavka-header">
        <span class="zayavka-id">Заявка №<?php echo (int)$z['id_zayavky']; ?></span>
        <span class="status-badge <?php echo $statusClass; ?>">
            <?php echo htmlspecialchars($z['status'] ?? '—'); ?>
        </span>
    </div>

    <div class="zayavka-body">
        <p><strong>Курс:</strong> <?php echo htmlspecialchars($z['kurs'] ?? '—'); ?></p>
        <p><strong>Способ оплаты:</strong> <?php echo htmlspecialchars($z['oplata'] ?? '—'); ?></p>
        <p><strong>Дата начала:</strong>
            <?php
                if (!empty($z['nachalo'])) {
                    $d = DateTime::createFromFormat('Y-m-d', $z['nachalo']);
                    echo $d ? $d->format('d.m.Y') : htmlspecialchars($z['nachalo']);
                } else {
                    echo '—';
                }
            ?>
        </p>
    </div>

    <?php if ($hasOtzyv): ?>
        <!-- Отзыв уже оставлен -->
        <div class="zayavka-otzyv">
            <h4>Ваш отзыв</h4>

            <div class="stars-display">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?php echo ($i <= (int)$z['zvezdy']) ? 'star-filled' : ''; ?>"
                          aria-hidden="true">★</span>
                <?php endfor; ?>
                <span class="visually-hidden"><?php echo (int)$z['zvezdy']; ?> из 5</span>
            </div>

            <p class="otzyv-text"><?php echo htmlspecialchars($z['otzyv']); ?></p>
        </div>

    <?php elseif ($isCompleted): ?>
        <!-- Форма отзыва -->
        <div class="zayavka-otzyv-form">
            <h4>Оставить отзыв об обучении</h4>

            <form method="POST" class="review-form">
                <input type="hidden" name="zayavka_id" value="<?php echo (int)$z['id_zayavky']; ?>">

                <div class="form-group">
                    <label for="commentary-<?php echo (int)$z['id_zayavky']; ?>">
                        Комментарий
                    </label>
                    <textarea
                        id="commentary-<?php echo (int)$z['id_zayavky']; ?>"
                        name="commentary"
                        rows="3"
                        maxlength="255"
                        placeholder="Поделитесь впечатлениями об обучении"
                        required></textarea>
                </div>

                <div class="form-group">
                    <label>Оценка</label>
                    <div class="star-input">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <label class="star-label" title="<?php echo $i; ?> из 5">
                                <input
                                    type="radio"
                                    name="zvezdy"
                                    value="<?php echo $i; ?>"
                                    <?php echo $i === 5 ? 'required' : ''; ?>>
                                <span aria-hidden="true">★</span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <button type="submit" class="btn-sm">Отправить отзыв</button>
            </form>
        </div>

    <?php else: ?>
        <p class="hint-text">
            Отзыв можно будет оставить после того, как администратор
            переведёт заявку в статус «Обучение завершено».
        </p>
    <?php endif; ?>
</div>
        <?php endforeach; ?>
    </div>

    <p class="zayavki-actions">
        <a href="/DE/korochki/pages/new_zayavka.php" class="btn-submit btn-inline">+ Новая заявка</a>
    </p>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';