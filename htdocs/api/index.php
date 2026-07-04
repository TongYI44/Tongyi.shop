<?php
/**
 * ============================================================
 *  TongYI API - PHP + MySQL (แปลงจาก Node/Express เดิม)
 * ============================================================
 *  Endpoint ทั้งหมดเหมือนของเดิมทุกเส้นทาง เพื่อให้ฝั่งหน้าบ้าน
 *  (public/js/script.js, admin/js/admin.js) ใช้งานได้โดยไม่ต้องแก้โค้ด
 * ============================================================
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

startAppSession();

// ---------- หา path ของ API ที่ถูกเรียก ----------
// ตัวอย่าง: /api/projects/proj-1  ->  projects/proj-1
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'); // .../api
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = $requestUri;
if ($scriptDir !== '' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
}
$path = trim($path, '/'); // เช่น "projects/proj-1" หรือ "auth/login"
$segments = $path === '' ? [] : explode('/', $path);
$method = $_SERVER['REQUEST_METHOD'];

$resource = $segments[0] ?? '';
$sub      = $segments[1] ?? null; // เช่น "login" หรือ id ของ project/skill
$id       = $sub;

try {
    switch ($resource) {

        // ============ AUTH ============
        case 'auth':
            handleAuth($method, $sub);
            break;

        // ============ CONTENT (รวม projects+skills) ============
        case 'content':
            if ($method === 'GET') {
                $db = getDb();
                jsonResponse([
                    'projects' => fetchAllProjects($db),
                    'skills'   => fetchAllSkills($db),
                    'products' => fetchAllProducts($db),
                ]);
            }
            jsonResponse(['error' => 'Method not allowed'], 405);
            break;

        // ============ PROJECTS ============
        case 'projects':
            handleProjects($method, $id);
            break;

        // ============ SKILLS ============
        case 'skills':
            handleSkills($method, $id);
            break;

        // ============ PRODUCTS (ร้านค้า) ============
        case 'products':
            handleProducts($method, $id);
            break;

        // ============ CONTACT ============
        case 'contact':
            if ($method === 'POST') {
                handleContactSubmit();
            }
            jsonResponse(['error' => 'Method not allowed'], 405);
            break;

        // ============ MESSAGES (แอดมินดูข้อความติดต่อ) ============
        case 'messages':
            if ($method === 'GET') {
                requireAuth();
                $db = getDb();
                $rows = $db->query('SELECT id, name, email, message, received_at AS receivedAt FROM messages ORDER BY id DESC')->fetchAll();
                jsonResponse($rows);
            }
            jsonResponse(['error' => 'Method not allowed'], 405);
            break;

        default:
            jsonResponse(['error' => 'ไม่พบเส้นทางนี้'], 404);
    }
} catch (Throwable $e) {
    jsonResponse([
        'error'  => 'เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์',
        'detail' => APP_DEBUG ? $e->getMessage() : null,
    ], 500);
}

// ================================================================
//  Handlers
// ================================================================

function handleAuth(string $method, ?string $action): void
{
    if ($action === 'login' && $method === 'POST') {
        $body = getJsonBody();
        $username = bodyStr($body, 'username');
        $password = bodyStr($body, 'password');

        if ($username === '' || $password === '') {
            jsonResponse(['error' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'], 400);
        }

        $db = getDb();
        $stmt = $db->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            jsonResponse(['error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], 401);
        }

        session_regenerate_id(true);
        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $user['username'];

        jsonResponse(['ok' => true, 'username' => $user['username']]);
    }

    if ($action === 'logout' && $method === 'POST') {
        $_SESSION = [];
        session_destroy();
        jsonResponse(['ok' => true]);
    }

    if ($action === 'status' && $method === 'GET') {
        if (isLoggedIn()) {
            jsonResponse(['loggedIn' => true, 'username' => $_SESSION['username'] ?? null]);
        }
        jsonResponse(['loggedIn' => false]);
    }

    jsonResponse(['error' => 'ไม่พบเส้นทางนี้'], 404);
}

// ---------- Projects ----------

function fetchAllProjects(PDO $db): array
{
    $rows = $db->query('SELECT id, title, description, image, tags, link FROM projects ORDER BY sort_order ASC, id ASC')->fetchAll();
    foreach ($rows as &$row) {
        $row['tags'] = $row['tags'] !== null ? json_decode($row['tags'], true) : [];
    }
    return $rows;
}

function handleProjects(string $method, ?string $id): void
{
    $db = getDb();

    if ($method === 'GET' && $id === null) {
        jsonResponse(fetchAllProjects($db));
    }

    if ($method === 'POST' && $id === null) {
        requireAuth();
        $body = getJsonBody();
        $title = bodyStr($body, 'title');
        $description = bodyStr($body, 'description');

        if ($title === '' || $description === '') {
            jsonResponse(['error' => 'กรุณากรอกชื่อและคำอธิบายผลงาน'], 400);
        }

        $newId = genId('proj');
        $image = bodyStr($body, 'image');
        $link  = bodyStr($body, 'link', '#');
        $tags  = normalizeTags($body['tags'] ?? []);

        $stmt = $db->prepare('INSERT INTO projects (id, title, description, image, tags, link, sort_order) VALUES (?, ?, ?, ?, ?, ?, (SELECT n FROM (SELECT COALESCE(MAX(sort_order), 0) + 1 AS n FROM projects) t))');
        $stmt->execute([$newId, $title, $description, $image, json_encode($tags, JSON_UNESCAPED_UNICODE), $link]);

        jsonResponse(['id' => $newId, 'title' => $title, 'description' => $description, 'image' => $image, 'tags' => $tags, 'link' => $link], 201);
    }

    if ($method === 'PUT' && $id !== null) {
        requireAuth();
        $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            jsonResponse(['error' => 'ไม่พบผลงานนี้'], 404);
        }

        $body = getJsonBody();
        $title = array_key_exists('title', $body) ? bodyStr($body, 'title') : $existing['title'];
        $description = array_key_exists('description', $body) ? bodyStr($body, 'description') : $existing['description'];
        $image = array_key_exists('image', $body) ? bodyStr($body, 'image') : $existing['image'];
        $link = array_key_exists('link', $body) ? bodyStr($body, 'link') : $existing['link'];
        $tags = array_key_exists('tags', $body) ? normalizeTags($body['tags']) : json_decode($existing['tags'], true);

        $upd = $db->prepare('UPDATE projects SET title = ?, description = ?, image = ?, tags = ?, link = ? WHERE id = ?');
        $upd->execute([$title, $description, $image, json_encode($tags, JSON_UNESCAPED_UNICODE), $link, $id]);

        jsonResponse(['id' => $id, 'title' => $title, 'description' => $description, 'image' => $image, 'tags' => $tags, 'link' => $link]);
    }

    if ($method === 'DELETE' && $id !== null) {
        requireAuth();
        $stmt = $db->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            jsonResponse(['error' => 'ไม่พบผลงานนี้'], 404);
        }
        jsonResponse(['ok' => true]);
    }

    jsonResponse(['error' => 'Method not allowed'], 405);
}

// ---------- Skills ----------

function fetchAllSkills(PDO $db): array
{
    return $db->query('SELECT id, name, category, icon FROM skills ORDER BY sort_order ASC, id ASC')->fetchAll();
}

function handleSkills(string $method, ?string $id): void
{
    $db = getDb();

    if ($method === 'GET' && $id === null) {
        jsonResponse(fetchAllSkills($db));
    }

    if ($method === 'POST' && $id === null) {
        requireAuth();
        $body = getJsonBody();
        $name = bodyStr($body, 'name');
        if ($name === '') {
            jsonResponse(['error' => 'กรุณากรอกชื่อทักษะ'], 400);
        }
        $category = bodyStr($body, 'category', 'อื่นๆ');
        $icon = bodyStr($body, 'icon', 'code');
        $newId = genId('skill');

        $stmt = $db->prepare('INSERT INTO skills (id, name, category, icon, sort_order) VALUES (?, ?, ?, ?, (SELECT n FROM (SELECT COALESCE(MAX(sort_order), 0) + 1 AS n FROM skills) t))');
        $stmt->execute([$newId, $name, $category, $icon]);

        jsonResponse(['id' => $newId, 'name' => $name, 'category' => $category, 'icon' => $icon], 201);
    }

    if ($method === 'PUT' && $id !== null) {
        requireAuth();
        $stmt = $db->prepare('SELECT * FROM skills WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            jsonResponse(['error' => 'ไม่พบทักษะนี้'], 404);
        }

        $body = getJsonBody();
        $name = array_key_exists('name', $body) ? bodyStr($body, 'name') : $existing['name'];
        $category = array_key_exists('category', $body) ? bodyStr($body, 'category') : $existing['category'];
        $icon = array_key_exists('icon', $body) ? bodyStr($body, 'icon') : $existing['icon'];

        $upd = $db->prepare('UPDATE skills SET name = ?, category = ?, icon = ? WHERE id = ?');
        $upd->execute([$name, $category, $icon, $id]);

        jsonResponse(['id' => $id, 'name' => $name, 'category' => $category, 'icon' => $icon]);
    }

    if ($method === 'DELETE' && $id !== null) {
        requireAuth();
        $stmt = $db->prepare('DELETE FROM skills WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            jsonResponse(['error' => 'ไม่พบทักษะนี้'], 404);
        }
        jsonResponse(['ok' => true]);
    }

    jsonResponse(['error' => 'Method not allowed'], 405);
}

// ---------- Products (ร้านค้า) ----------

function fetchAllProducts(PDO $db): array
{
    $rows = $db->query('SELECT id, title, description, image, price, download_link AS downloadLink FROM products ORDER BY sort_order ASC, id ASC')->fetchAll();
    foreach ($rows as &$row) {
        $row['price'] = (float) $row['price'];
    }
    return $rows;
}

function handleProducts(string $method, ?string $id): void
{
    $db = getDb();

    if ($method === 'GET' && $id === null) {
        jsonResponse(fetchAllProducts($db));
    }

    if ($method === 'POST' && $id === null) {
        requireAuth();
        $body = getJsonBody();
        $title = bodyStr($body, 'title');
        $description = bodyStr($body, 'description');

        if ($title === '' || $description === '') {
            jsonResponse(['error' => 'กรุณากรอกชื่อและคำอธิบายสินค้า'], 400);
        }

        $newId = genId('prod');
        $image = bodyStr($body, 'image');
        $downloadLink = bodyStr($body, 'downloadLink', '#');
        $price = isset($body['price']) ? (float) $body['price'] : 0;
        if ($price < 0) {
            jsonResponse(['error' => 'ราคาต้องไม่ติดลบ'], 400);
        }

        $stmt = $db->prepare('INSERT INTO products (id, title, description, image, price, download_link, sort_order) VALUES (?, ?, ?, ?, ?, ?, (SELECT n FROM (SELECT COALESCE(MAX(sort_order), 0) + 1 AS n FROM products) t))');
        $stmt->execute([$newId, $title, $description, $image, $price, $downloadLink]);

        jsonResponse(['id' => $newId, 'title' => $title, 'description' => $description, 'image' => $image, 'price' => $price, 'downloadLink' => $downloadLink], 201);
    }

    if ($method === 'PUT' && $id !== null) {
        requireAuth();
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            jsonResponse(['error' => 'ไม่พบสินค้านี้'], 404);
        }

        $body = getJsonBody();
        $title = array_key_exists('title', $body) ? bodyStr($body, 'title') : $existing['title'];
        $description = array_key_exists('description', $body) ? bodyStr($body, 'description') : $existing['description'];
        $image = array_key_exists('image', $body) ? bodyStr($body, 'image') : $existing['image'];
        $downloadLink = array_key_exists('downloadLink', $body) ? bodyStr($body, 'downloadLink') : $existing['download_link'];
        $price = array_key_exists('price', $body) ? (float) $body['price'] : (float) $existing['price'];
        if ($price < 0) {
            jsonResponse(['error' => 'ราคาต้องไม่ติดลบ'], 400);
        }

        $upd = $db->prepare('UPDATE products SET title = ?, description = ?, image = ?, price = ?, download_link = ? WHERE id = ?');
        $upd->execute([$title, $description, $image, $price, $downloadLink, $id]);

        jsonResponse(['id' => $id, 'title' => $title, 'description' => $description, 'image' => $image, 'price' => $price, 'downloadLink' => $downloadLink]);
    }

    if ($method === 'DELETE' && $id !== null) {
        requireAuth();
        $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            jsonResponse(['error' => 'ไม่พบสินค้านี้'], 404);
        }
        jsonResponse(['ok' => true]);
    }

    jsonResponse(['error' => 'Method not allowed'], 405);
}

// ---------- Contact ----------

function handleContactSubmit(): void
{
    $body = getJsonBody();
    $name = bodyStr($body, 'name');
    $email = bodyStr($body, 'email');
    $message = bodyStr($body, 'message');

    if ($name === '' || $email === '' || $message === '') {
        jsonResponse(['error' => 'กรุณากรอกข้อมูลให้ครบถ้วน'], 400);
    }

    $db = getDb();
    $stmt = $db->prepare('INSERT INTO messages (id, name, email, message, received_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([genId('msg'), $name, $email, $message]);

    jsonResponse(['ok' => true]);
}
