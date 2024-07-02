<?php
include 'db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'error' => ''];

try {
    // Fetch last 8 products
    $stmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC LIMIT 8');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $products;
} catch (PDOException $e) {
    $response['error'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>
