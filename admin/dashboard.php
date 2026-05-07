<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// İstatistikler
$toplam_ogrenci = $conn->query("SELECT COUNT(*) as count FROM ogrenciler WHERE durum = 'aktif'")->fetch_assoc()['count'];
$toplam_ders = $conn->query("SELECT COUNT(*) as count FROM dersler WHERE durum = 'aktif'")->fetch_assoc()['count'];
$toplam_not = $conn->query("SELECT COUNT(*) as count FROM notlar")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Not Takip Sistemi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar h2 {
            margin-bottom: 30px;
            font-size: 20px;
        }
        
        .sidebar nav a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            color: #333;
        }
        
        .logout-btn {
            background: #f5576c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #e63946;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid #667eea;
        }
        
        .stat-card h3 {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .stat-card .number {
            font-size: 2.5em;
            color: #667eea;
            font-weight: 600;
        }
        
        .menu-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .menu-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        .menu-card h3 {
            margin-bottom: 10px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>📚 Not Takip</h2>
        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="ogrenci_yonetim.php">👥 Öğrenci Yönetimi</a>
            <a href="ders_yonetim.php">📖 Ders Yönetimi</a>
            <a href="not_giris.php">✏️ Not Giriş</a>
            <a href="logout.php">🚪 Çıkış</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Toplam Öğrenci</h3>
                <div class="number"><?php echo $toplam_ogrenci; ?></div>
            </div>
            <div class="stat-card">
                <h3>Toplam Ders</h3>
                <div class="number"><?php echo $toplam_ders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Not Giriş Yapılanlar</h3>
                <div class="number"><?php echo $toplam_not; ?></div>
            </div>
        </div>
        
        <h2 style="color: white; margin-bottom: 20px;">Hızlı Erişim</h2>
        <div class="menu-cards">
            <a href="ogrenci_yonetim.php" class="menu-card">
                <h3>👥 Öğrenci Yönetimi</h3>
                <p>Öğrenci ekleyin, düzenleyin ve silin</p>
            </a>
            <a href="ders_yonetim.php" class="menu-card">
                <h3>📖 Ders Yönetimi</h3>
                <p>Dersleri yönetin ve güncelleyin</p>
            </a>
            <a href="not_giris.php" class="menu-card">
                <h3>✏️ Not Giriş</h3>
                <p>Öğrenci notlarını girin</p>
            </a>
        </div>
    </div>
</body>
</html>