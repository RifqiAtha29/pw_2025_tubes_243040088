<?php
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=tubes2025_event_management',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    echo "Koneksi berhasil!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
