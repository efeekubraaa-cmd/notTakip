-- Not Takip Sistemi Veritabanı
CREATE DATABASE IF NOT EXISTS nottakip;
USE nottakip;

-- Admin Tablosu
CREATE TABLE IF NOT EXISTS admin (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Öğrenci Tablosu
CREATE TABLE IF NOT EXISTS ogrenciler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  no INT UNIQUE NOT NULL,
  isim VARCHAR(50) NOT NULL,
  soyisim VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  kullanici_adi VARCHAR(50) UNIQUE NOT NULL,
  sifre VARCHAR(255) NOT NULL,
  sinif VARCHAR(20),
  tel VARCHAR(20),
  durum ENUM('aktif', 'pasif') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dersler Tablosu
CREATE TABLE IF NOT EXISTS dersler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad VARCHAR(100) NOT NULL,
  kod VARCHAR(20) UNIQUE NOT NULL,
  kredi INT,
  ogun INT,
  durum ENUM('aktif', 'pasif') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Notlar Tablosu
CREATE TABLE IF NOT EXISTS notlar (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ogrenci_id INT NOT NULL,
  ders_id INT NOT NULL,
  vize DECIMAL(5, 2),
  final DECIMAL(5, 2),
  proje DECIMAL(5, 2),
  ortalama DECIMAL(5, 2),
  harf_notu VARCHAR(2),
  devamsizlik INT DEFAULT 0,
  tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (ogrenci_id) REFERENCES ogrenciler(id) ON DELETE CASCADE,
  FOREIGN KEY (ders_id) REFERENCES dersler(id) ON DELETE CASCADE,
  UNIQUE KEY unique_og_ders (ogrenci_id, ders_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Öğrenci-Ders İlişkisi
CREATE TABLE IF NOT EXISTS ogrenci_dersler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ogrenci_id INT NOT NULL,
  ders_id INT NOT NULL,
  semester VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ogrenci_id) REFERENCES ogrenciler(id) ON DELETE CASCADE,
  FOREIGN KEY (ders_id) REFERENCES dersler(id) ON DELETE CASCADE,
  UNIQUE KEY unique_og_ders_kayit (ogrenci_id, ders_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Default Admin Kullanıcısı (admin / admin123)
INSERT IGNORE INTO admin (username, password, email) 
VALUES ('admin', SHA2('admin123', 256), 'admin@nottakip.com');