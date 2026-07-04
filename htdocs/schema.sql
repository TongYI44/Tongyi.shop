-- ============================================================
--  TongYI - โครงสร้างฐานข้อมูล MySQL
--  นำเข้าไฟล์นี้ผ่าน phpMyAdmin (เมนู Import) บนโฮสต์ cPanel
--  หรือรันผ่าน: mysql -u user -p dbname < schema.sql
-- ============================================================

SET NAMES utf8mb4;

-- ---------- ตารางผู้ดูแลระบบ (แอดมิน) ----------
CREATE TABLE IF NOT EXISTS admin_users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- ตารางผลงาน (Projects) ----------
CREATE TABLE IF NOT EXISTS projects (
  id          VARCHAR(50) PRIMARY KEY,
  title       VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  image       VARCHAR(500) DEFAULT '',
  tags        JSON NULL,
  link        VARCHAR(500) DEFAULT '#',
  sort_order  INT NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- ตารางทักษะ (Skills) ----------
CREATE TABLE IF NOT EXISTS skills (
  id          VARCHAR(50) PRIMARY KEY,
  name        VARCHAR(255) NOT NULL,
  category    VARCHAR(100) DEFAULT 'อื่นๆ',
  icon        VARCHAR(100) DEFAULT 'code',
  sort_order  INT NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- ตารางข้อความติดต่อ (Contact form) ----------
CREATE TABLE IF NOT EXISTS messages (
  id          VARCHAR(50) PRIMARY KEY,
  name        VARCHAR(255) NOT NULL,
  email       VARCHAR(255) NOT NULL,
  message     TEXT NOT NULL,
  received_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- ตารางร้านค้า (Products / Shop) ----------
CREATE TABLE IF NOT EXISTS products (
  id            VARCHAR(50) PRIMARY KEY,
  title         VARCHAR(255) NOT NULL,
  description   TEXT NOT NULL,
  image         VARCHAR(500) DEFAULT '',
  price         DECIMAL(10,2) NOT NULL DEFAULT 0,
  download_link VARCHAR(500) DEFAULT '#',
  sort_order    INT NOT NULL DEFAULT 0,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  ข้อมูลตั้งต้น (เหมือนของเดิมใน data/content.json)
-- ============================================================

INSERT INTO projects (id, title, description, image, tags, link, sort_order) VALUES
('proj-1', 'เครื่องคิดเลข', 'แอปพลิเคชันเครื่องคิดเลขสมัยใหม่ ตอบสนองได้ดี สร้างด้วย HTML, CSS และ JavaScript', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500&h=500&fit=crop', JSON_ARRAY('HTML', 'CSS', 'JavaScript'), '#', 1),
('proj-2', 'แอปพยากรณ์อากาศ', 'แอปพยากรณ์อากาศแบบเรียลไทม์ อิงตามตำแหน่งที่ตั้ง พร้อมดีไซน์ที่สวยงาม', 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=500&h=500&fit=crop', JSON_ARRAY('React', 'API', 'Tailwind'), '#', 2),
('proj-3', 'ตัวจัดการงาน (Todo)', 'แอปพลิเคชันจัดการงาน พร้อมระบบจัดเก็บข้อมูลในเครื่องและอินเทอร์เฟซที่ใช้งานง่าย', 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=500&h=500&fit=crop', JSON_ARRAY('React', 'JavaScript', 'CSS'), '#', 3)
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO skills (id, name, category, icon, sort_order) VALUES
('skill-1', 'HTML5', 'Frontend', 'code', 1),
('skill-2', 'CSS3', 'Frontend', 'palette', 2),
('skill-3', 'JavaScript', 'Frontend', 'zap', 3),
('skill-4', 'React', 'Frontend', 'code', 4),
('skill-5', 'Next.js', 'Frontend', 'code', 5),
('skill-6', 'Tailwind CSS', 'Frontend', 'palette', 6),
('skill-7', 'TypeScript', 'ภาษาโปรแกรม', 'code', 7),
('skill-8', 'Node.js', 'Backend', 'database', 8),
('skill-9', 'Python', 'ภาษาโปรแกรม', 'code', 9),
('skill-10', 'Figma', 'ดีไซน์', 'palette', 10),
('skill-11', 'WordPress', 'CMS', 'code', 11),
('skill-12', 'Git', 'เครื่องมือ', 'zap', 12)
ON DUPLICATE KEY UPDATE id = id;

-- หมายเหตุ: ตาราง admin_users ไม่ได้ seed ไว้ในไฟล์นี้
-- ให้รัน api/install.php ผ่านเบราว์เซอร์ 1 ครั้งหลังตั้งค่า config.php
-- เพื่อสร้างบัญชีแอดมินพร้อม hash รหัสผ่านที่ปลอดภัย
