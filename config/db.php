<?php
// =============================================
// config/db.php
// Koneksi database - include di setiap halaman
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // ganti sesuai user MySQL kamu
define('DB_PASS', '');           // ganti sesuai password MySQL kamu
define('DB_NAME', 'ilmuqayyim');
define('DB_CHARSET', 'utf8mb4');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // Di production, jangan tampilkan error ke user
    // Log error ke file, lalu redirect ke halaman error
    error_log('DB Connection failed: ' . mysqli_connect_error());
    die(json_encode(['error' => 'Koneksi database gagal.']));
}

mysqli_set_charset($conn, DB_CHARSET);

// ── Helper functions ─────────────────────────

/**
 * Escape string untuk mencegah SQL injection
 * Pakai ini setiap kali input dari user masuk ke query
 */
function esc(mysqli $conn, string $val): string {
    return mysqli_real_escape_string($conn, trim($val));
}

/**
 * Jalankan query dan return result
 * Untuk SELECT
 */
function db_query(mysqli $conn, string $sql): mysqli_result|bool {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log('Query error: ' . mysqli_error($conn) . ' | SQL: ' . $sql);
    }
    return $result;
}

/**
 * Ambil semua baris sebagai array asosiatif
 */
function db_fetch_all(mysqli $conn, string $sql): array {
    $result = db_query($conn, $sql);
    if (!$result) return [];
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);
    return $rows;
}

/**
 * Ambil satu baris saja
 */
function db_fetch_one(mysqli $conn, string $sql): array|null {
    $result = db_query($conn, $sql);
    if (!$result) return null;
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $row ?: null;
}

/**
 * Jalankan INSERT / UPDATE / DELETE
 * Return: insert_id untuk INSERT, affected_rows untuk UPDATE/DELETE, false kalau gagal
 */
function db_execute(mysqli $conn, string $sql): int|false {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log('Execute error: ' . mysqli_error($conn) . ' | SQL: ' . $sql);
        return false;
    }
    // Kalau INSERT, return insert_id
    if (mysqli_insert_id($conn)) return mysqli_insert_id($conn);
    // Kalau UPDATE/DELETE, return affected_rows
    return mysqli_affected_rows($conn);
}