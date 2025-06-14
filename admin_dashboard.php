<?php
// 1. Proteksi admin
require_once __DIR__ . '/admin_check.php';
adminOnly();

// 2. Ambil data dari database
$stats = [
    'total_users' => 0,
    'active_events' => 0,
    'revenue' => 0
];
$activities = [];

// Buat koneksi PDO
$host = 'localhost';
$db_name = 'tubes2025_event_management'; // Ganti dengan nama database Anda
$username = 'root';
$password = ''; // Kosongkan jika tidak ada password

try {
    $db = new PDO(
        "mysql:host={$host};dbname={$db_name}", 
        $username, 
        $password
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hitung total user
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Hitung event aktif
    $stmt = $db->query("SELECT COUNT(*) FROM events WHERE status = 'active'");
    $stats['active_events'] = $stmt->fetchColumn();
    
    // Hitung revenue
    $stmt = $db->query("SELECT COALESCE(SUM(ticket_price), 0) FROM events");
    $stats['revenue'] = $stmt->fetchColumn();
    
    // Ambil aktivitas terbaru
    $stmt = $db->query("SELECT description, created_at FROM activities ORDER BY created_at DESC LIMIT 5");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$path = __DIR__ . '/user.php';

require_once $path;

// Cek role admin
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Koneksi database
$userModel = new User($db);

// --- HANDLE CRUD ACTIONS --- //
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// CREATE: Tambah user baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $data = [
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'password' => password_hash('default123', PASSWORD_DEFAULT), // Password default
        'role' => trim($_POST['role'])
    ];
    $userModel->register($data);
    header('Location: admin_dashboard.php?success=created');
    exit;
}

// UPDATE: Edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
    $data = [
        'id' => $id,
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'role' => trim($_POST['role'])
    ];
    $userModel->updateUser($data);
    header('Location: admin_dashboard.php?success=updated');
    exit;
}

// DELETE: Hapus user
if ($action === 'delete' && $id > 0) {
    $userModel->deleteUser($id);
    header('Location: admin_dashboard.php?success=deleted');
    exit;
}

// READ: Ambil semua user
$users = $userModel->getAllUsers();

// Ambil data user untuk edit (jika ada)
$editUser = null;
if ($action === 'edit' && $id > 0) {
    $editUser = $userModel->getById($id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="w-64 bg-blue-800 text-white p-4 hidden md:block">
        <!-- ... konten sidebar lainnya ... -->
        <div class="hidden md:block mt-auto p-4">
            <a href="logout.php" class="...">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-auto">
        <!-- Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <!-- ... menu toggle ... -->
            <div class="flex items-center space-x-4">
                <div class="md:hidden">
                    <a href="logout.php" class="...">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </header>
        
        <div class="container mx-auto p-6">
            <!-- Notifikasi Sukses -->
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                    <?php
                    $message = match ($_GET['success']) {
                        'created' => 'User berhasil ditambahkan!',
                        'updated' => 'User berhasil diperbarui!',
                        'deleted' => 'User berhasil dihapus!',
                        default => ''
                    };
                    echo $message;
                    ?>
                </div>
            <?php endif; ?>

            <!-- Header dan Tombol Tambah -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Manajemen User</h1>
                <button onclick="openModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Tambah User
                </button>
            </div>

            <!-- Tabel Data User -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="openModal('edit', <?= $user['id'] ?>)" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="admin_dashboard.php?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Yakin hapus user ini?')" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form (Create/Edit) -->
    <div id="crudModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg w-full max-w-md">
            <div class="p-6">
                <h2 id="modalTitle" class="text-xl font-bold mb-4">Tambah User Baru</h2>
                <form method="POST" action="admin_dashboard.php?action=<?= $action ?><?= $id ? '&id=' . $id : '' ?>">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" value="<?= $editUser['username'] ?? '' ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="<?= $editUser['email'] ?? '' ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                            <option value="user" <?= isset($editUser) && $editUser['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= isset($editUser) && $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-md">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openModal(action, id = null) {
        document.getElementById('crudModal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = action === 'create' ? 'Tambah User Baru' : 'Edit User';
        document.getElementById('username').value = '';
        document.getElementById('email').value = '';
        document.getElementById('role').value = 'user';
        if (id) {
            document.getElementById('username').value = '<?= $editUser['username'] ?? '' ?>';
            document.getElementById('email').value = '<?= $editUser['email'] ?? '' ?>';
            document.getElementById('role').value = '<?= $editUser['role'] ?? 'user' ?>';
        }
    }

    function closeModal() {
        document.getElementById('crudModal').classList.add('hidden');
    }
    </script>

    <div class="relative">
        <!-- Tombol Profil -->
        <button class="flex items-center space-x-2">
            <img src="https://ui-avatars.com/api/?name=Admin" class="w-8 h-8 rounded-full">
        </button>
        
        <!-- Dropdown Menu -->
        <div class="hidden absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg">
            <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">
                <i class="fas fa-user mr-2"></i> Profil
            </a>
            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </div>
</body>
</html>
