<?php
/**
 * ฟังก์ชันช่วยเหลือทั่วไปสำหรับ API
 */

function jsonResponse($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function genId(string $prefix): string
{
    return $prefix . '-' . bin2hex(random_bytes(4));
}

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // เปิดบรรทัดนี้ถ้าเว็บใช้ HTTPS (แนะนำให้เปิดเสมอบนโปรดักชัน)
    ]);
    session_start();
}

function isLoggedIn(): bool
{
    startAppSession();
    return !empty($_SESSION['loggedIn']);
}

function requireAuth(): void
{
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'ยังไม่ได้เข้าสู่ระบบ'], 401);
    }
}

function bodyStr(array $body, string $key, string $default = ''): string
{
    return isset($body[$key]) ? trim((string) $body[$key]) : $default;
}

/**
 * แปลง tags: รับได้ทั้ง array หรือ string คั่นด้วยจุลภาค -> คืนเป็น array เสมอ
 */
function normalizeTags($tags): array
{
    if (is_array($tags)) {
        return array_values(array_filter(array_map('trim', $tags), fn($t) => $t !== ''));
    }
    $tags = (string) $tags;
    if ($tags === '') {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $tags)), fn($t) => $t !== ''));
}
