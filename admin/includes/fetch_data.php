<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Fetch all products, newest first
    $productsStmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC');
    $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all categories
    // $categoriesStmt = $pdo->query('SELECT * FROM categories');
    // $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

    // foreach ($categories as &$category) {
    //     // Fetch subcategories for each category
    //     $subcategoriesStmt = $pdo->prepare('SELECT * FROM subcategories WHERE category_id = ?');
    //     $subcategoriesStmt->execute([$category['id']]);
    //     $subcategories = $subcategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

    //     // Filter out subcategories without products
    //     $filteredSubcategories = [];
    //     foreach ($subcategories as $subcategory) {
    //         $hasProducts = false;
    //         foreach ($products as $product) {
    //             if ($product['subcategory_id'] == $subcategory['id']) {
    //                 $hasProducts = true;
    //                 break;
    //             }
    //         }
    //         if ($hasProducts) {
    //             $filteredSubcategories[] = $subcategory;
    //         }
    //     }

    //     // Assign filtered subcategories to category
    //     $category['subcategories'] = $filteredSubcategories;
    // }

    //  //iterate over products and get all unique categoriy id
    // $uniqueCategories = [];
    // foreach ($products as $product) {
    //     $uniqueCategories[] = $product['category_id'];
    // }
    // $uniqueCategories = array_unique($uniqueCategories);

    // // Filter out categories without subcategories or category id in $categoryIds
    // $filteredCategories = [];

    // //use for loop not foreach
    // for ($i = 0; $i < count($categories); $i++) {
    //     if (in_array($categories[$i]['id'], $uniqueCategories) || 
    //     count($categories[$i]['subcategories'])>0) {
    //         $filteredCategories[] = $categories[$i];
    //     }
    // }

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