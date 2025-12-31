<?php
declare(strict_types=1);

function env(string $key, $default = null) {
  $val = getenv($key);
  if ($val === false || $val === '') return $default;
  return $val;
}

function load_env_file(string $path): void {
  if (!is_file($path) || !is_readable($path)) return;

  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;

    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $k = trim($k);
    $v = trim($v);

    if ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
      $v = substr($v, 1, -1);
    }

    if (getenv($k) === false) {
      putenv("$k=$v");
      $_ENV[$k] = $v;
      $_SERVER[$k] = $v;
    }
  }
}
