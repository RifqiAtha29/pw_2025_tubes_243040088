<?php
$data = [
    'id' => 6,
    'phone' => '089638297610',
    'profile_photo' => '',
    'created_at' => '2025-06-14 00:39:42'
];

session_start();
$_SESSION['test'] = $data;

echo "<pre>ORIGINAL:\n";
print_r($data);
echo "\nSESSION:\n";
print_r($_SESSION['test']);
