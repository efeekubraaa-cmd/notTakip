<?php
require_once '../config.php';

session_destroy();
header('Location: ../ogrenci_login.php');
exit();