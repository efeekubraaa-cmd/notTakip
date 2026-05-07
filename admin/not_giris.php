<?php
session_start();
include '../config.php';

// Admin kontrol
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

$message = '';
$error = '';

// Öğrencileri ve derslerini getir
$ogrenciler = $conn->query("SELECT * FROM ogrenciler WHERE durum = 'aktif' ORDER BY isim");
$dersler = $conn->query("SELECT * FROM dersler ORDER BY ad");

// Not güncelleme
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ogrenci_id = $_POST['ogrenci_id'] ?? '';
    $ders_id = $_POST['ders_id'] ?? '';
    $vize = $_POST['vize'] ?? '';
    $final = $_POST['final'] ?? '';
    $proje = $_POST['proje'] ?? '';
    $devamsizlik = $_POST['devamsizlik'] ?? 0;

    if ($ogrenci_id && $ders_id) {
        // Veri doğrulama
        $errors = [];
        
        if ($vize !== '' && ($vize < 0 || $vize > 100)) {
            $errors[] = "Vize notu 0-100 arasında olmalıdır.";
        }
        if ($final !== '' && ($final < 0 || $final > 100)) {
            $errors[] = "Final notu 0-100 arasında olmalıdır.";
        }
        if ($proje !== '' && ($proje < 0 || $proje > 100)) {
            $errors[] = "Proje notu 0-100 arasında olmalıdır.";
        }
        if ($devamsizlik < 0) {
            $errors[] = "Devamsızlık sayısı negatif olamaz.";
        }

        if (empty($errors)) {
            // Ortalama hesapla
            $ort = 0;
            $sayac = 0;
            
            if ($vize !== '') {
                $ort += $vize * 0.3;
                $sayac++;
            }
            if ($final !== '') {
                $ort += $final * 0.5;
                $sayac++;
            }
            if ($proje !== '') {
                $ort += $proje * 0.2;
                $sayac++;
            }

            if ($sayac > 0) {
                $ort = $ort / $sayac;
            }

            // Harf notu atama
            if ($ort >= 90) $harf = 'AA';
            elseif ($ort >= 85) $harf = 'BA';
            elseif ($ort >= 80) $harf = 'BB';
            elseif ($ort >= 75) $harf = 'CB';
            elseif ($ort >= 70) $harf = 'CC';
            elseif ($ort >= 60) $harf = 'DC';
            elseif ($ort >= 50) $harf = 'DD';
            else $harf = 'FF';

            // Veritabanına kaydet
            $stmt = $conn->prepare("
                INSERT INTO notlar (ogrenci_id, ders_id, vize, final, proje, ortalama, harf_notu, devamsizlik, tarih)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                vize = VALUES(vize),
                final = VALUES(final),
                proje = VALUES(proje),
                ortalama = VALUES(ortalama),
                harf_notu = VALUES(harf_notu),
                devamsizlik = VALUES(devamsizlik)
            ");
            
            $vize_val = $vize === '' ? NULL : $vize;
            $final_val = $final === '' ? NULL : $final;
            $proje_val = $proje === '' ? NULL : $proje;

            $stmt->bind_param("iiddddsii", $ogrenci_id, $ders_id, $vize_val, $final_val, $proje_val, $ort, $harf, $devamsizlik);
            
            if ($stmt->execute()) {
                $message = "✓ Not başarıyla kaydedildi!";
            } else {
                $error = "Veritabanı hatası: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = implode("<br>", $errors);
        }
    } else {
        $error = "Lütfen öğrenci ve ders seçiniz.";
    }
}

// Seçilen öğrencinin derslerini getir
$selected_ogrenci_dersler = [];
if (isset($_POST['ogrenci_id'])) {
    $ogrenci_id = $_POST['ogrenci_id'];
    $result = $conn->query("
        SELECT d.*, n.vize, n.final, n.proje, n.ortalama, n.harf_notu, n.devamsizlik
        FROM dersler d
        LEFT JOIN notlar n ON d.id = n.ders_id AND n.ogrenci_id = $ogrenci_id
        ORDER BY d.ad
    ");
    $selected_ogrenci_dersler = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Not Girişi</title>
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
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .content {
            padding: 30px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        select, input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            font-weight: 600;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .table-section {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .harf-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
        }

        .harf-aa { background: #28a745; color: white; }
        .harf-ba { background: #20c997; color: white; }
        .harf-bb { background: #17a2b8; color: white; }
        .harf-cb { background: #ffc107; color: #333; }
        .harf-cc { background: #fd7e14; color: white; }
        .harf-dc { background: #e83e8c; color: white; }
        .harf-dd { background: #dc3545; color: white; }
        .harf-ff { background: #6c757d; color: white; }

        .nav-link {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }

        .nav-link:hover {
            background: #764ba2;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border-left: 4px solid #667eea;
        }

        .stat-box h3 {
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-box p {
            font-size: 1.5em;
            font-weight: 600;
            color: #333;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5em;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Admin - Not Girişi</h1>
            <p>Öğrenci notlarını yönetin ve güncelleyin</p>
        </div>

        <div class="content">
            <a href="dashboard.php" class="nav-link">← Dashboard'a Dön</a>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h2>Not Giriş Formu</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ogrenci_id">Öğrenci Seçiniz *</label>
                            <select name="ogrenci_id" id="ogrenci_id" required onchange="this.form.submit()">
                                <option value="">-- Öğrenci Seçiniz --</option>
                                <?php while ($og = $ogrenciler->fetch_assoc()): ?>
                                    <option value="<?php echo $og['id']; ?>" <?php echo (isset($_POST['ogrenci_id']) && $_POST['ogrenci_id'] == $og['id']) ? 'selected' : ''; ?>>
                                        <?php echo $og['isim'] . ' ' . $og['soyisim'] . ' (' . $og['no'] . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <?php if (!empty($selected_ogrenci_dersler)): ?>
                        <div class="table-section">
                            <h3>Dersler ve Notlar</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Ders Adı</th>
                                        <th>Kredi</th>
                                        <th>Vize</th>
                                        <th>Final</th>
                                        <th>Proje</th>
                                        <th>Devamsızlık</th>
                                        <th>Ortalama</th>
                                        <th>Harf Notu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selected_ogrenci_dersler as $ders): ?>
                                        <tr>
                                            <td><?php echo $ders['ad']; ?></td>
                                            <td><?php echo $ders['kredi']; ?></td>
                                            <td>
                                                <input type="number" name="vize" min="0" max="100" step="0.5" 
                                                    value="<?php echo $ders['vize'] ?? ''; ?>" placeholder="0-100">
                                            </td>
                                            <td>
                                                <input type="number" name="final" min="0" max="100" step="0.5" 
                                                    value="<?php echo $ders['final'] ?? ''; ?>" placeholder="0-100">
                                            </td>
                                            <td>
                                                <input type="number" name="proje" min="0" max="100" step="0.5" 
                                                    value="<?php echo $ders['proje'] ?? ''; ?>" placeholder="0-100">
                                            </td>
                                            <td>
                                                <input type="number" name="devamsizlik" min="0" 
                                                    value="<?php echo $ders['devamsizlik'] ?? 0; ?>" placeholder="0">
                                            </td>
                                            <td>
                                                <strong><?php echo $ders['ortalama'] ? round($ders['ortalama'], 2) : '-'; ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($ders['harf_notu']): ?>
                                                    <span class="harf-badge harf-<?php echo strtolower($ders['harf_notu']); ?>">
                                                        <?php echo $ders['harf_notu']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span>-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <br>
                            <input type="hidden" name="ders_id" value="1">
                            <button type="submit">💾 Notları Kaydet</button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (isset($_POST['ogrenci_id'])): ?>
                <div class="stats">
                    <div class="stat-box">
                        <h3>Toplam Ders</h3>
                        <p><?php echo count($selected_ogrenci_dersler); ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Not Girilenler</h3>
                        <p><?php echo count(array_filter($selected_ogrenci_dersler, function($d) { return $d['ortalama'] !== null; })); ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Orta Not</h3>
                        <p><?php 
                            $ortalamalar = array_filter(array_column($selected_ogrenci_dersler, 'ortalama'));
                            echo count($ortalamalar) > 0 ? round(array_sum($ortalamalar) / count($ortalamalar), 2) : '-';
                        ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>