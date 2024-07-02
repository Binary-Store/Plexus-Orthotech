<?php
// Database connection settings
require_once 'db.php';

// Fetch video links from the database
try {
    $stmt = $pdo->query('SELECT * FROM video_link ORDER BY id DESC');
    $videoLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare response as JSON
    header('Content-Type: application/json');
    echo json_encode($videoLinks);
} catch (PDOException $e) {
    die("Error fetching video links: " . $e->getMessage());
}
?>
