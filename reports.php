<?php
session_start();
require_once 'user.php';

// Strict admin access control
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Koneksi database
$db = new PDO('mysql:host=localhost;dbname=tubes2025_event_management', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$userModel = new User($db);

// Data untuk report
$userGrowth = $userModel->getUserGrowthReport();
$eventStats = $userModel->getEventStatistics();
$activeUsers = $userModel->getActiveUsers(5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Admin Navbar -->
    <nav class="gradient-bg text-white shadow-xl">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <i class="fas fa-chart-line text-2xl"></i>
                <span class="text-xl font-bold">Analytics Dashboard</span>
            </div>
            <div class="flex items-center space-x-6">
                <span class="px-3 py-1 bg-white bg-opacity-20 rounded-full">
                    <i class="fas fa-user-shield mr-1"></i> Admin Mode
                </span>
                <a href="admin_dashboard.php" class="hover:text-purple-200">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Advanced Reports</h1>
                <p class="text-gray-600">Last updated: <?= date('F j, Y, g:i a') ?></p>
            </div>
            <div class="mt-4 md:mt-0">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    <i class="fas fa-download mr-2"></i> Export PDF
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 card-hover">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Total Users</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $userModel->getTotalUsers() ?></h3>
                        <p class="text-sm text-green-500 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i> 12% from last month
                        </p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full text-indigo-800">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 card-hover">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Active Events</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $eventStats['active_events'] ?? 0 ?></h3>
                        <p class="text-sm text-blue-500 mt-1">
                            <i class="fas fa-calendar-check mr-1"></i> <?= $eventStats['upcoming_events'] ?? 0 ?> upcoming
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full text-green-800">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 card-hover">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Avg. Engagement</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $eventStats['avg_participants'] ?? 0 ?></h3>
                        <p class="text-sm text-purple-500 mt-1">
                            <i class="fas fa-user-clock mr-1"></i> per event
                        </p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full text-purple-800">
                        <i class="fas fa-chart-pie text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- User Growth Chart -->
            <div class="bg-white rounded-xl p-6 shadow card-hover">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-user-plus mr-2 text-indigo-600"></i> User Growth
                </h3>
                <canvas id="userGrowthChart" height="250"></canvas>
            </div>

            <!-- Event Distribution Chart -->
            <div class="bg-white rounded-xl p-6 shadow card-hover">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-calendar-week mr-2 text-green-600"></i> Event Distribution
                </h3>
                <canvas id="eventDistributionChart" height="250"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-bolt mr-2 text-yellow-500"></i> Most Active Users
                </h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php foreach ($activeUsers as $user): ?>
                <div class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-800">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div class="ml-auto">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                <?= $user['event_count'] ?> events
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Export Section -->
        <div class="bg-white rounded-xl p-6 shadow">
</body>
</html>
