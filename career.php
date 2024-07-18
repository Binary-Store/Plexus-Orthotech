<?php
require 'vendor/autoload.php'; // Make sure you have the correct path

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $phone = $_POST['contact'];
  $email = $_POST['email'];
  $country = $_POST['country'];
  $state = $_POST['state'];
  $city = $_POST['city'];
  $position = $_POST['position'];
  $pdf = $_FILES['pdf']['tmp_name'];
  $pdfName = $_FILES['pdf']['name'];

  if ($_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo "Error during file upload.";
    exit;
  }

  $mail = new PHPMailer();
  try {
    // Server settings
    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'harshil9915vasoya@gmail.com'; // SMTP username
    $mail->Password = 'fiummkhswgxudiso'; // SMTP password
    $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587; // TCP port to connect to

    // Recipients
    $mail->setFrom('harshil9915vasoya@gmail.com', $name);
    $mail->addAddress('utsavbusa222@gmail.com', 'Utsav busa'); // Add a recipient
    $mail->addReplyTo($email, $name);

    // Attachments
    $mail->addAttachment($pdf, $pdfName); // Add attachments

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'New Career Form Submission';
    $mail->Body = "<p><strong>Name:</strong> $name</p>
                       <p><strong>Phone:</strong> $phone</p>
                       <p><strong>Email:</strong> $email</p>
                       <p><strong>Country:</strong> $country</p>
                       <p><strong>State:</strong> $state</p>
                       <p><strong>City:</strong> $city</p>
                       <p><strong>Position:</strong> $position</p>";
    $mail->AltBody = "Name: $name\n
                          Phone: $phone\n
                          Email: $email\n
                          Country: $country\n
                          State: $state\n
                          City: $city\n
                          Position: $position";

    $mail->send();
    http_response_code(200);
    echo 'Message has been sent';
  } catch (Exception $e) {
    http_response_code(400);
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
}
?>