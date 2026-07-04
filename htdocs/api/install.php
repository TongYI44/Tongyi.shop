<?php
/**
 * ============================================================
 *  install.php — รันครั้งเดียวหลังตั้งค่า config.php + import schema.sql
 *  เปิดผ่านเบราว์เซอร์: https://yourdomain.com/api/install.php
 *  ทำหน้าที่สร้างบัญชีแอดมิน (username/password จาก config.php)
 *  โดย hash รหัสผ่านด้วย bcrypt ก่อนเก็บลงฐานข้อมูล
 *
 *  ⚠️ สำคัญ: ลบไฟล์นี้ทิ้ง (หรือเปลี่ยนชื่อ) หลังติดตั้งเสร็จ
 *     เพื่อไม่ให้ใครมารันซ้ำ/รีเซ็ตรหัสผ่านแอดมินได้
 * ============================================================
 */

require_once __DIR__ . '/db.php';

header('Content-Type: text/html; charset=utf-8');

$db = getDb();

$stmt = $db->prepare('SELECT id FROM admin_users WHERE username = ?');
$stmt->execute([ADMIN_USERNAME]);
$exists = $stmt->fetch();

if ($exists) {
    echo "<p>✅ มีบัญชีแอดมิน <b>" . htmlspecialchars(ADMIN_USERNAME) . "</b> อยู่แล้ว ไม่ต้องสร้างซ้ำ</p>";
    echo "<p>ถ้าต้องการเปลี่ยนรหัสผ่าน ให้แก้ค่า ADMIN_PASSWORD ใน config.php แล้วลบบัญชีเดิมออกจากตาราง admin_users ก่อนรันไฟล์นี้ใหม่</p>";
} else {
    $hash = password_hash(ADMIN_PASSWORD, PASSWORD_BCRYPT);
    $ins = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
    $ins->execute([ADMIN_USERNAME, $hash]);

    echo "<p>✅ สร้างบัญชีแอดมินสำเร็จ!</p>";
    echo "<p>ชื่อผู้ใช้: <b>" . htmlspecialchars(ADMIN_USERNAME) . "</b></p>";
    echo "<p>ไปเข้าสู่ระบบได้ที่ <a href='/admin/'>/admin/</a></p>";
}

echo "<hr><p style='color:red'><b>⚠️ อย่าลืมลบไฟล์ api/install.php ออกจากเซิร์ฟเวอร์ตอนนี้เลย เพื่อความปลอดภัย</b></p>";
