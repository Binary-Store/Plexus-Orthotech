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
    $positionStmt = $pdo->query('SELECT * FROM opningPosition');
    $position = $positionStmt->fetchAll(PDO::FETCH_ASSOC);
    $response = array('success' => true, 'careers' => $position);
    echo json_encode($response);
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

  $urlParts = explode('/', $_SERVER['REQUEST_URI']);
  $careerId = end($urlParts);

  // below code is delete position from opningPosition table
  try {
    $stmt = $pdo->prepare('DELETE FROM opningPosition WHERE id = ?');
    $stmt->execute([$careerId]);
    $response = array('success' => true, 'message' => "Position deleted successfully");
    echo json_encode($response);
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "something went wrong");
    echo json_encode($response);
    exit();
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  // ther will two column which is position-name and position-address
  $position_name = $data['position_name'];
  $position_address = $data['position_address'];

  if (empty($position_name) || empty($position_address)) {
    http_response_code(400);
    $response = array('success' => false, 'message' => "All fields are required");
    echo json_encode($response);
    exit();
  }

  // store data in opningPosition table and send new data
  try {
    // check position name and address already exist or not
    $stmt = $pdo->prepare('SELECT * FROM opningPosition WHERE name = ? AND address = ?');
    $stmt->execute([$position_name, $position_address]);
    $career = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($career) {
      http_response_code(400);
      $response = array('success' => false, 'message' => "Position already exist");
      echo json_encode($response);
      exit();
    }
    $stmt = $pdo->prepare('INSERT INTO opningPosition (name, address) VALUES (?, ?)');
    $stmt->execute([$position_name, $position_address]);
    $careerId = $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT * FROM opningPosition WHERE id = ?');
    $stmt->execute([$careerId]);
    $career = $stmt->fetch(PDO::FETCH_ASSOC);
    $response = array('success' => true, 'message' => "Career added successfully", 'career' => $career);
    echo json_encode($response);
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }

} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $urlParts = explode('/', $_SERVER['REQUEST_URI']);
  $positionId = end($urlParts);

  $data = json_decode(file_get_contents('php://input'), true);
  $position_name = $data['position_name'];
  $position_address = $data['position_address'];

  if (empty($position_name) || empty($position_address)) {
    http_response_code(400);
    $response = array('success' => false, 'message' => "All fields are required");
    echo json_encode($response);
    exit();
  }

  try {
    // check position name and address already exist or not
    $stmt = $pdo->prepare('SELECT * FROM opningPosition WHERE name = ? AND address = ? AND id != ?');
    $stmt->execute([$position_name, $position_address, $positionId]);
    $career = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($career) {
      http_response_code(400);
      $response = array('success' => false, 'message' => "Position already exist");
      echo json_encode($response);
      exit();
    }

    $stmt = $pdo->prepare('UPDATE opningPosition SET name = ?, address = ? WHERE id = ?');
    $stmt->execute([$position_name, $position_address, $positionId]);
    $stmt = $pdo->prepare('SELECT * FROM opningPosition WHERE id = ?');
    $stmt->execute([$positionId]);
    $career = $stmt->fetch(PDO::FETCH_ASSOC);
    $response = array('success' => true, 'message' => "Career updated successfully", 'career' => $career);
    echo json_encode($response);
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
}

?>