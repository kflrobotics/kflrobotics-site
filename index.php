<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php
require_once __DIR__ . '/config/bootstrap.php';
$SEO_TITLE = t('index.seo.title');
$SEO_DESC  = t('index.seo.desc');
$SEO_PATH  = "/";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/config/captcha.php';
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Login kontrolü (formu gizlemek için)
$isLoggedIn = false;
$pdo = Database::connection();
if (isset($_SESSION['user_id'])) {
    $check = $pdo->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
    $check->execute([$_SESSION['user_id']]);
    if ($check->fetch()) {
        $isLoggedIn = true;
    } else {
        session_unset();
        session_destroy();
    }
}

$message = '';
$isError = false;

if (!$isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['password_confirm'] ?? '');
    $captchaToken = $_POST['cf-turnstile-response'] ?? '';
    $captchaToken = $_POST['cf-turnstile-response'] ?? null;
    [$captchaOk, $captchaMsg] = turnstile_verify($captchaToken, $_SERVER['REMOTE_ADDR'] ?? null);

    if (!$captchaOk) {
        $message = t('index.form.errors.turnstile');
        $isError = true;
    }

    if (!verify_csrf($token)) {
        $message = t('index.form.errors.csrf');
        $isError = true;
    } elseif (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 60) {
        $message = t('index.form.errors.name_len');
        $isError = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = t('index.form.errors.email_invalid');
        $isError = true;
    } elseif (mb_strlen($password) < 8) {
        $message = t('index.form.errors.pass_len');
        $isError = true;
    } elseif ($password !== $confirm) {
        $message = t('index.form.errors.pass_mismatch');
        $isError = true;
    } else {
        // users tablosu kontrolü
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = t('index.form.errors.email_exists');
            $isError = true;
        }
    }

    if (!$isError) {
        // registration_requests kontrolü (sadece pending engellensin)
        $stmt = $pdo->prepare("SELECT id FROM registration_requests WHERE email = ? AND status = 'pending' LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = t('index.form.errors.pending_request');
            $isError = true;
        }
    }

    if (!$isError) {
        // Önceden onaylanmış kayıt isteği tabloda duruyorsa temizle ki UNIQUE hatası olmasın
        $pdo->prepare('DELETE FROM registration_requests WHERE email = :email AND status = "approved"')
            ->execute(['email' => $email]);

        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $ins = $pdo->prepare('INSERT INTO registration_requests (full_name, email, password_hash) VALUES (:n, :e, :p)');
            $ins->execute(['n' => $fullName, 'e' => $email, 'p' => $hash]);
            $message = t('index.form.success.request_sent');
            $isError = false;
            // Adminlere "yeni kayıt isteği" maili gönder
            $admins = $pdo->prepare('SELECT name, email FROM users WHERE role = "admin"');
            $admins->execute();
            $adminRows = $admins->fetchAll(PDO::FETCH_ASSOC);

            $adminLink = (isset($_SERVER['HTTP_HOST'])
                ? 'https://' . $_SERVER['HTTP_HOST'] . '/admin.php'
                : '/admin.php'
            );

            foreach ($adminRows as $a) {
                if (empty($a['email'])) continue;

                sendKflMail(
                    $a['email'],
                    t('index.mail.new_request.subject'),
                    t('index.mail.new_request.title'),
                    $a['name'] ?? t('index.mail.new_request.default_name'),
                    t('index.mail.new_request.body_prefix') . "\n\n" . t('index.mail.new_request.body_name') . ": {$fullName}\n" . t('index.mail.new_request.body_email') . ": {$email}",
                    t('index.mail.new_request.cta'),
                    $adminLink
                );
            }

        } catch (PDOException $e) {
            $message = t('index.form.errors.create_failed');
            $isError = true;
        }
    }
}

