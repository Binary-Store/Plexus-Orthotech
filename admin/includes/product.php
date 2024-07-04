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
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  try {
    $productsStmt = $pdo->query('SELECT p.name AS name, p.description, p.image, c.name AS category, sc.name AS subcategory
        FROM products p
        INNER JOIN categories c ON p.category_id = c.id
        INNER JOIN subcategories sc ON p.subcategory_id = sc.id');
    $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
    $response = array('success' => true, 'products' => $products);
    echo json_encode($response);

  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => $e->getMessage());
    echo json_encode($response);
    exit();
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $productName = $_POST['product_name'];
  $productDescription = $_POST['product_description'];
  $categoryId = $_POST['category_id'];
  $subcategoryId = $_POST['subcategory_id'];
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

  try {
    $stmt = $pdo->prepare('INSERT INTO products (name, description, image, category_id, subcategory_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$productName, $productDescription, $imageName, $categoryId, $subcategoryId]);
    $response = array('success' => true, 'message' => "Product added successfully");
    echo json_encode($response);
  } catch (PDOException $e) {
    if ($e->getCode() == 23000) {
      $response = array('success' => false, 'message' => "Product name must be unique within the same subcategory.");
    } else {
      $response = array('success' => false, 'message' => "Something went wrong");
    }
    echo json_encode($response);
    exit();
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $urlParts = explode('/', $_SERVER['REQUEST_URI']);
  $productId = end($urlParts);


  if (!is_numeric($productId)) {
    http_response_code(400); // Bad Request
    $response = array('success' => false, 'message' => "subCategory Id is invalid");
    echo json_encode($response);
    exit;
  }

  try {

    // Check if there are any products in the subcategory
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
      $response = array('success' => false, 'message' => "Product not found");
      echo json_encode($response);
      exit();
    }

    // unlink images
    $image = $product['image'];
    if ($image) {
      unlink('../images/' . $image);
    }

    // Delete category
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $response = array('success' => true, 'message' => "Product deleted successfully");
    echo json_encode($response);

  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => $e->getMessage());
    echo json_encode($response);
    exit();
  }
}

?>