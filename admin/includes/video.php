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
    $videoStmt = $pdo->query('SELECT * FROM video ORDER BY id DESC');
    $videos = $videoStmt->fetchAll(PDO::FETCH_ASSOC);
    $response = array('success' => true, 'videos' => $videos);
    echo json_encode($response);
  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postData = file_get_contents('php://input');

  function convertToEmbedUrl($url)
  {
    parse_str(parse_url($url, PHP_URL_QUERY), $vars);
    return "https://www.youtube.com/embed/" . $vars['v'];
  }

  $jsonData = json_decode($postData, true);
  $videoUrl = convertToEmbedUrl($jsonData['url'] ?? '');

  try {
    $stmt = $pdo->prepare('INSERT INTO video (link) VALUES (?)');
    $stmt->execute([$videoUrl]);
    $response = array('success' => true, 'message' => "Video added successfully");
    echo json_encode($response);
  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $urlParts = explode('/', $_SERVER['REQUEST_URI']);
  $videoId = end($urlParts);

  if (!is_numeric($videoId)) {
    http_response_code(400); // Bad Request
    $response = array('success' => false, 'message' => "video Id is invalid");
    echo json_encode($response);
    exit;
  }

  try {

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM video WHERE id = ?');
    $stmt->execute([$videoId]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
      $response = array('success' => false, 'message' => "subCategory not found");
      echo json_encode($response);
      exit();
    }

    $stmt = $pdo->prepare('DELETE FROM video WHERE id = ?');
    $stmt->execute([$videoId]);
    $response = array('success' => true, 'message' => "Video deleted successfully");
    echo json_encode($response);
  } catch (PDOException $e) {
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
}

?>