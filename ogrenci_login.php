<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
    $sifre = trim($_POST['sifre'] ?? '');
    
    if (empty($kullanici_adi) || empty($sifre)) {
        $error = 'Kullanıcı adı ve şifre boş bırakılamaz!';
    } else {
        $stmt = $conn->prepare("SELECT id, isim, soyisim FROM ogrenciler WHERE kullanici_adi = ? AND sifre = SHA2(?, 256) AND durum = 'aktif'");
        $stmt->bind_param("ss", $kullanici_adi, $sifre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $ogrenci = $result->fetch_assoc();
            $_SESSION['ogrenci_id'] = $ogrenci['id'];
            $_SESSION['ogrenci_adi'] = $ogrenci['isim'] . ' ' . $ogrenci['soyisim'];
            $_SESSION['user_type'] = 'ogrenci';
            header("Location: ogrenci/dashboard.php");
            exit();
        } else {
            $error = 'Geçersiz kullanıcı adı veya şifre!';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Giriş - Not Takip Sistemi</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .login-container p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #f5576c;
            box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.1);
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .login-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .login-links a {
            color: #f5576c;
            text-decoration: none;
            margin: 0 5px;
        }
        
        .login-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>📚 Not Takip</h1>
        <p>Öğrenci Giriş</p>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="kullanici_adi">Kullanıcı Adı</label>
                <input type="text" id="kullanici_adi" name="kullanici_adi" required>
            </div>
            
            <div class="form-group">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" required>
            </div>
            
            <button type="submit" class="submit-btn">Giriş Yap</button>
        </form>
        
        <div class="login-links">
            <p>Admin misiniz? <a href="admin_login.php">Admin Girişi</a></p>
        </div>
    </div>
</body>
</html>