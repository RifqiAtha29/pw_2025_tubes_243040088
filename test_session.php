<?php
session_start();
$_SESSION['test'] = [
    'phone' => '123456789',
    'created_at' => date('Y-m-d H:i:s')
];
print_r($_SESSION['test']);
