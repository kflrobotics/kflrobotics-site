<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Cloudflare Turnstile helpers
 */
function turnstile_site_key(): string {
  return (string) env('TURNSTILE_SITE_KEY', '');
}

function turnstile_secret_key(): string {
  return (string) env('TURNSTILE_SECRET_KEY', '');
}

/**
 * Verify Turnstile token server-side.
 * Returns: [bool $ok, string $message]
 */
function turnstile_verify(?string $token, ?string $remoteIp = null): array
{
  $secret = turnstile_secret_key();

  if ($secret === '') {
    return [false, 'Turnstile secret missing.'];
  }
  if (!$token) {
    return [false, 'Captcha doğrulaması gerekli.'];
  }

  $payload = http_build_query([
    'secret'   => $secret,
    'response' => $token,
    'remoteip' => $remoteIp ?: '',
  ]);

  $opts = [
    'http' => [
      'method'  => 'POST',
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'content' => $payload,
      'timeout' => 8,
    ]
  ];

  $ctx = stream_context_create($opts);
  $raw = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $ctx);

  if ($raw === false) {
    return [false, 'Captcha doğrulama servisine erişilemedi.'];
  }

  $data = json_decode($raw, true);
  if (!is_array($data)) {
    return [false, 'Captcha doğrulama yanıtı okunamadı.'];
  }

  if (!empty($data['success'])) {
    return [true, 'OK'];
  }

  // İstersen hata kodlarını logla:
  if (!empty($data['error-codes'])) {
    error_log('[Turnstile] Verify failed: ' . json_encode($data['error-codes']));
  }

  return [false, 'Captcha doğrulaması başarısız.'];
}
