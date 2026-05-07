# 📚 Not Takip Sistemi

XAMPP ve PHP tabanlı kapsamlı bir not takip ve yönetim sistemi.

## 🎯 Özellikler

### Admin Paneli
- ✅ Admin giriş sistemi (SHA2 şifreleme)
- ✅ Öğrenci yönetimi (CRUD)
- ✅ Ders yönetimi (CRUD)
- ✅ **Admin Not Girişi** (Vize, Final, Proje)
- ✅ Otomatik ortalama hesaplama
- ✅ Harf notu atanması (AA-FF)
- ✅ Dashboard ve istatistikler

### Öğrenci Paneli
- ✅ Öğrenci giriş sistemi
- ✅ Notları görüntüleme
- ✅ Akademik bilgiler
- ✅ Başarı durumu takibi

## 🔐 Güvenlik
- SHA2(256) şifre şifrelemesi
- Prepared Statements (SQL Injection koruması)
- Session tabanlı yetkilendirme
- Admin/Öğrenci ayrımı

## 📊 Veritabanı Tabloları
- admin
- ogrenciler
- dersler
- notlar
- ogrenci_dersler

## 🚀 Kurulum

1. XAMPP'i başlatın (Apache + MySQL)
2. `database.sql` dosyasını phpMyAdmin'de çalıştırın
3. Tüm dosyaları `htdocs/notTakip/` klasörüne kopyalayın
4. `http://localhost/notTakip/` adresine gidin

## 🔑 Test Hesapları

**Admin:**
- Kullanıcı: `admin`
- Şifre: `admin123`

## 📁 Dosya Yapısı
```
notTakip/
├── config.php
├── database.sql
├── admin_login.php
├── ogrenci_login.php
├── admin/
│   ├── dashboard.php
│   ├── not_giris.php
│   ├── ogrenci_yonetim.php
│   ├── ders_yonetim.php
│   └── logout.php
└── ogrenci/
    ├── dashboard.php
    └── logout.php
```

## 💻 Teknolojiler
- PHP 7.0+
- MySQL
- HTML5
- CSS3
- JavaScript

## 📝 Lisans
MIT
