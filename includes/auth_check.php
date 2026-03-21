<?php
// =============================================
// includes/auth_check.php
// Include di halaman yang butuh proteksi login
//
// Cara pakai:
//   require_once '../includes/auth_check.php';
//   require_role('admin');   ← khusus admin
//   require_role('guru');    ← khusus guru
//   require_login();         ← semua yang sudah login
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Pastikan user sudah login.
 * Kalau belum → redirect ke halaman login.
 */
function require_login(string $redirect = '/auth/login.php'): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Pastikan user sudah login DAN punya role tertentu.
 * Kalau belum login → redirect login.
 * Kalau role salah  → redirect ke halaman utama (403).
 */
function require_role(string $role, string $redirect = '/index.php'): void {
    require_login();
    if ($_SESSION['role'] !== $role) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Cek apakah user sudah login (return bool, tidak redirect)
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Ambil data session user saat ini
 */
function current_user(): array {
    return [
        'id'    => $_SESSION['user_id']   ?? null,
        'name'  => $_SESSION['user_name'] ?? 'Tamu',
        'role'  => $_SESSION['role']      ?? null,
        'email' => $_SESSION['email']     ?? null,
    ];
}