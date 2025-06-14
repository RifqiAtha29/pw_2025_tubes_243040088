<?php
// admin_navbar.php
?>
<nav class="bg-purple-800 text-white shadow-lg">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <i class="fas fa-user-shield text-2xl"></i>
            <span class="text-xl font-bold">Admin Panel</span>
        </div>
        <div class="flex items-center space-x-6">
            <a href="admin_dashboard.php" class="hover:text-purple-200">
                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
            </a>
            <a href="manage_users.php" class="bg-purple-700 px-3 py-1 rounded hover:bg-purple-600">
                <i class="fas fa-users mr-1"></i> Users
            </a>
            <a href="manage_events.php" class="hover:text-purple-200">
                <i class="fas fa-calendar-alt mr-1"></i> Events
            </a>
            <a href="logout.php" class="hover:text-purple-200">
                <i class="fas fa-sign-out-alt mr-1"></i> Logout
            </a>
        </div>
    </div>
</nav>
