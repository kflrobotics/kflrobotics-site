<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/bootstrap.php';
function requireLogin(): array
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $pdo = Database::connection();
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }

    return $user;
}

function requireAdmin(): void
{
    $user = requireLogin();
    if ($user['role'] !== 'admin') {
        header('Location: panel.php');
        exit;
    }
}
