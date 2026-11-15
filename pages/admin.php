<?php
// pages/admin.php
require_once __DIR__ . '/../helpers/db.php';
session_start();

// ---------- Защита: только администратор ----------
if (empty($_SESSION['user_id']) || (int)($_SESSION['user_role_id'] ?? 0) !== 2) {
    header('Location: /DE/korochki/pages/auth.php');
    exit;
}

$success = '';
$error   = '';

// ============================================================
// 1. СМЕНА СТАТУСА ЗАЯВКИ
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zayavka_id'], $_POST['status_id'])) {
    $zayavkaId = (int)$_POST['zayavka_id'];
    $statusId  = (int)$_POST['status_id'];

    // Проверяем, что заявка и статус существуют
    $checkZ = $pdo->prepare('SELECT id_zayavky FROM zayavki WHERE id_zayavky = :zid LIMIT 1');
    $checkZ->execute(['zid' => $zayavkaId]);

    $checkS = $pdo->prepare('SELECT id_statusi, status FROM statusi WHERE id_statusi = :sid LIMIT 1');
    $checkS->execute(['sid' => $statusId]);
    $statusRow = $checkS->fetch();

    if (!$checkZ->fetch()) {
        $error = 'Заявка не найдена.';
    } elseif (!$statusRow) {
        $error = 'Такого статуса не существует.';
    } else {
        try {
            $upd = $pdo->prepare('UPDATE zayavki SET status_id = :sid WHERE id_zayavky = :zid');
            $upd->execute(['sid' => $statusId, 'zid' => $zayavkaId]);
            $success = 'Статус заявки №' . $zayavkaId . ' изменён на «' . $statusRow['status'] . '».';
        } catch (PDOException $e) {
            $error = 'Ошибка обновления: ' . $e->getMessage();
        }
    }
}

// ============================================================
// 2. СМЕНА РОЛИ ПОЛЬЗОВАТЕЛЯ
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_rol_id'])) {
    $userId = (int)$_POST['user_id'];
    $newRol = (int)$_POST['new_rol_id'];

    if ($userId === (int)$_SESSION['user_id']) {
        $error = 'Нельзя изменить свою собственную роль.';
    } elseif (!in_array($newRol, [1, 2], true)) {
        $error = 'Недопустимая роль.';
    } else {
        $upd = $pdo->prepare('UPDATE polzovately SET rol_id = :rid WHERE id_polzovately = :uid');
        $upd->execute(['rid' => $newRol, 'uid' => $userId]);
        $success = 'Роль пользователя обновлена.';
    }
}

// ============================================================
// 3. СТАТИСТИКА
// ============================================================
$stats = $pdo->query('
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) AS cnt_new,
        SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) AS cnt_progress,
        SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) AS cnt_done,
        SUM(CASE WHEN otzyv_id IS NOT NULL THEN 1 ELSE 0 END) AS cnt_otzyv
    FROM zayavki
')->fetch();

