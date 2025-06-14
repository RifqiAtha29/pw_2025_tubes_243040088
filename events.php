<?php
// Mulai session
session_start();

// Redirect ke halaman login jika tidak ada session user
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil data user dari session
$user = $_SESSION['user'];

// Koneksi database (sesuaikan dengan konfigurasi Anda)
require_once 'user.php';
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
    $userModel = new User($db);
    $events = $userModel->getUserEvents($_SESSION['user']['id']);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .logout-btn {
            background-color: var(--accent);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background-color: #d1146d;
            transform: translateY(-2px);
        }
        
        .page-title {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: var(--secondary);
            text-align: center;
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .event-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        }
        
        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .event-details {
            padding: 1.5rem;
        }
        
        .event-title {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }
        
        .event-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .event-meta i {
            margin-right: 0.3rem;
            color: var(--accent);
        }
        
        .event-description {
            margin-bottom: 1.5rem;
            color: #555;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-confirmed {
            background-color: #e3fcef;
            color: #27ae60;
        }
        
        .status-pending {
            background-color: #fff8e6;
            color: #f39c12;
        }
        
        .no-events {
            text-align: center;
            grid-column: 1 / -1;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .no-events p {
            margin-bottom: 1rem;
            color: #666;
        }
        
        .explore-btn {
            background-color: var(--primary);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .explore-btn:hover {
            background-color: var(--secondary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">EventHub</div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>
        
        <h1 class="page-title">Event Saya</h1>
        
        <div class="events-grid">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <img 
                            src="<?php echo htmlspecialchars($event['event_image'] ?? 'default-event.jpg'); ?>" 
                            alt="<?php echo htmlspecialchars($event['title']); ?>" 
                            class="event-image"
                        >
                        <div class="event-details">
                            <h2 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h2>
                            
                            <div class="event-meta">
                                <span><i>📍</i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i>📅</i> <?php echo date('d M Y', strtotime($event['event_date'])); ?></span>
                            </div>
                            
                            <p class="event-description">
                                <?php echo htmlspecialchars($event['description'] ?? 'Deskripsi tidak tersedia'); ?>
                            </p>
                            
                            <span class="status-badge status-<?php echo strtolower($event['rsvp_status']); ?>">
                                <?php echo htmlspecialchars($event['rsvp_status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-events">
                    <p>Anda belum terdaftar di event apapun.</p>
                    <a href="explore_events.php" class="explore-btn">Jelajahi Event</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
