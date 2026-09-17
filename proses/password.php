<?php
require '../config/database.php'; require '../includes/auth.php'; require_login();
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: ../index.php');exit;}
$old=$_POST['password_lama']??'';$new=$_POST['password_baru']??'';$confirm=$_POST['konfirmasi']??'';
$s=$pdo->prepare("SELECT password FROM users WHERE id=?");$s->execute([$_SESSION['user']['id']]);$u=$s->fetch();
$back=$_SESSION['user']['role']==='admin'?'../admin/pengaturan.php':'../siswa/pengaturan.php';
if(!$u||!password_verify($old,$u['password'])){header("Location:$back?error=Password lama salah");exit;}
if(strlen($new)<6||$new!==$confirm){header("Location:$back?error=Password baru tidak valid atau konfirmasi berbeda");exit;}
$pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new,PASSWORD_DEFAULT),$_SESSION['user']['id']]);
header("Location:$back?success=Password berhasil diubah");exit;
