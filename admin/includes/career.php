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
    $careerStmt = $pdo->query('SELECT * FROM careers ORDER BY created_at DESC');
    $careers = $careerStmt->fetchAll(PDO::FETCH_ASSOC);
    $response = array('success' => true, 'careers' => $careers);
    echo json_encode($response);
  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

  $urlParts = explode('/', $_SERVER['REQUEST_URI']);
  $careerId = end($urlParts);

  try {
    $stmt = $pdo->prepare('DELETE FROM careers WHERE id = ?');
    $stmt->execute([$careerId]);
    $response = array('success' => true, 'message' => "Career deleted successfully");
    echo json_encode($response);
  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
}

?>