-- ============================================================
--  Migration: เพิ่มตารางร้านค้า (products) ให้ฐานข้อมูลที่มีอยู่แล้ว
--  ใช้ไฟล์นี้แทน schema.sql ถ้าคุณตั้งค่าฐานข้อมูลไปแล้วก่อนหน้านี้
--  นำเข้าผ่าน phpMyAdmin → เลือกฐานข้อมูลของคุณ → แท็บ Import
-- ============================================================

SET NAMES utf8mb4;

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
