<?php
require_once __DIR__ . '/config/bootstrap.php';

$SEO_TITLE = t('team.seo.title', 'KFL Robotics | Ekibimiz');
$SEO_DESC  = t('team.seo.desc', 'KFL Robotics takım üyeleri, görev dağılımı ve VEX Robotics hedeflerimiz.');
$SEO_PATH  = '/team';

/** Baş harf üret */
function initials(string $name): string {
	$parts = preg_split('/\s+/', trim($name));
	$ini = '';
	foreach ($parts as $p) {
		if ($p === '') continue;
		$ini .= mb_strtoupper(mb_substr($p, 0, 1));
		if (mb_strlen($ini) >= 2) break;
	}
	return $ini ?: 'KFL';
}

/** Basit ikon svg */
function icon_svg(string $key): string {
	switch ($key) {
		case 'instagram':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.5 2h9A5.5 5.5 0 0 1 22 7.5v9A5.5 5.5 0 0 1 16.5 22h-9A5.5 5.5 0 0 1 2 16.5v-9A5.5 5.5 0 0 1 7.5 2Zm0 2A3.5 3.5 0 0 0 4 7.5v9A3.5 3.5 0 0 0 7.5 20h9A3.5 3.5 0 0 0 20 16.5v-9A3.5 3.5 0 0 0 16.5 4h-9Zm4.5 4.2a3.8 3.8 0 1 1 0 7.6 3.8 3.8 0 0 1 0-7.6Zm0 2a1.8 1.8 0 1 0 0 3.6 1.8 1.8 0 0 0 0-3.6ZM17.7 6.9a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg>';
		case 'linkedin':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 6.8A2.2 2.2 0 1 1 6.5 2.4a2.2 2.2 0 0 1 0 4.4ZM3.6 21.6h5.8V8.3H3.6v13.3ZM14 8.3h-5.6v13.3H14v-7c0-1.8.3-3.5 2.5-3.5 2.2 0 2.2 2 2.2 3.6v6.9H24V13.6c0-3.6-.8-6.4-5-6.4-2 0-3.4 1.1-4 2.1h-.1V8.3Z"/></svg>';
		case 'github':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-3.2 19.5c.5.1.7-.2.7-.5v-1.8c-2.9.6-3.5-1.2-3.5-1.2-.5-1.1-1.2-1.4-1.2-1.4-1-.7.1-.7.1-.7 1.1.1 1.7 1.1 1.7 1.1 1 .1.8 1.7 2.9 1.2.1-.7.4-1.2.7-1.5-2.3-.3-4.7-1.1-4.7-5a3.9 3.9 0 0 1 1-2.7c-.1-.3-.4-1.3.1-2.7 0 0 .9-.3 2.8 1a9.7 9.7 0 0 1 5.1 0c2-1.3 2.8-1 2.8-1 .5 1.4.2 2.4.1 2.7a3.9 3.9 0 0 1 1 2.7c0 3.9-2.4 4.7-4.7 5 .4.3.7 1 .7 2v3c0 .3.2.6.7.5A10 10 0 0 0 12 2Z"/></svg>';
		case 'mail':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5v2Z"/></svg>';
		case 'phone':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.5 3 3.9 5.3 6.9 6.9l2.3-2.3c.3-.3.7-.4 1.1-.3 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V21c0 .6-.4 1-1 1C11.4 22 2 12.6 2 1c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1l-2.2 2.1Z"/></svg>';
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 14.6h-2V11h2v5.6Zm0-7.6h-2V7h2v2Z"/></svg>';
	}
}

// Sayfa dili (html lang)
$htmlLang = $_SESSION['lang'] ?? ($_COOKIE['site_lang'] ?? 'tr');

// Üyeler
$members = [
	[
		'name'  => 'A. Işılay',
		'role'  => 'KFL Vex Robotics · Leader',
		'tag'   => 'lider',
		'bio'   => 'Takım koordinasyonu, proje planlama ve yarışma stratejisi.',
		'email' => 'asya@kflrobotics.com',
		'social'=> ['instagram'=>'https://instagram.com/asya.isilay04/']
	],
/*	[
		'name'  => 'T. Çelik',
		'role'  => 'KFL Vex Robotics · Coding',
		'tag'   => 'yazilim',
		'bio'   => 'Robot.',
		'email' => 'tuna@kflrobotics.com',
		'social'=> ['instagram'=>'https://instagram.com/charliekirk1776']
	], */
	[
		'name'  => 'E. Özdemir',
		'role'  => 'KFL Vex Robotics · Coding',
		'tag'   => 'yazilim',
		'bio'   => 'Robot kontrol yazılımı, web developing ve otomasyon.',
		'email' => 'ege@kflrobotics.com',
		'social'=> ['instagram'=>'https://instagram.com/egeoozzdemir']
	],
	[
		'name'  => 'E. Sağdıç',
		'role'  => 'KFL Vex Robotics · PR',
		'tag'   => 'destek',
		'bio'   => 'Sponsorluk, sosyal medya yönetimi ve iletişim süreçleri.',
		'email' => 'eslem@kflrobotics.com',
		'social'=> ['instagram'=>'https://instagram.com/eslems14']
	], 
];
?>
<!doctype html>
<html lang="<?= e($htmlLang) ?>">
<head>
	<?php require __DIR__ . '/partials/head.php'; ?>

	<link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
	<link rel="stylesheet" href="/assets/css/teams.css?v=<?= (int)time(); ?>">
</head>

<body class="page-team">
	<?php require_once __DIR__ . '/partials/loader.php'; ?>
	<?php require_once __DIR__ . '/partials/navbar.php'; ?>

	<main class="page">
		<div class="glass team-hero">
			<div>
				<h1><?= e(t('team.hero.title', 'Ekip')) ?></h1>
				<p class="lead"><?= e(t('team.hero.lead', 'KFL Robotics takımında yer alan takım üyeleri.')) ?></p>
			</div>
		</div>

		<section class="team-grid team-grid-v2">
			<?php foreach ($members as $m): ?>
				<article class="team-card-v2" data-tag="<?= e($m['tag']) ?>">
					<div class="team-top">
						<div class="team-avatar"><?= e(initials($m['name'])) ?></div>
						<div class="team-title">
							<h3><?= e($m['name']) ?></h3>
							<p class="team-role"><?= e($m['role']) ?></p>
						</div>
						<div class="team-badge"></div>
					</div>

					<p class="team-bio"><?= e($m['bio']) ?></p>

					<div class="team-actions">
						<span class="chip"><?= e($m['email']) ?></span>
					</div>

					<div class="team-social">
						<?php foreach ($m['social'] as $k => $v): ?>
							<?php if (!empty($v)): ?>
								<a class="social-btn"
								   href="<?= e($v) ?>"
								   target="_blank"
								   rel="noopener noreferrer"
								   aria-label="<?= e($m['name']) ?> · <?= e($k) ?>">
									<?= icon_svg($k); ?>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</section>
	</main>

	<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
