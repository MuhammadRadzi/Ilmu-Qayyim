<?php
// =============================================
// config/db.php
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ilmuqayyim');
define('DB_CHARSET', 'utf8mb4');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    error_log('DB Connection failed: ' . mysqli_connect_error());
    die('Koneksi database gagal.');
}

mysqli_set_charset($conn, DB_CHARSET);

function esc(mysqli $conn, string $val): string {
    return mysqli_real_escape_string($conn, trim($val));
}

function db_fetch_all(mysqli $conn, string $sql): array {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log('Query error: ' . mysqli_error($conn) . ' | SQL: ' . $sql);
        return [];
    }
    // Ambil SEMUA baris dulu sebelum free
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    return $rows ?: [];
}

function db_fetch_one(mysqli $conn, string $sql): ?array {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log('Query error: ' . mysqli_error($conn) . ' | SQL: ' . $sql);
        return null;
    }
    // Ambil baris dulu, BARU free
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $row ?: null;
}

function db_execute(mysqli $conn, string $sql): int|false {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log('Execute error: ' . mysqli_error($conn) . ' | SQL: ' . $sql);
        return false;
    }
    $insert_id = mysqli_insert_id($conn);
    if ($insert_id) return $insert_id;
    return mysqli_affected_rows($conn);
}