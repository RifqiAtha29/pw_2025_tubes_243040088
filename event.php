<?php
class Event {
    private $conn;
    private $table_name = "events";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($search = '', $category = '', $limit = 10, $offset = 0) {
        $query = "SELECT e.*, c.name as category_name, c.color as category_color, u.full_name as created_by_name
                  FROM " . $this->table_name . " e
                  LEFT JOIN event_categories c ON e.category_id = c.id
                  LEFT JOIN users u ON e.created_by = u.id
                  WHERE e.status = 'active'";
        
        if (!empty($search)) {
            $query .= " AND (e.title LIKE :search OR e.description LIKE :search OR e.location LIKE :search)";
        }
        
        if (!empty($category)) {
            $query .= " AND e.category_id = :category";
        }
        
        $query .= " ORDER BY e.event_date ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search", $search_param);
        }
        
        if (!empty($category)) {
            $stmt->bindParam(":category", $category);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT e.*, c.name as category_name, c.color as category_color, u.full_name as created_by_name
                  FROM " . $this->table_name . " e
                  LEFT JOIN event_categories c ON e.category_id = c.id
                  LEFT JOIN users u ON e.created_by = u.id
                  WHERE e.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, description, category_id, event_date, location, max_capacity, ticket_price, event_image, created_by)
                  VALUES (:title, :description, :category_id, :event_date, :location, :max_capacity, :ticket_price, :event_image, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }

    public function update($id, $data) {
        $fields = array_keys($data);
        $set_clause = implode(' = :, ', $fields) . ' = :';
        
        $query = "UPDATE " . $this->table_name . " SET {$set_clause} WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'cancelled' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getUserRSVP($event_id, $user_id) {
        $query = "SELECT * FROM rsvps WHERE event_id = :event_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createRSVP($event_id, $user_id, $notes = '') {
        $query = "INSERT INTO rsvps (event_id, user_id, notes) VALUES (:event_id, :user_id, :notes)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":notes", $notes);
        
        if ($stmt->execute()) {
            // Update current registrations
            $this->updateRegistrationCount($event_id);
            return true;
        }
        return false;
    }

    public function updateRSVP($event_id, $user_id, $status) {
        $query = "UPDATE rsvps SET status = :status WHERE event_id = :event_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($stmt->execute()) {
            $this->updateRegistrationCount($event_id);
            return true;
        }
        return false;
    }

    private function updateRegistrationCount($event_id) {
        $query = "UPDATE events SET current_registrations = (
                    SELECT COUNT(*) FROM rsvps WHERE event_id = :event_id AND status = 'confirmed'
                  ) WHERE id = :event_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->execute();
    }
}

$db = new PDO(
    'mysql:host=localhost;dbname=tubes2025_event_management',
    'root',  // Ganti dengan username database
    '',      // Ganti dengan password database (kosong jika tidak ada)
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Jika ada error SSL
    ]
);
?>