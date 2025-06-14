<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function authenticate($username, $password) {
        try {
            // Pastikan mengambil kolom password
            $query = "SELECT id, username, role, password FROM users 
                     WHERE username = :username OR email = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug: Cek data yang diambil
            error_log("User data from DB: " . print_r($user, true));

            if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
                unset($user['password']); // Hapus password sebelum disimpan di session
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }

    public function register($data) {
        // Pastikan semua field ada di $data
        $required_fields = ['username', 'email', 'password', 'full_name', 'phone'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = null; // Atau beri nilai default
            }
        }

        $query = "INSERT INTO " . $this->table . " 
                  (username, email, password, full_name, phone) 
                  VALUES (:username, :email, :password, :full_name, :phone)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Bind parameter secara eksplisit
        $stmt->bindParam(":username", $data['username']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":password", $data['password']);
        $stmt->bindParam(":full_name", $data['full_name']);
        $stmt->bindParam(":phone", $data['phone']);
        
        return $stmt->execute();
    }

    public function getById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserEvents($user_id) {
        $query = "SELECT e.*, r.status as rsvp_status, r.registration_date, c.name as category_name
                  FROM rsvps r
                  JOIN events e ON r.event_id = e.id
                  LEFT JOIN event_categories c ON e.category_id = c.id
                  WHERE r.user_id = :user_id
                  ORDER BY e.event_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getTotalEvents() {
        $query = "SELECT COUNT(*) as total FROM events";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getRecentUsers($limit = 5) {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentEvents($limit = 5) {
        $query = "SELECT * FROM events ORDER BY event_date DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Mendapatkan laporan pertumbuhan pengguna per bulan
     */
    public function getUserGrowthReport() {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') AS month,
                    COUNT(*) AS total_users
                  FROM users
                  GROUP BY month
                  ORDER BY month DESC
                  LIMIT 12";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mendapatkan statistik event berdasarkan status
     */
    public function getEventStatistics() {
        $query = "SELECT 
                    status,
                    COUNT(*) AS total_events
                  FROM events
                  GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mendapatkan daftar pengguna aktif
     */
    public function getActiveUsers($limit = 5) {
        // Alternatif 1: Hitung partisipasi event dari tabel events (jika ada kolom created_by)
        $query = "SELECT 
                    username,
                    COUNT(events.id) AS event_count
                  FROM users
                  LEFT JOIN events ON users.id = events.created_by
                  GROUP BY users.id
                  ORDER BY event_count DESC
                  LIMIT :limit";

        // Alternatif 2: Ambil user terbaru (jika tidak ada data partisipasi)
        // $query = "SELECT username FROM users ORDER BY created_at DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>