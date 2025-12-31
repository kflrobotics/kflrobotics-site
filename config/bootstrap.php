<?php
declare(strict_types=1);
require_once __DIR__ . '/env.php';
load_env_file(__DIR__ . '/../.env');
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/lang.php';
