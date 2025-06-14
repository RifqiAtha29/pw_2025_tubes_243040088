<?php
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=tubes2025_event_management',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Koneksi database berhasil!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
