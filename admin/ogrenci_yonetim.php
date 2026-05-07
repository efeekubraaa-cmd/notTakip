<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $no = intval($_POST['no'] ?? 0);
        $isim = trim($_POST['isim'] ?? '');
        $soyisim = trim($_POST['soyisim'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
        $sifre = trim($_POST['sifre'] ?? '');
        $sinif = trim($_POST['sinif'] ?? '');
        
        if (empty($no) || empty($isim) || empty($soyisim) || empty($email) || empty($kullanici_adi) || empty($sifre)) {
            $error = 'Tüm alanlar zorunludur!';
        } else {
            $stmt = $conn->prepare("INSERT INTO ogrenciler (no, isim, soyisim, email, kullanici_adi, sifre, sinif) VALUES (?, ?, ?, ?, ?, SHA2(?, 256), ?)");
            $stmt->bind_param("issssss", $no, $isim, $soyisim, $email, $kullanici_adi, $sifre, $sinif);
            
            if ($stmt->execute()) {
                $message = 'Öğrenci başarıyla eklendi!';
                $action = 'list';
            } else {
                $error = 'Öğrenci eklenirken hata oluştu!';
            }
            $stmt->close();
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id']);
        $isim = trim($_POST['isim'] ?? '');
        $soyisim = trim($_POST['soyisim'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sinif = trim($_POST['sinif'] ?? '');
        $durum = trim($_POST['durum'] ?? 'aktif');
        
        if (empty($isim) || empty($soyisim) || empty($email)) {
            $error = 'Zorunlu alanları doldurunuz!';
        } else {
            $stmt = $conn->prepare("UPDATE ogrenciler SET isim = ?, soyisim = ?, email = ?, sinif = ?, durum = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $isim, $soyisim, $email, $sinif, $durum, $id);
            
            if ($stmt->execute()) {
                $message = 'Öğrenci başarıyla güncellendi!';
                $action = 'list';
            } else {
                $error = 'Güncelleme sırasında hata oluştu!';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        $stmt = $conn->prepare("DELETE FROM ogrenciler WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Öğrenci başarıyla silindi!';
            $action = 'list';
        } else {
            $error = 'Silme sırasında hata oluştu!';
        }
        $stmt->close();
    }
}

// Get student list
if ($action === 'list') {
    $result = $conn->query("SELECT * FROM ogrenciler ORDER BY isim ASC");
    $ogrenciler = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} elseif ($action === 'edit') {
    $id = intval($_GET['id'] ?? 0);
    $result = $conn->query("SELECT * FROM ogrenciler WHERE id = $id");
    $ogrenci = $result ? $result->fetch_assoc() : null;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Yönetimi - Not Takip Sistemi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar h2 {
            margin-bottom: 30px;
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
            flex: 1;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #764ba2;
        }
        
        .btn-success {
            background: #51cf66;
        }
        
        .btn-success:hover {
            background: #40c057;
        }
        
        .btn-danger {
            background: #f5576c;
        }
        
        .btn-danger:hover {
            background: #e63946;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin-bottom: 20px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .action-btns {
            display: flex;
            gap: 5px;
        }
        
        .action-btns a,
        .action-btns button {
            padding: 6px 12px;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .edit-btn {
            background: #51cf66;
            color: white;
        }
        
        .delete-btn {
            background: #f5576c;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>📋 Not Takip</h2>
            <nav>
                <a href="dashboard.php">Ana Sayfa</a>
                <a href="ogrenci_yonetim.php">Öğrenci Yönetimi</a>
                <a href="ders_yonetim.php">Ders Yönetimi</a>
                <a href="not_giris.php">Not Giriş</a>
                <a href="logout.php">Çıkış Yap</a>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Öğrenci Yönetimi</h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=add" class="btn">+ Yeni Öğrenci Ekle</a>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($action === 'add'): ?>
                <div class="form-card">
                    <h2>Yeni Öğrenci Ekle</h2>
                    <form method="POST" action="?action=add">
                        <div class="form-group">
                            <label for="no">Öğrenci No *</label>
                            <input type="number" id="no" name="no" required>
                        </div>
                        <div class="form-group">
                            <label for="isim">Ad *</label>
                            <input type="text" id="isim" name="isim" required>
                        </div>
                        <div class="form-group">
                            <label for="soyisim">Soyad *</label>
                            <input type="text" id="soyisim" name="soyisim" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="kullanici_adi">Kullanıcı Adı *</label>
                            <input type="text" id="kullanici_adi" name="kullanici_adi" required>
                        </div>
                        <div class="form-group">
                            <label for="sifre">Şifre *</label>
                            <input type="password" id="sifre" name="sifre" required>
                        </div>
                        <div class="form-group">
                            <label for="sinif">Sınıf</label>
                            <input type="text" id="sinif" name="sinif" placeholder="örn: 1-A">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Ekle</button>
                            <a href="?action=list" class="btn">İptal</a>
                        </div>
                    </form>
                </div>
            <?php elseif ($action === 'edit' && $ogrenci): ?>
                <div class="form-card">
                    <h2>Öğrenci Güncelle</h2>
                    <form method="POST" action="?action=update">
                        <input type="hidden" name="id" value="<?php echo $ogrenci['id']; ?>">
                        <div class="form-group">
                            <label for="isim">Ad *</label>
                            <input type="text" id="isim" name="isim" value="<?php echo htmlspecialchars($ogrenci['isim']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="soyisim">Soyad *</label>
                            <input type="text" id="soyisim" name="soyisim" value="<?php echo htmlspecialchars($ogrenci['soyisim']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($ogrenci['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="sinif">Sınıf</label>
                            <input type="text" id="sinif" name="sinif" value="<?php echo htmlspecialchars($ogrenci['sinif'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="durum">Durum</label>
                            <select id="durum" name="durum">
                                <option value="aktif" <?php echo $ogrenci['durum'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="pasif" <?php echo $ogrenci['durum'] === 'pasif' ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Güncelle</button>
                            <a href="?action=list" class="btn">İptal</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Ad Soyadı</th>
                            <th>Email</th>
                            <th>Sınıf</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ogrenciler as $og): ?>
                            <tr>
                                <td><?php echo $og['no']; ?></td>
                                <td><?php echo htmlspecialchars($og['isim'] . ' ' . $og['soyisim']); ?></td>
                                <td><?php echo htmlspecialchars($og['email']); ?></td>
                                <td><?php echo htmlspecialchars($og['sinif'] ?? '-'); ?></td>
                                <td><?php echo $og['durum'] === 'aktif' ? '✓ Aktif' : '✗ Pasif'; ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="?action=edit&id=<?php echo $og['id']; ?>" class="edit-btn">Düzenle</a>
                                        <form method="POST" action="?action=delete" style="display: inline;" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                            <input type="hidden" name="id" value="<?php echo $og['id']; ?>">
                                            <button type="submit" class="delete-btn">Sil</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>