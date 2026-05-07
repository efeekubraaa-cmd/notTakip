<?php
session_start();

// Veritabanı bağlantısı
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nottakip";

$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrol
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Karakter seti
$conn->set_charset("utf8");

// Timezone
date_default_timezone_set('Europe/Istanbul');
?>