// ============================================================
// 4. СПИСОК ЗАЯВОК
// ============================================================
$zayavki = $pdo->query('
    SELECT
        z.id_zayavky,
        z.nachalo,
        z.otzyv_id,
        p.id_polzovately           AS uid,
        p.fio                      AS polzovatel_fio,
        p.login                    AS polzovatel_login,
        p.telephon                 AS polzovatel_telephon,
        k.kyrs                     AS kurs,
        o.`oplata VARCHAR(255)`    AS oplata,
        s.id_statusi               AS status_id,
        s.status                   AS status,
        ot.commentary              AS otzyv,
        ot.zvezdy                  AS zvezdy
    FROM zayavki z
    LEFT JOIN polzovately p  ON p.id_polzovately   = z.polzovatel_id
    LEFT JOIN kyrsi       k  ON k.id_kyrs          = z.kyrs_id
    LEFT JOIN oplati      o  ON o.`id_oplati(int)` = z.oplata_id
    LEFT JOIN statusi     s  ON s.id_statusi       = z.status_id
    LEFT JOIN otzyvy      ot ON ot.id_otzyvy       = z.otzyv_id
    ORDER BY z.id_zayavky DESC
')->fetchAll();

// ============================================================
// 5. СПИСОК СТАТУСОВ
// ============================================================
$statusi = $pdo->query('SELECT id_statusi, status FROM statusi ORDER BY id_statusi')->fetchAll();

// ============================================================
// 6. СПИСОК ПОЛЬЗОВАТЕЛЕЙ
// ============================================================
$users = $pdo->query('
    SELECT p.*, r.rol
    FROM polzovately p
    LEFT JOIN roli r ON r.id_roli = p.rol_id
    ORDER BY p.id_polzovately DESC
')->fetchAll();

$roli = $pdo->query('SELECT id_roli, rol FROM roli ORDER BY id_roli')->fetchAll();

$pageTitle = 'Панель администратора - Корочки.есть';

ob_start();
?>
<h1 class="admin-title">Панель администратора</h1>

<?php if ($success): ?>
    <div class="message-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="error-messages"><p class="error-text"><?php echo htmlspecialchars($error); ?></p></div>
<?php endif; ?>

<!-- ================== СТАТИСТИКА ================== -->
<div class="admin-stats">
    <div class="stat-card">
        <span class="stat-num"><?php echo (int)$stats['total']; ?></span>
        <span class="stat-label">Всего заявок</span>
    </div>
    <div class="stat-card stat-new">
        <span class="stat-num"><?php echo (int)$stats['cnt_new']; ?></span>
        <span class="stat-label">Новые</span>
    </div>
    <div class="stat-card stat-progress">
        <span class="stat-num"><?php echo (int)$stats['cnt_progress']; ?></span>
        <span class="stat-label">Идёт обучение</span>
    </div>
    <div class="stat-card stat-done">
        <span class="stat-num"><?php echo (int)$stats['cnt_done']; ?></span>
        <span class="stat-label">Обучение завершено</span>
    </div>
    <div class="stat-card stat-otzyv">
        <span class="stat-num"><?php echo (int)$stats['cnt_otzyv']; ?></span>
        <span class="stat-label">С отзывом</span>
    </div>
</div>

<!-- ================== ЗАЯВКИ ================== -->
<section class="admin-section">
    <h2>Заявки пользователей</h2>

    <?php if (empty($zayavki)): ?>
        <p class="empty-text">Заявок пока нет.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Пользователь</th>
                        <th>Телефон</th>
                        <th>Курс</th>
                        <th>Оплата</th>
                        <th>Дата начала</th>
                        <th>Отзыв</th>
                        <th>Статус</th>
                        <th>Изменить статус</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($zayavki as $z): ?>
                    <tr>
                        <td><?php echo (int)$z['id_zayavky']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($z['polzovatel_fio'] ?? '—'); ?></strong><br>
                            <small class="muted">@<?php echo htmlspecialchars($z['polzovatel_login'] ?? '—'); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($z['polzovatel_telephon'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($z['kurs'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($z['oplata'] ?? '—'); ?></td>
                        <td>
                            <?php
                                if (!empty($z['nachalo'])) {
                                    $d = DateTime::createFromFormat('Y-m-d', $z['nachalo']);
                                    echo $d ? $d->format('d.m.Y') : htmlspecialchars($z['nachalo']);
                                } else {
                                    echo '—';
                                }
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($z['otzyv'])): ?>
                                <div class="admin-otzyv">
                                    <div class="stars stars-sm">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo ($i <= (int)$z['zvezdy']) ? 'star-filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="otzyv-text">
                                        <?php echo htmlspecialchars($z['otzyv']); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="muted">— нет —</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo (int)$z['status_id']; ?>">
                                <?php echo htmlspecialchars($z['status'] ?? '—'); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="zayavka_id" value="<?php echo (int)$z['id_zayavky']; ?>">
                                <select name="status_id" class="select-sm">
                                    <?php foreach ($statusi as $s): ?>
                                        <option value="<?php echo (int)$s['id_statusi']; ?>"
                                            <?php echo ((int)$s['id_statusi'] === (int)$z['status_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['status']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-sm">Сохранить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- ================== ПОЛЬЗОВАТЕЛИ ================== -->
<section class="admin-section">
    <h2>Пользователи</h2>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Логин</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Роль</th>
                    <th>Изменить роль</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo (int)$u['id_polzovately']; ?></td>
                    <td><?php echo htmlspecialchars($u['login']); ?></td>
                    <td><?php echo htmlspecialchars($u['fio']); ?></td>
                    <td><?php echo htmlspecialchars($u['telephon']); ?></td>
                    <td>
                        <span class="role-badge role-<?php echo (int)$u['rol_id']; ?>">
                            <?php echo htmlspecialchars($u['rol'] ?? '—'); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ((int)$u['id_polzovately'] === (int)$_SESSION['user_id']): ?>
                            <em class="muted">это вы</em>
                        <?php else: ?>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id_polzovately']; ?>">
                                <select name="new_rol_id" class="select-sm">
                                    <?php foreach ($roli as $r): ?>
                                        <option value="<?php echo (int)$r['id_roli']; ?>"
                                            <?php echo ((int)$r['id_roli'] === (int)$u['rol_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($r['rol']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-sm">Сменить</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<p class="admin-logout">
    Вы вошли как <strong><?php echo htmlspecialchars($_SESSION['user_login']); ?></strong>.
    <a href="/DE/korochki/pages/logout.php">Выйти</a>
</p>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';