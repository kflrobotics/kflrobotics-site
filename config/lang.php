<?php
declare(strict_types=1);
$supported = ['tr', 'en', 'de'];

$isHttps =
  (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
  || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
  || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
  $chosen = $_GET['lang'];

  setcookie('site_lang', $chosen, [
    'expires'  => time() + 60 * 60 * 24 * 365,
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => false,
    'samesite' => 'Lax',
  ]);

  $_SESSION['lang'] = $chosen;

  $uri = $_SERVER['REQUEST_URI'] ?? '/';
  $parts = parse_url($uri);

  $path = $parts['path'] ?? '/';
  $query = [];
  if (!empty($parts['query'])) {
    parse_str($parts['query'], $query);
    unset($query['lang']);
  }

  $newQuery = http_build_query($query);
  $cleanUrl = $path . ($newQuery ? ('?' . $newQuery) : '');

  header('Location: ' . $cleanUrl, true, 302);
  exit;
}

$lang = 'tr';

if (!empty($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], $supported, true)) {
  $lang = $_COOKIE['site_lang'];
} elseif (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], $supported, true)) {
  $lang = $_SESSION['lang'];
}

$_SESSION['lang'] = $lang;

$langFile = dirname(__DIR__) . '/lang/' . $lang . '.php';
$L = [];

if (is_file($langFile)) {
  $loaded = require $langFile;
  if (is_array($loaded)) $L = $loaded;
}

if (!function_exists('t')) {
  function t(string $key, ?string $fallback = null): string {
    global $L;
    if (isset($L[$key])) return (string)$L[$key];
    return $fallback ?? $key;
  }
}

if (!function_exists('e')) {
  function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }
}
