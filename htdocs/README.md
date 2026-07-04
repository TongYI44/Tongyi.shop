# TongYI — เวอร์ชัน PHP + MySQL

แปลงจากเวอร์ชัน Node/Express เดิม ให้เป็น PHP + MySQL สำหรับอัปโหลดขึ้นโฮสต์แบบ cPanel ได้ตรงๆ
**หน้าเว็บ (public) และหน้าแอดมิน (admin) เหมือนเดิมทุกอย่าง ไม่ต้องแก้โค้ด** — เปลี่ยนแค่หลังบ้านเป็น PHP

## โครงสร้างไฟล์

```
/                     ← อัปโหลดทั้งโฟลเดอร์นี้ขึ้น public_html
├── index.html        ← หน้าเว็บหลัก
├── css/, js/, videos/
├── admin/            ← หน้าแอดมิน (/admin)
├── api/              ← โค้ด PHP หลังบ้านทั้งหมด (/api/...)
│   ├── index.php     ← จุดรวมเส้นทาง API (router)
│   ├── db.php         ← เชื่อมต่อ MySQL ผ่าน PDO
│   ├── helpers.php    ← ฟังก์ชันช่วยเหลือ
│   ├── install.php    ← รันครั้งเดียวเพื่อสร้างบัญชีแอดมิน
│   └── .htaccess      ← ตั้งค่า rewrite ให้ /api/... เข้า index.php
├── config.php         ← ตั้งค่าฐานข้อมูล + บัญชีแอดมินตั้งต้น
└── schema.sql         ← โครงสร้างตาราง MySQL + ข้อมูลตั้งต้น
```

## ขั้นตอนติดตั้งบนโฮสต์ cPanel

### 1) สร้างฐานข้อมูล MySQL
ใน cPanel → **MySQL® Databases**
- สร้างฐานข้อมูลใหม่ เช่น `tongyi` (cPanel จะเติม prefix ให้เป็น `username_tongyi`)
- สร้าง MySQL user + รหัสผ่าน แล้ว "Add User to Database" พร้อมสิทธิ์ **ALL PRIVILEGES**

### 2) นำเข้าโครงสร้างตาราง
ใน cPanel → **phpMyAdmin** → เลือกฐานข้อมูลที่สร้างไว้ → แท็บ **Import** → เลือกไฟล์ `schema.sql` → กด Go
(ไฟล์นี้จะสร้างตาราง `admin_users`, `projects`, `skills`, `messages` พร้อม seed ข้อมูลผลงาน/ทักษะตัวอย่างให้)

### 3) แก้ไข `config.php`
เปิดไฟล์ `config.php` แก้ค่าต่อไปนี้ให้ตรงกับที่สร้างในข้อ 1:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'username_tongyi');
define('DB_USER', 'username_tongyi');
define('DB_PASS', 'รหัสผ่านฐานข้อมูล');

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'ตั้งรหัสผ่านแอดมินใหม่ตรงนี้');
```

### 4) อัปโหลดไฟล์ทั้งหมด
อัปโหลดทุกไฟล์/โฟลเดอร์ในนี้ขึ้น `public_html` (หรือ subdomain ที่ต้องการ) ผ่าน File Manager หรือ FTP
- ต้องใช้ PHP **7.4 ขึ้นไป** (แนะนำ PHP 8.x) — เช็ค/ตั้งค่าได้ที่เมนู **MultiPHP Manager**
- ต้องเปิดใช้ extension `pdo_mysql` (โฮสต์ cPanel ส่วนใหญ่เปิดให้อยู่แล้ว)

### 5) สร้างบัญชีแอดมิน (รันครั้งเดียว)
เปิดเบราว์เซอร์ไปที่:
```
https://yourdomain.com/api/install.php
```
จะสร้างบัญชีแอดมินตาม `ADMIN_USERNAME` / `ADMIN_PASSWORD` ที่ตั้งไว้ในขั้นตอนที่ 3 (รหัสผ่านจะถูก hash ก่อนเก็บ ปลอดภัย)

**⚠️ สำคัญ: หลังรันเสร็จ ให้ลบไฟล์ `api/install.php` ทิ้งทันที** เพื่อไม่ให้ใครมารันซ้ำได้

### 6) ทดสอบใช้งาน
- หน้าเว็บหลัก: `https://yourdomain.com/`
- หน้าแอดมิน: `https://yourdomain.com/admin/` (login ด้วยบัญชีที่สร้างในข้อ 5)

## หมายเหตุด้านความปลอดภัย
- ถ้าเว็บใช้ HTTPS (แนะนำอย่างยิ่ง) ให้เปิดบรรทัด `'secure' => true` ใน `api/helpers.php` ฟังก์ชัน `startAppSession()`
- ไฟล์ `config.php` และ `schema.sql` ถูกกันไม่ให้เข้าถึงผ่านเบราว์เซอร์โดยตรงแล้ว (ผ่าน `.htaccess`)
- แนะนำเปลี่ยนรหัสผ่านแอดมินเริ่มต้นก่อนเปิดใช้งานจริงเสมอ

## API endpoints (เหมือนเดิมทุกเส้นทาง)

| Method | Path                  | ต้อง login |
|--------|-----------------------|:---------:|
| POST   | /api/auth/login       | ❌ |
| POST   | /api/auth/logout      | ❌ |
| GET    | /api/auth/status      | ❌ |
| GET    | /api/content          | ❌ |
| GET    | /api/projects         | ❌ |
| POST   | /api/projects         | ✅ |
| PUT    | /api/projects/:id     | ✅ |
| DELETE | /api/projects/:id     | ✅ |
| GET    | /api/skills           | ❌ |
| POST   | /api/skills           | ✅ |
| PUT    | /api/skills/:id       | ✅ |
| DELETE | /api/skills/:id       | ✅ |
| POST   | /api/contact          | ❌ |
| GET    | /api/messages         | ✅ |

## ถ้าต้องการรันทดสอบบนเครื่อง local
ใช้ PHP built-in server (ต้องมี MySQL รันอยู่ในเครื่องด้วย):
```bash
php -S localhost:8000
```
แล้วเข้า `http://localhost:8000/`
