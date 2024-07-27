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
    $response = array('success' => true, 'categories' => $categories);
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "Error fetching categories: " . $e->getMessage());
    echo json_encode($response);
    exit();
  }
  $response = array('success' => true, 'categories' => $categories);

  echo json_encode($response);
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postData = file_get_contents('php://input');

  $jsonData = json_decode($postData, true);
  $categoryName = $jsonData['name'] ?? '';
  try {
    // Check if category name already exists for new category insert
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
    $stmt->execute([$categoryName]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      http_response_code(402);
      $response = array('success' => false, 'message' => "Category name '$categoryName' already exists.");
    } else {
      // Insert new category
      $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
      $stmt->execute([$categoryName]);

      //get last insert id as category id
      $categoryId = $pdo->lastInsertId();
      //get data for this category id
      $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
      $stmt->execute([$categoryId]);
      $category = $stmt->fetch(PDO::FETCH_ASSOC);

      $response = array('success' => true, 'message' => "Category added successfully", "category" => $category);
    }
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
  }

  echo json_encode($response);
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $postData = file_get_contents('php://input');

  $jsonData = json_decode($postData, true);
  $categoryName = $jsonData['name'] ?? '';
  $categoryId = $jsonData['id'] ?? '';

  try {
    // Check if category name already exists, excluding the current category being updated
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?');
    $stmt->execute([$categoryName, $categoryId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      http_response_code(402);
      $response = array('success' => false, 'message' => "Category name '$categoryName' already exists.");
    } else {
      $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
      $stmt->execute([$categoryId]);
      $count = $stmt->fetchColumn();

      if ($count == 0) {
        http_response_code(402);
        $response = array('success' => false, 'message' => "Category not found");
        echo json_encode($response);
        exit();
      }

      // Update category
      $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
      $stmt->execute([$categoryName, $categoryId]);
      $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
      $stmt->execute([$categoryId]);
      $category = $stmt->fetch(PDO::FETCH_ASSOC);
      $response = array('success' => true, 'message' => "Category updated successfully", 'category' => $category);
    }
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
  }
  echo json_encode($response);
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $urlParts = explode('/', $_SERVER['REQUEST_URI']);
  $categoryId = end($urlParts);

  if (!is_numeric($categoryId)) {
    http_response_code(400); // Bad Request
    $response = array('success' => false, 'message' => "Category Id is invalid");
    echo json_encode($response);
    exit;
  }

  try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
      http_response_code(402);
      $response = array('success' => false, 'message' => "Category not found");
      echo json_encode($response);
      exit();
    }

    // Check if there are any subcategories in the category
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM subcategories WHERE category_id = ?');
    $stmt->execute([$categoryId]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
      http_response_code(402);
      $response = array('success' => false, 'message' => "Category cannot be deleted because it contains subcategories.");
      echo json_encode($response);
      exit();
    }

    // Delete category
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $response = array('success' => true, 'message' => "Category deleted successfully");
    echo json_encode($response);

  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
}


?>