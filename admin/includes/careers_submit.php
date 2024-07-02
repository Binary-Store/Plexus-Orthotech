<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $resume = $_FILES['resume'] ?? [];

    // Validate inputs
    if (empty($name) || empty($email) || empty($contact) || empty($resume)) {
        echo "Please fill in all required fields.";
        exit;
    }

    // Validate file upload
    if ($resume['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($resume['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['pdf']; // Add more if needed
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        // Check file extension
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            echo "Only PDF files are allowed.";
            exit;
        }

        // Check file size
        if ($resume['size'] > $maxFileSize) {
            echo "File size exceeds maximum limit (5 MB).";
            exit;
        }

        // Generate unique file name
        $resumeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $name) . '_' . time() . '.' . $extension;
        $resumePath = 'uploads/resumes/' . $resumeName;

        // Move the uploaded file
        if (move_uploaded_file($resume['tmp_name'], $resumePath)) {
            try {
                // Insert data into the database
                $stmt = $pdo->prepare('INSERT INTO careers (name, email, contact, resume_path) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $email, $contact, $resumePath]);
                echo "Application submitted successfully.";
            } catch (PDOException $e) {
                echo "Error saving data: " . $e->getMessage();
            }
        } else {
            echo "Error uploading resume.";
        }
    } else {
        echo "Error with the uploaded file.";
    }
}
?>
