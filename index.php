<?php
session_start();
if (!empty($_SESSION['user'])) {
    header('Location: '.($_SESSION['user']['role']==='admin' ? 'admin/dashboard.php' : 'siswa/dashboard.php')); exit;
}
header('Location: login.php'); exit;
