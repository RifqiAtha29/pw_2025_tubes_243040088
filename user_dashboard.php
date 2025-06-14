<?php
session_start();
session_destroy(); // Hapus session lama

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Set default values
$_SESSION['user']['full_name'] = $_SESSION['user']['full_name'] ?? $_SESSION['user']['username'];
$_SESSION['user']['email'] = $_SESSION['user']['email'] ?? '';

require_once 'User.php';
$db = new PDO('mysql:host=localhost;dbname=tubes2025_event_management', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$userModel = new User($db);

// Ambil data user dan event
$userData = $userModel->getById($_SESSION['user']['id']);

// Debug: Cek data
if (!$userData) {
    die("User data not found!");
}

$events = $userModel->getUserEvents($userData['id']);
$totalEvents = count($events);
$upcomingEvents = array_filter($events, function($event) {
    return strtotime($event['event_date']) >= time();
});

error_log("Redirecting to: " . ($userData['role'] === 'admin' ? 'admin' : 'user') . " dashboard");

// Debug: Tampilkan session SEBELUM modifikasi apapun
error_log("DASHBOARD SESSION: " . print_r($_SESSION['user'], true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header Baru -->
    <header class="bg-indigo-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold"><?= htmlspecialchars($userData['full_name']) ?>'s Dashboard</h1>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg">Logout</a>
        </div>
    </header>

    <!-- Konten Utama (Contoh Modifikasi) -->
    <main class="container mx-auto p-4">
        <!-- Card Profil -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center space-x-4">
                <img src="<?= !empty($userData['profile_photo']) ? htmlspecialchars($userData['profile_photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($userData['username']) ?>" 
                     class="w-20 h-20 rounded-full border-4 border-indigo-200">
                <div>
                    <h2 class="text-xl font-bold"><?= htmlspecialchars($userData['full_name']) ?></h2>
                    <p class="text-gray-600"><?= htmlspecialchars($userData['email'] ?? 'Not set') ?></p>
                    <p class="text-gray-600"><?= isset($_SESSION['user']['phone']) ? htmlspecialchars($_SESSION['user']['phone'] ?? '') : 'Nomor tidak tersedia' ?></p>
                </div>
            </div>
        </div>

        <!-- Fitur Baru: Event Terbaru -->
        <section class="mb-8">
            <h3 class="text-lg font-semibold mb-4">Event Terbaru</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Contoh Event Card -->
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <h4 class="font-bold text-indigo-700">Workshop Programming</h4>
                    <p class="text-sm text-gray-500">15 Juli 2025</p>
                    <button class="mt-2 bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs">Detail</button>
                </div>
                <!-- Tambahkan card lainnya -->
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <p>&copy; <?= date('Y') ?> EventKu. All rights reserved.</p>
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </footer>
</body>
</html>
