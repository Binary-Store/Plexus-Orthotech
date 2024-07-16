<?php
include "variables.php";
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Expose-Headers: Authorization');
header('Content-Type: application/json');
include "db.php";
require "vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

function verifyToken($token)
{
  global $key;
  try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    return $decoded;
  } catch (\Exception $e) {
    return null;
  }
}

$headers = apache_request_headers();

$temp_token = $headers["Authorization"] ?? '';
$token = str_replace('Bearer ', '', $temp_token);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  if (empty($token)) {
    http_response_code(401);
    $response = array('success' => false, 'message' => "Token missing");
    echo json_encode($response);
    exit();
  }
  $userData = verifyToken($token);

  if ($userData === null || $userData->name != 'Admin') {
    http_response_code(401);
    $response = array('success' => false, 'message' => "Unauthorized");
    echo json_encode($response);
    exit();
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $productName = $_POST['product_name'];
  $categoryId = $_POST['category_id'];
  $subcategoryId = $_POST['subcategory_id'] ?? null;
  if (empty($subcategoryId)) {
    $subcategoryId = null;
}
  $productId = $_POST['product_id'];
  $imageName = null;


  if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
    $image = $_FILES['product_image'];
    $imageName = uniqid() . '-' . basename($image['name']);
    $imagePath = '../images/' . $imageName;

    if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
      $response = array('success' => false, 'message' => "Failed to upload image");
      echo json_encode($response);
      exit();
    }
  }

  // get image name of current product
  $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
  $stmt->execute([$productId]);
  $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($currentProduct) {
    $currentImageName = $currentProduct['image'];
  } else {
    $response = array('success' => false, 'message' => "Product not found");
    echo json_encode($response);
    exit();
  }
  if (!$imageName) {
    $imageName = $currentImageName;
  }
  try {
       $stmt = $pdo->prepare('SELECT * FROM products WHERE name = ? AND category_id = ? AND subcategory_id IS NULL');
    $stmt->execute([$productName, $categoryId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
      http_response_code(402);
      $response = array('success' => false, 'message' => 'Product name already exists in the category');
      echo json_encode($response);
      exit();
    }
    
    $stmt = $pdo->prepare('UPDATE products SET name = ?, image = ?, category_id = ?, subcategory_id = ? WHERE id = ?');
    $stmt->execute([$productName, $imageName, $categoryId, $subcategoryId, $productId]);

    // get product details
    $stmt = $pdo->prepare('SELECT  p.id AS id ,p.name AS name, p.image, c.name AS category,c.id AS category_id,sc.id AS subcategory_id, sc.name AS subcategory
        FROM products p
        INNER JOIN categories c ON p.category_id = c.id
        LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
        WHERE p.id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = array('success' => true, 'message' => "Product update successfully", 'product' => $product);
  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => $e->getMessage());
  }
  echo json_encode($response);

}
?>