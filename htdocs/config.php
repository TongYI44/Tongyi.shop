<?php
/**
 * ============================================================
 *  TongYI - Config หลักของระบบ (PHP + MySQL)
 * ============================================================
 *  แก้ค่าด้านล่างให้ตรงกับฐานข้อมูล MySQL บนโฮสต์ของคุณ
 *  (โฮสต์แบบ cPanel ส่วนใหญ่: สร้าง DB + user ผ่านเมนู MySQL Databases
 *   แล้วชื่อ DB/user มักจะถูก cPanel เติม prefix เช่น username_ ให้อัตโนมัติ)
 * ============================================================
 */

// ---------- ตั้งค่าฐานข้อมูล ----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'pp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---------- บัญชีแอดมิน (ใช้ตอน seed ฐานข้อมูลครั้งแรกเท่านั้น) ----------
// หลัง seed แล้ว รหัสผ่านจะถูกเก็บเป็น hash ในตาราง admin_users
// เปลี่ยนได้ภายหลังโดยตรงในฐานข้อมูล หรือเขียนหน้าจัดการเพิ่มเอง
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '090809'); // เปลี่ยนก่อนใช้งานจริง!

// ---------- ตั้งค่า session cookie ----------
define('SESSION_NAME', 'tongyi_session');
define('SESSION_LIFETIME', 60 * 60 * 24); // 24 ชั่วโมง (วินาที)

// ---------- โซนเวลา ----------
date_default_timezone_set('Asia/Bangkok');

// ---------- โหมด debug (ปิดตอนขึ้นเซิร์ฟเวอร์จริง) ----------
define('APP_DEBUG', false);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
