<?php

include ('smtp/PHPMailerAutoload.php');

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

  $mail = new PHPMailer(true);
  try {
    // Server settings
    $mail->SMTPDebug = 0; // Enable verbose debug output
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "harshil9915vasoya@gmail.com";
    $mail->Password = "fiummkhswgxudiso";
    $mail->SetFrom("harshil9915vasoya@gmail.com");// TCP port to connect to

    // Recipients
    $mail->SetFrom("harshil9915vasoya@gmail.com");
    $mail->addAddress('utsavbusa222@gmail.com', 'Utsav busa'); // Add a recipient
    $mail->addReplyTo($email, $name);
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    // Attachments
    $mail->addAttachment($pdf, $pdfName); // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); // Optional name

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