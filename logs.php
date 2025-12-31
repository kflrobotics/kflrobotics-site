<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
$pdo = Database::connection();
$allowedRoles = ['pd','admin','coding','leader'];

/* =========================
   AUTH
   ========================= */
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

if (!$currentUser || !in_array($currentUser['role'], $allowedRoles, true)) {
    http_response_code(403);
    exit(t('logs.auth.forbidden'));
}

/* =========================
   HELPERS
   ========================= */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function turkishMonthName(int $m): string {
    return [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'][$m];
}
function formatDateTRFull(string $date): string {
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dt) return $date;
    $days = [
        'Monday'=>'Pazartesi','Tuesday'=>'Salı','Wednesday'=>'Çarşamba',
        'Thursday'=>'Perşembe','Friday'=>'Cuma',
        'Saturday'=>'Cumartesi','Sunday'=>'Pazar'
    ];
    return $days[$dt->format('l')] . ', ' .
           $dt->format('d') . ' ' .
           turkishMonthName((int)$dt->format('m')) . ' ' .
           $dt->format('Y');
}

/* =========================
   DATE LOGIC
   ========================= */
$monthParam = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) $monthParam = date('Y-m');

$baseDate = DateTime::createFromFormat('Y-m-d', $monthParam . '-01');
$year = (int)$baseDate->format('Y');
$month = (int)$baseDate->format('m');
$daysInMonth = (int)$baseDate->format('t');
$firstIso = (int)$baseDate->format('N');

$prevMonth = (clone $baseDate)->modify('-1 month')->format('Y-m');
$nextMonth = (clone $baseDate)->modify('+1 month')->format('Y-m');

$todayStr = date('Y-m-d');
$selectedDate = $_GET['date'] ?? $todayStr;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) $selectedDate = $todayStr;

/* =========================
   ADD NOTE
   ========================= */
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_note') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $message = t('logs.form.errors.csrf');
    } else {
        $note = trim($_POST['note'] ?? '');
        if (mb_strlen($note) < 3) {
            $message = t('logs.form.errors.note_min');
        } else {
            $ins = $pdo->prepare('INSERT INTO daily_logs (log_date, user_id, note) VALUES (?, ?, ?)');
            $ins->execute([$selectedDate, $currentUser['id'], $note]);
            header('Location: logs.php?month='.$monthParam.'&date='.$selectedDate);
            exit;
        }
    }
}

/* =========================
   DELETE NOTE
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_note') {
    if (!verify_csrf($_POST['csrf'] ?? '')) exit(t('logs.form.errors.csrf_short'));

    $noteId = (int)($_POST['note_id'] ?? 0);
    if ($noteId <= 0) exit(t('logs.form.errors.invalid_id'));

    $q = $pdo->prepare('SELECT user_id FROM daily_logs WHERE id = ?');
    $q->execute([$noteId]);
    $note = $q->fetch();

    if (!$note) exit(t('logs.form.errors.note_missing'));

    $isAdmin = in_array($currentUser['role'], ['admin','leader'], true);
    $isOwner = ((int)$note['user_id'] === (int)$currentUser['id']);

    if (!$isAdmin && !$isOwner) {
        http_response_code(403);
        exit(t('logs.auth.no_permission'));
    }

    $pdo->prepare('DELETE FROM daily_logs WHERE id = ?')->execute([$noteId]);
    header('Location: logs.php?month='.$monthParam.'&date='.$selectedDate);
    exit;
}

/* =========================
   FETCH DATA
   ========================= */
$daysWithNotes = [];
$q = $pdo->prepare('SELECT DISTINCT log_date FROM daily_logs WHERE log_date BETWEEN ? AND ?');
$q->execute([$baseDate->format('Y-m-01'), $baseDate->format('Y-m-t')]);
foreach ($q->fetchAll() as $r) $daysWithNotes[$r['log_date']] = true;

