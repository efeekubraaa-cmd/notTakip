<?php
require_once '../config.php';

if (!isset($_SESSION['ogrenci_id'])) {
    header('Location: ../ogrenci_login.php');
    exit();
}

$ogrenci_id = $_SESSION['ogrenci_id'];

// Öğrenci bilgileri
$ogrenci = $conn->query("SELECT * FROM ogrenciler WHERE id = $ogrenci_id")->fetch_assoc();

// Öğrencinin notları
$notlar = $conn->query("
    SELECT d.ad, d.kod, n.vize, n.final, n.proje, n.ortalama, n.harf_notu, n.devamsizlik
    FROM notlar n
    JOIN dersler d ON n.ders_id = d.id
    WHERE n.ogrenci_id = $ogrenci_id
    ORDER BY d.ad
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Dashboard - Not Takip Sistemi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin-bottom: 10px;
        }
        
        .content {
            padding: 30px;
        }
        
        .student-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .student-info p {
            margin: 10px 0;
            color: #333;
        }
        
        .student-info strong {
            color: #f5576c;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th {
            background: #f5576c;
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
            background: #f8f9fa;
        }
        
        .harf-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            color: white;
        }
        
        .harf-aa { background: #28a745; }
        .harf-ba { background: #20c997; }
        .harf-bb { background: #17a2b8; }
        .harf-cb { background: #ffc107; color: #333; }
        .harf-cc { background: #fd7e14; }
        .harf-dc { background: #e83e8c; }
        .harf-dd { background: #dc3545; }
        .harf-ff { background: #6c757d; }
        
        .logout-btn {
            display: inline-block;
            background: #f5576c;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #e63946;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Hoşgeldiniz, <?php echo htmlspecialchars($ogrenci['isim']); ?>!</h1>
            <p>Notlarınızı ve akademik bilgilerinizi görüntüleyin</p>
        </div>
        
        <div class="content">
            <div class="student-info">
                <p><strong>Adı Soyadı:</strong> <?php echo htmlspecialchars($ogrenci['isim'] . ' ' . $ogrenci['soyisim']); ?></p>
                <p><strong>Öğrenci No:</strong> <?php echo $ogrenci['no']; ?></p>
                <p><strong>Sınıf:</strong> <?php echo htmlspecialchars($ogrenci['sinif'] ?? 'Tanımlanmamış'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($ogrenci['email']); ?></p>
            </div>
            
            <h2>Notlarınız</h2>
            <?php if (empty($notlar)): ?>
                <p style="color: #999; margin-top: 20px;">Henüz not giriş yapılmamıştır.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ders Adı</th>
                            <th>Kod</th>
                            <th>Vize</th>
                            <th>Final</th>
                            <th>Proje</th>
                            <th>Devamsızlık</th>
                            <th>Ortalama</th>
                            <th>Harf Notu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notlar as $not): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($not['ad']); ?></td>
                                <td><?php echo htmlspecialchars($not['kod']); ?></td>
                                <td><?php echo $not['vize'] ? number_format($not['vize'], 2) : '-'; ?></td>
                                <td><?php echo $not['final'] ? number_format($not['final'], 2) : '-'; ?></td>
                                <td><?php echo $not['proje'] ? number_format($not['proje'], 2) : '-'; ?></td>
                                <td><?php echo $not['devamsizlik']; ?></td>
                                <td><?php echo $not['ortalama'] ? number_format($not['ortalama'], 2) : '-'; ?></td>
                                <td>
                                    <?php if ($not['harf_notu']): ?>
                                        <span class="harf-badge harf-<?php echo strtolower($not['harf_notu']); ?>">
                                            <?php echo $not['harf_notu']; ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <a href="../ogrenci_login.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
</body>
</html>