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
  $subcategoryId = $_POST['subcategory_id'];
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
    $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, image = ?, category_id = ?, subcategory_id = ? WHERE id = ?');
    $stmt->execute([$productName, $productDescription, $imageName, $categoryId, $subcategoryId, $productId]);
    $response = array('success' => true, 'message' => "Product updated successfully");
  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => "Something went wrong");
  }
  echo json_encode($response);

}
?>