$csrf = csrf_token();
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo t('index.head.title'); ?></title>
    <?php require __DIR__ . "/partials/head.php"; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&family=General+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
    <link rel="stylesheet" href="/assets/css/lang.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/lang.css') ?>">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="page-index is-loading">
    <?php require_once __DIR__ . '/partials/loader.php'; ?>
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="page">
        <section class="hero-panel">
            <div class="glass hero-main hero-card" style="<?php echo $isLoggedIn ? 'justify-content:center; text-align:center;' : ''; ?>">
                <div class="hero-left" style="<?php echo $isLoggedIn ? 'max-width:760px; margin:0 auto; text-align:center;' : ''; ?>">
                    <p class="badge"><?php echo t('index.hero.badge'); ?></p>
                    <h1><?php echo t('index.hero.h1'); ?></h1>
                    <p class="lead"><?php echo t('index.hero.lead'); ?></p>
                    <div class="meta" style="<?php echo $isLoggedIn ? 'justify-content:center;' : ''; ?>">
                        <span class="tag"><?php echo t('index.hero.tags.robotics'); ?></span>
                        <span class="tag"><?php echo t('index.hero.tags.teamwork'); ?></span>
                        <span class="tag"><?php echo t('index.hero.tags.vex'); ?></span>
                    </div>
                </div>

                <?php if (!$isLoggedIn): ?>
                <div class="hero-right">
                    <div class="hero-form">
                        <h3><?php echo t('index.form.title'); ?></h3>
                        <?php if ($message): ?>
                            <div class="alert <?php echo $isError ? 'error' : 'success'; ?>">
                                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="/">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="field">
                                <label><?php echo t('index.form.labels.full_name'); ?></label>
                                <input type="text" name="full_name" maxlength="100" value="<?php echo htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="field">
                                <label><?php echo t('index.form.labels.email'); ?></label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="field">
                                <label><?php echo t('index.form.labels.password'); ?></label>
                                <input type="password" name="password" minlength="8" required>
                            </div>
                            <div class="field">
                                <label><?php echo t('index.form.labels.password_confirm'); ?></label>
                                <input type="password" name="password_confirm" minlength="8" required>
                            </div>
                            <div class="captcha-wrap">
                                <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars(turnstile_site_key(), ENT_QUOTES, 'UTF-8') ?>"></div>
                            </div>

                            <button type="submit" class="cta-btn"><?php echo t('index.form.submit'); ?></button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <!-- Vizyonumuz -->
        <section class="vision-section reveal reveal-up">
            <div class="glass vision-box">
                <div class="vision-text">
                    <p class="badge"><?php echo t('index.vision.badge'); ?></p>
                    <h2><?php echo t('index.vision.title'); ?></h2>
                    <p class="lead">
                        <?php echo t('index.vision.lead'); ?>
                    </p>
                </div>
                <div class="vision-cta">
                    <a class="cta-btn" href="vision.php"><?php echo t('index.vision.cta'); ?></a>
                </div>
            </div>
        </section>

      <!-- Takım Hakkında -->
        <section class="about-team-section reveal">
            <h2><?php echo t('index.about.title'); ?></h2>
            <div class="glass about-team">
                <div class="about-text">
                    <p class="lead">
                        <?php echo t('index.about.lead'); ?>
                    </p>
                </div>
                <div class="about-cta">
                    <a class="cta-btn" href="team.php"><?php echo t('index.about.cta'); ?></a>
                </div>
            </div>
        </section>
        <!-- Hedeflerimiz -->
        <section class="features-section reveal left">
            <h2><?php echo t('index.goals.title'); ?></h2>
            <div class="features-grid">
                <?php
                $features = [
                    ['title' => t('index.goals.items.1.title'), 'text' => t('index.goals.items.1.text'), 'side' => 'left',  'img' => 'assets/images/goals/goal1.jpg'],
                    ['title' => t('index.goals.items.2.title'), 'text' => t('index.goals.items.2.text'), 'side' => 'right', 'img' => 'assets/images/goals/goal2.jpg'],
                    ['title' => t('index.goals.items.3.title'), 'text' => t('index.goals.items.3.text'), 'side' => 'left',   'img' => 'assets/images/goals/goal3.jpg'],
                ];
                foreach ($features as $feature):
                    $class = $feature['side'] === 'left' ? 'reveal-left' : 'reveal-right';
                    $bg    = isset($feature['img']) ? htmlspecialchars($feature['img'], ENT_QUOTES, 'UTF-8') : '';
                ?>
                    <article class="feature-card reveal <?php echo $class; ?>">
                        <div class="feature-visual feature-visual-img" style="background-image:url('<?php echo $bg; ?>');"></div>
                        <div class="feature-body">
                            <h3><?php echo htmlspecialchars($feature['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars($feature['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <!-- Önerileriniz -->
        <section class="about-team-section reveal right">
            <h2><?php echo t('index.suggests.title'); ?></h2>
            <div class="glass about-team">
                <div class="about-text">
                    <p class="lead">
                        <?php echo t('index.suggests.lead'); ?>
                    </p>
                </div>
                <div class="about-cta">
                    <a class="cta-btn" href="suggests.php"><?php echo t('index.suggests.cta'); ?></a>
                </div>
            </div>
        </section>
        <section class="about-team-section reveal right">
        <h2>Sponsorumuz Olarak Takımımızı Destekleyin</h2>
        <div class="glass about-team">
            <div class="about-text">
            <p class="lead">
                <?php echo t('sponsor.why.lead'); ?>
            </p>
            <div class="btn-row" style="margin-top:16px;">
                <a class="btn primary" href="/sponsors" id="goPackages"><?php echo t('sponsor.index.why.cta'); ?></a>
            </div>
            </div>
        </div>
        </section>
        <!-- Sponsorlar -->
        <section class="sponsors-section reveal up">
          <div class="glass sponsors-wrap">
            <div class="sponsors-head">
              <h3><?php echo t('index.sponsors.title'); ?></h3>
              <p class="lead"><?php echo t('index.sponsors.lead'); ?></p>
            </div>

            <div class="sponsors-slider" id="sponsorsSlider">
              <div class="sponsors-track">
                <div class="sponsor-card" aria-label="Sponsor 1">
                  <img src="assets/images/sponsors/sponsor1.png" alt="Sponsor 1">
                </div>
                <div class="sponsor-card" aria-label="Sponsor 2">
                  <img src="assets/images/sponsors/sponsor2.png" alt="Sponsor 2">
                </div>
                <div class="sponsor-card" aria-label="Sponsor 3">
                  <img src="assets/images/sponsors/sponsor3.png" alt="Sponsor 3">
                </div>
                <div class="sponsor-card" aria-label="Sponsor 4">
                  <img src="assets/images/sponsors/sponsor4.png" alt="Sponsor 4">
                </div>
                <div class="sponsor-card" aria-label="Sponsor 5">
                  <img src="assets/images/sponsors/sponsor5.png" alt="Sponsor 5">
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- Spotlight -->
        <section class="kfl-mark" aria-hidden="true">
        <div class="kfl-mark-inner" id="kflMark">
            <img src="/assets/images/kflGlass.png" alt="KFLRobotics" class="kfl-mark-img" draggable="false">
            <div class="kfl-mark-spot"></div>
        </div>
        </section>



    </main>

    <script>
        window.initReveal = function () {
            const reveals = document.querySelectorAll('.reveal');

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                root: null,
                threshold: 0.25
            });

            reveals.forEach(el => observer.observe(el));
        };
    </script>
    <script src="/assets/js/kflrobotics.js?v=1"></script>
    <script src="assets/js/sponsors.js"></script>
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
