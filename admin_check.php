<?php
function adminOnly() {
    session_start();
    
    // Debug: Tampilkan data session
    error_log("Session data in admin_check: " . print_r($_SESSION, true));
    
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    
    if ($_SESSION['user']['role'] !== 'admin') {
        header('HTTP/1.0 403 Forbidden');
        die('
        <!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h1 { color: #dc3545; }
            </style>
        </head>
        <body>
            <h1>403 Forbidden</h1>
            <p>You dont have permission to access this page.</p>
            <a href="dashboard.php">Go to User Dashboard</a>
        </body>
        </html>
        ');
    }
}
