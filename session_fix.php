<?php
// session_fix.php
session_start();
require_once 'User.php'; // Sesuaikan path

// Koneksi database
$db = new PDO('mysql:host=localhost;dbname=tubes2025_event_management', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$userModel = new User($db);

// Ambil data LENGKAP user dari database berdasarkan ID di session
$user = $userModel->getUserById($_SESSION['user']['id']); // Pastikan method ini ada

// Overwrite session dengan data lengkap
$_SESSION['user'] = [
    'id' => $user['id'],
    'username' => $user['username'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'role' => $user['role'],
    'profile_photo' => $user['profile_photo'],
    'created_at' => $user['created_at']
];

// Redirect ke halaman dashboard
header('Location: dashboard.php');
exit;