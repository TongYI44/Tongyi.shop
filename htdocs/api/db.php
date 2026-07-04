<?php
/**
 * เชื่อมต่อฐานข้อมูล MySQL ผ่าน PDO
 */

require_once __DIR__ . '/../config.php';

function getDb(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'เชื่อมต่อฐานข้อมูลไม่สำเร็จ',
            'detail' => APP_DEBUG ? $e->getMessage() : null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return $pdo;
}
