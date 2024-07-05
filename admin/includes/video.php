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

  function convertToEmbedURL($url)
  {
    $parsed_url = parse_url($url);

    // Ensure the URL has a query component
    if (!isset($parsed_url['query'])) {
      return null; // Invalid URL format
    }

    // Parse the query parameters
    parse_str($parsed_url['query'], $query_params);

    // Ensure the 'v' parameter is present
    if (!isset($query_params['v'])) {
      return null; // Invalid URL format
    }

    // Extract the video ID
    $videoID = $query_params['v'];

    // Construct the embed URL
    $embedURL = "https://www.youtube.com/embed/$videoID";

    // Check if there is a playlist
    if (isset($query_params['list'])) {
      $listID = $query_params['list'];
      $embedURL .= "?list=$listID";
    }

    return $embedURL;
  }

  $jsonData = json_decode($postData, true);
  $videoUrl = convertToEmbedUrl($jsonData['video_url'] ?? '');

  try {
    $stmt = $pdo->prepare('INSERT INTO video (link) VALUES (?)');
    $stmt->execute([$videoUrl]);

    $videoId = $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT * FROM video WHERE id = ?');
    $stmt->execute([$videoId]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = array('success' => true, 'message' => "Video added successfully", 'videos' => $video);
    echo json_encode($response);
  } catch (PDOException $e) {
    http_response_code(505);
    $response = array('success' => false, 'message' => $e->getMessage());
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
    http_response_code(505);
    $response = array('success' => false, 'message' => "Something went wrong");
    echo json_encode($response);
    exit();
  }
}

?>