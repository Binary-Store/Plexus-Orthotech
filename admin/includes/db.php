<?php
$host = 'localhost';
$db = 'u371241921_plexus';
$user = 'u371241921_root';
$pass = 'Plexus.root.mysql7';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>