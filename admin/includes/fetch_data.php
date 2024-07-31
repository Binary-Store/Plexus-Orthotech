<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Fetch all products, newest first
    $productsStmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC');
    $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

    // fetch all categories
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY id ASC');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->query('SELECT * FROM subcategories ORDER BY id ASC');
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //create subcategories array and add to category objects array
    foreach ($categories as $key => $category) {
        $categories[$key]['subcategories'] = [];
        foreach ($subcategories as $subcategory) {
            if ($subcategory['category_id'] == $category['id']) {
                $categories[$key]['subcategories'][] = $subcategory;
            }
        }
    }

    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            // 'categories' => array_values($filteredCategories),
            'categories' => $categories,
            'products' => $products
        ]
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>