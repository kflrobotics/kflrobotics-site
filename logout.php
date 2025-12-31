<?php
require_once __DIR__ . '/config/bootstrap.php';
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
