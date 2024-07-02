<?php
session_start();

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

// Include necessary files
include './includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="./css/styles.css">
    <link rel="stylesheet" type="text/css" href="./css/category.css">
    <link rel="stylesheet" type="text/css" href="./css/subcategory.css">
    <link rel="stylesheet" type="text/css" href="./css/product.css">
    <link rel="stylesheet" type="text/css" href="./css/careers.css">
</head>
<body>
    <nav class="side-nav">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="Logo">
        </div>
        <ul>
            <li><a href="index.php?tab=category">Category</a></li>
            <li><a href="index.php?tab=subcategory">Subcategory</a></li>
            <li><a href="index.php?tab=products">Products</a></li>
            <li><a href="index.php?tab=careers">Careers</a></li>
        </ul>
    </nav>

    <div class="content">
        <?php
            $tab = isset($_GET['tab']) ? $_GET['tab'] : 'category';
            include("$tab.php");
        ?>
    </div>

</body>
</html>
