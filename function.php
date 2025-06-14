<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage($file, $target_dir = 'uploads/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau WebP.");
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception("Ukuran file terlalu besar. Maksimal 5MB.");
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_extension;
    $target_path = $target_dir . $filename;
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $filename;
    }
    
    return false;
}

function formatDate($date) {
    return date('d M Y, H:i', strtotime($date));
}

function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function getEventStatus($event_date, $status) {
    $now = new DateTime();
    $event_datetime = new DateTime($event_date);
    
    if ($status === 'cancelled') {
        return ['status' => 'Dibatalkan', 'class' => 'danger'];
    }
    
    if ($event_datetime < $now) {
        return ['status' => 'Selesai', 'class' => 'secondary'];
    }
    
    $diff = $now->diff($event_datetime);
    if ($diff->days <= 7) {
        return ['status' => 'Segera Dimulai', 'class' => 'warning'];
    }
    
    return ['status' => 'Aktif', 'class' => 'success'];
}
?>