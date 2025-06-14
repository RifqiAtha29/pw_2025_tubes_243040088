<?php
  require_once 'user.php';
  $db = new PDO('mysql:host=localhost;dbname=nama_database', 'root', '');
  $user = new User($db);

  $data = [
      'username' => 'admin_baru',
      'email' => 'admin@example.com',
      'password' => 'password_rahasia',
      'full_name' => 'Admin Baru',
      'role' => 'admin'
  ];

  if ($user->register($data)) {
      echo "Admin berhasil dibuat!";
  } else {
      echo "Gagal membuat admin.";
  }
  ?>