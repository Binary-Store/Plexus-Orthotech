<?php

require 'vendor/autoload.php'; // Make sure you have the correct path

use PHPMailer\PHPMailer\PHPMailer;

$data = json_decode(file_get_contents("php://input"), true);

// Validate and sanitize data (add more validation as needed)
$name = isset($data['name']) ? htmlspecialchars(trim($data['name'])) : '';
$number = isset($data['number']) ? htmlspecialchars(trim($data['number'])) : '';
$email = isset($data['email']) ? filter_var($data['email'], FILTER_VALIDATE_EMAIL) : '';
$message = isset($data['message']) ? htmlspecialchars(trim($data['message'])) : '';
$profession = isset($data['profession']) ? htmlspecialchars(trim($data['profession'])) : '';
$subject = isset($data['subject']) ? htmlspecialchars(trim($data['subject'])) : '';
$city = isset($data['city']) ? htmlspecialchars(trim($data['city'])) : '';
$country = isset($data['country']) ? htmlspecialchars(trim($data['country'])) : '';
$state = isset($data['state']) ? htmlspecialchars(trim($data['state'])) : '';




if (!empty($name)) {
    $html .= "Name: <b>" . $name . "</b><br>";
    $pdfFilePath = $name . '_' . time() . '.pdf';
}
if (!empty($number)) {
    $html .= "Mobile No: " . $number . "<br>";
}
if (!empty($email)) {
    $html .= "Email: " . $email . "<br>";
}
if (!empty($message)) {
    $html .= "Message: " . $message . "<br>";
}
if (!empty($profession)) {
    $html .= "Profession: " . $profession . "<br>";
}
if (!empty($country)) {
    $html .= "Country: " . $country . "<br>";
}
if (!empty($city)) {
    $html .= "City: " . $city . "<br>";
}
if (!empty($state)) {
    $html .= "State: " . $state . "<br>";
}


echo smtp_mailer('utsavbusa222@gmail.com', $subject, $html);

function smtp_mailer($to, $subject, $msg)
{
    $mail = new PHPMailer();
    // $mail->SMTPDebug  = 3;
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "harshil9915vasoya@gmail.com";
    $mail->Password = "fiummkhswgxudiso";
    $mail->SetFrom("harshil9915vasoya@gmail.com");
    $mail->Subject = $subject;
    $mail->Body = $msg;
    $mail->AddAddress($to);


    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false
        )
    );

    if (!$mail->Send()) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Error sending email']);
    } else {
        http_response_code(200); // OK
        $pdfData = isset($pdfData) ? $pdfData : 'hellon world';
        echo json_encode(['status' => 'success', 'message' => 'Email sent successfully', 'data' => $pdfData]);
    }


}
?>