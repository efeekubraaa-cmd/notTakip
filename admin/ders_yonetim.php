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
        $ad = trim($_POST['ad'] ?? '');
        $kod = trim($_POST['kod'] ?? '');
        $kredi = intval($_POST['kredi'] ?? 0);
        $ogun = intval($_POST['ogun'] ?? 0);
        
        if (empty($ad) || empty($kod)) {
            $error = 'Ders adı ve kodu zorunludur!';
        } else {
            $stmt = $conn->prepare("INSERT INTO dersler (ad, kod, kredi, ogun) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $ad, $kod, $kredi, $ogun);
            
            if ($stmt->execute()) {
                $message = 'Ders başarıyla eklendi!';
                $action = 'list';
            } else {
                $error = 'Ders eklenirken hata oluştu!';
            }
            $stmt->close();
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id']);
        $ad = trim($_POST['ad'] ?? '');
        $kod = trim($_POST['kod'] ?? '');
        $kredi = intval($_POST['kredi'] ?? 0);
        $ogun = intval($_POST['ogun'] ?? 0);
        $durum = trim($_POST['durum'] ?? 'aktif');
        
        if (empty($ad) || empty($kod)) {
            $error = 'Ders adı ve kodu boş bırakılamaz!';
        } else {
            $stmt = $conn->prepare("UPDATE dersler SET ad = ?, kod = ?, kredi = ?, ogun = ?, durum = ? WHERE id = ?");
            $stmt->bind_param("ssiisi", $ad, $kod, $kredi, $ogun, $durum, $id);
            
            if ($stmt->execute()) {
                $message = 'Ders başarıyla güncellendi!';
                $action = 'list';
            } else {
                $error = 'Güncelleme sırasında hata oluştu!';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        $stmt = $conn->prepare("DELETE FROM dersler WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Ders başarıyla silindi!';
            $action = 'list';
        } else {
            $error = 'Silme sırasında hata oluştu!';
        }
        $stmt->close();
    }
}

// Get course list
if ($action === 'list') {
    $result = $conn->query("SELECT * FROM dersler ORDER BY ad ASC");
    $dersler = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} elseif ($action === 'edit') {
    $id = intval($_GET['id'] ?? 0);
    $result = $conn->query("SELECT * FROM dersler WHERE id = $id");
    $ders = $result ? $result->fetch_assoc() : null;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ders Yönetimi - Not Takip Sistemi</title>
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
                <h1>Ders Yönetimi</h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=add" class="btn">+ Yeni Ders Ekle</a>
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
                    <h2>Yeni Ders Ekle</h2>
                    <form method="POST" action="?action=add">
                        <div class="form-group">
                            <label for="ad">Ders Adı *</label>
                            <input type="text" id="ad" name="ad" required>
                        </div>
                        <div class="form-group">
                            <label for="kod">Ders Kodu *</label>
                            <input type="text" id="kod" name="kod" required>
                        </div>
                        <div class="form-group">
                            <label for="kredi">Kredi</label>
                            <input type="number" id="kredi" name="kredi" min="0">
                        </div>
                        <div class="form-group">
                            <label for="ogun">Ögün (Ders Saati)</label>
                            <input type="number" id="ogun" name="ogun" min="0">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Ekle</button>
                            <a href="?action=list" class="btn">İptal</a>
                        </div>
                    </form>
                </div>
            <?php elseif ($action === 'edit' && $ders): ?>
                <div class="form-card">
                    <h2>Ders Güncelle</h2>
                    <form method="POST" action="?action=update">
                        <input type="hidden" name="id" value="<?php echo $ders['id']; ?>">
                        <div class="form-group">
                            <label for="ad">Ders Adı *</label>
                            <input type="text" id="ad" name="ad" value="<?php echo htmlspecialchars($ders['ad']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="kod">Ders Kodu *</label>
                            <input type="text" id="kod" name="kod" value="<?php echo htmlspecialchars($ders['kod']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="kredi">Kredi</label>
                            <input type="number" id="kredi" name="kredi" value="<?php echo $ders['kredi']; ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label for="ogun">Ögün (Ders Saati)</label>
                            <input type="number" id="ogun" name="ogun" value="<?php echo $ders['ogun']; ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label for="durum">Durum</label>
                            <select id="durum" name="durum">
                                <option value="aktif" <?php echo $ders['durum'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="pasif" <?php echo $ders['durum'] === 'pasif' ? 'selected' : ''; ?>>Pasif</option>
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
                            <th>Ders Adı</th>
                            <th>Kod</th>
                            <th>Kredi</th>
                            <th>Ögün</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dersler as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d['ad']); ?></td>
                                <td><?php echo htmlspecialchars($d['kod']); ?></td>
                                <td><?php echo $d['kredi'] ?? '-'; ?></td>
                                <td><?php echo $d['ogun'] ?? '-'; ?></td>
                                <td><?php echo $d['durum'] === 'aktif' ? '✓ Aktif' : '✗ Pasif'; ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="?action=edit&id=<?php echo $d['id']; ?>" class="edit-btn">Düzenle</a>
                                        <form method="POST" action="?action=delete" style="display: inline;" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                            <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
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