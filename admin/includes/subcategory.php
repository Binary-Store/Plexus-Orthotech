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
  $postData = file_get_contents('php://input');

  $jsonData = json_decode($postData, true);
  $categoryId = $jsonData['categoryId'] ?? '';
  $subcategoryName = $jsonData['name'] ?? '';

  try {
    // check category id is valid or in valid
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        http_response_code(402);
      $response = array('success' => false, 'message' => "CategoryId is not valid");
      echo json_encode($response);
      exit();
    }

    // Check if sub category name already exists i for new sub category insert in particular category
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM subcategories WHERE name = ? AND category_id = ?');
    $stmt->execute([$subcategoryName, $categoryId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        http_response_code(402);
      $response = array('success' => false, 'message' => "Subcategory name must be unique within the same category.");
    } else {

      // Insert new subcategory
      $stmt = $pdo->prepare('INSERT INTO subcategories (name, category_id) VALUES (?, ?)');
      $stmt->execute([$subcategoryName, $categoryId]);

      //get last insert id and get data
      $lastInsertId = $pdo->lastInsertId();
      $stmt = $pdo->prepare('SELECT * FROM subcategories WHERE id = ?');
      $stmt->execute([$lastInsertId]);
      $subcategory = $stmt->fetch(PDO::FETCH_ASSOC);

      $response = array('success' => true, 'message' => "Subcategory added successfully", 'subcategory' => $subcategory);
    }
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
  }

  echo json_encode($response);
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $postData = file_get_contents('php://input');

  $jsonData = json_decode($postData, true);
  $categoryId = $jsonData['categoryId'] ?? '';
  $subcategoryId = $jsonData['subcategoryId'] ?? '';
  $subcategoryName = $jsonData['name'] ?? '';

  try {
    // check category id is valid or in valid
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        http_response_code(402);
      $response = array('success' => false, 'message' => "CategoryId is not valid");
      echo json_encode($response);
      exit();
    }

    // Check if the subcategory name already exists (excluding the current subcategory)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM subcategories WHERE name = ? AND category_id = ? AND id != ?');
    $stmt->execute([$subcategoryName, $categoryId, $subcategoryId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        http_response_code(402);
      $response = array('success' => false, 'message' => "Subcategory name must be unique within the same category.");
    } else {

      // check sub category exist or not
      $stmt = $pdo->prepare('SELECT COUNT(*) FROM subcategories WHERE id = ?');
      $stmt->execute([$subcategoryId]);
      $count = $stmt->fetchColumn();

      if ($count == 0) {
          http_response_code(402);
        $response = array('success' => false, 'message' => "SubCategory not found");
        echo json_encode($response);
        exit();
      }

      // Update category
      $stmt = $pdo->prepare('UPDATE subcategories SET name = ? WHERE id = ?');
      $stmt->execute([$subcategoryName, $subcategoryId]);

      //get last inser id and send data
      $stmt = $pdo->prepare('SELECT * FROM subcategories WHERE id = ?');
      $stmt->execute([$subcategoryId]);
      $subcategory = $stmt->fetch(PDO::FETCH_ASSOC); 
      $response = array('success' => true, 'message' => "Subcategory updated successfully", 'subcategory' => $subcategory);

    }
  } catch (PDOException $e) {
      http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
  }
  echo json_encode($response);
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $urlParts = explode('/', $_SERVER['REQUEST_URI']);
  $subcategoryId = end($urlParts);

  if (!is_numeric($subcategoryId)) {
    http_response_code(400); // Bad Request
    $response = array('success' => false, 'message' => "subCategory Id is invalid");
    echo json_encode($response);
    exit;
  }

  try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM subcategories WHERE id = ?');
    $stmt->execute([$subcategoryId]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        http_response_code(402);
      $response = array('success' => false, 'message' => "subCategory not found");
      echo json_encode($response);
      exit();
    }

    // Check if there are any products in the subcategory
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE subcategory_id = ?');
    $stmt->execute([$subcategoryId]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
      http_response_code(505);
      $response = array('success' => false, 'message' => "Subcategory has products. Please delete the products first.");
      echo json_encode($response);
      exit();
    }

    // Delete category
    $stmt = $pdo->prepare('DELETE FROM subcategories WHERE id = ?');
    $stmt->execute([$subcategoryId]);
    $response = array('success' => true, 'message' => "subCategory deleted successfully");
    echo json_encode($response);

  } catch (PDOException $e) {
      http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
}


?>