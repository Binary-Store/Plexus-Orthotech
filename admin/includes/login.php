<?php
require "variables.php";
require "db.php";
require "vendor/autoload.php";
use Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $mobileNumber = $data['mobileNumber'] ?? '';
    $password = trim($data['password']) ?? '';


    if (empty($mobileNumber) || empty($password)) {
        http_response_code(400);
        echo json_encode(array('error' => 'Mobile number and password are required'));
        exit();
    }



    if ($mobileNumber === '9427217406' && $password === 'admin') {

        $payload = array(
            "iss" => $web_url,
            "name" => 'Admin',
            "exp" => time() + 86400
        );

        $jwt = JWT::encode($payload, $key, "HS256");
        $response = array('success' => true, 'token' => $jwt, "message" => "Login Successfull");
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(array('success' => false, "message" => "Invalid Credentials"));
    }
}
?>