$notes = [];
$sel = $pdo->prepare('
    SELECT d.id, d.user_id, d.note, d.created_at, u.name
    FROM daily_logs d
    JOIN users u ON u.id = d.user_id
    WHERE d.log_date = ?
    ORDER BY d.created_at DESC
');
$sel->execute([$selectedDate]);
$notes = $sel->fetchAll();

$csrf = csrf_token();
$weekdays = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
?>

<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo t('logs.head.title'); ?></title>
<link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
<link rel="stylesheet" href="/assets/css/logs.css??v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/logs.css') ?>">
<?php require __DIR__ . "/partials/head.php"; ?>
</head>

<body class="page-panel">
<?php require __DIR__.'/partials/navbar.php'; ?>

<div class="page">
<div class="glass">
  <h2><?php echo t('logs.hero.title'); ?></h2>
  <p class="lead"><?php echo t('logs.hero.lead'); ?></p>
</div>

<div class="logs-wrap">

<div class="glass calendar">
  <div class="calendar-head">
    <a class="cal-nav" href="logs.php?month=<?=$prevMonth?>">&lt; <?php echo t('logs.calendar.prev'); ?></a>
    <div class="cal-month"><?=turkishMonthName($month).' '.$year?></div>
    <a class="cal-nav" href="logs.php?month=<?=$nextMonth?>"><?php echo t('logs.calendar.next'); ?> &gt;</a>
  </div>

  <div class="calendar-weekdays">
    <?php foreach($weekdays as $wd): ?><div class="weekday"><?=$wd?></div><?php endforeach; ?>
  </div>

  <div class="calendar-days">
    <?php for($i=1;$i<$firstIso;$i++): ?><div class="day empty"></div><?php endfor; ?>
    <?php for($d=1;$d<=$daysInMonth;$d++):
      $dateStr = sprintf('%04d-%02d-%02d',$year,$month,$d);
      $cls = 'day';
      if($dateStr===$todayStr) $cls.=' today';
      if($dateStr===$selectedDate) $cls.=' selected';
      if(isset($daysWithNotes[$dateStr])) $cls.=' has-note';
    ?>
    <a class="<?=$cls?>" href="logs.php?month=<?=$monthParam?>&date=<?=$dateStr?>"><?=$d?></a>
    <?php endfor; ?>
  </div>
</div>

<div class="glass day-panel">
  <div class="badge"><?php echo t('logs.day.badge'); ?></div>
  <h3><?=formatDateTRFull($selectedDate)?></h3>

  <?php if($message): ?><div class="alert error"><?=$message?></div><?php endif; ?>

  <form method="post" class="log-form">
    <input type="hidden" name="csrf" value="<?=$csrf?>">
    <input type="hidden" name="action" value="add_note">
    <textarea name="note" rows="3" placeholder="<?php echo t('logs.form.note_placeholder'); ?>" required></textarea>
    <button class="submit"><?php echo t('logs.form.submit'); ?></button>
  </form>

  <div class="log-list">
    <h4><?php echo t('logs.notes.title'); ?></h4>
    <?php if($notes): foreach($notes as $n):
      $canDelete = in_array($currentUser['role'], ['admin','leader'], true) || $n['user_id']===$currentUser['id'];
    ?>
    <div class="log-item">
      <div class="log-meta">
        <?=$n['name']?> · <?=date('d/m/Y H:i', strtotime($n['created_at']))?>
        <?php if($canDelete): ?>
        <form method="post" style="display:inline" onsubmit="return confirm('<?php echo t('logs.notes.confirm_delete'); ?>')">
          <input type="hidden" name="csrf" value="<?=$csrf?>">
          <input type="hidden" name="action" value="delete_note">
          <input type="hidden" name="note_id" value="<?=$n['id']?>">
          <button class="log-delete"><?php echo t('logs.notes.delete'); ?></button>
        </form>
        <?php endif; ?>
      </div>
      <div class="log-text"><?=nl2br(htmlspecialchars($n['note']))?></div>
    </div>
    <?php endforeach; else: ?>
      <p class="meta-small"><?php echo t('logs.notes.empty'); ?></p>
    <?php endif; ?>
  </div>
</div>

</div>
</div>

<?php require __DIR__.'/partials/footer.php'; ?>
</body>
</html>
