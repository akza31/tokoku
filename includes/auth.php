<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function require_owner(): void {
    require_login();
    if (current_user()['role'] !== 'owner') {
        http_response_code(403);
        die('Akses ditolak. Khusus pemilik.');
    }
}

function is_owner(): bool {
    $u = current_user();
    return $u && $u['role'] === 'owner';
}

function audit_log(string $activity, string $detail = ''): void {
    $u = current_user();
    $stmt = db()->prepare('INSERT INTO audit_logs (user_id, activity, detail) VALUES (?, ?, ?)');
    $stmt->execute([$u['id'] ?? null, $activity, $detail]);
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        die('CSRF token tidak valid.');
    }
}
