<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Fetch all products, newest first
    $productsStmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC');
    $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all categories
    $categoriesStmt = $pdo->query('SELECT * FROM categories');
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as &$category) {
        // Fetch subcategories for each category
        $subcategoriesStmt = $pdo->prepare('SELECT * FROM subcategories WHERE category_id = ?');
        $subcategoriesStmt->execute([$category['id']]);
        $subcategories = $subcategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter out subcategories without products
        $filteredSubcategories = [];
        foreach ($subcategories as $subcategory) {
            $hasProducts = false;
            foreach ($products as $product) {
                if ($product['subcategory_id'] == $subcategory['id']) {
                    $hasProducts = true;
                    break;
                }
            }
            if ($hasProducts) {
                $filteredSubcategories[] = $subcategory;
            }
        }

        // Assign filtered subcategories to category
        $category['subcategories'] = $filteredSubcategories;
    }

    // Filter out categories without subcategories
    $filteredCategories = array_filter($categories, function($category) {
        return !empty($category['subcategories']);
    });

    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'categories' => array_values($filteredCategories),
            'products' => $products
        ]
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
