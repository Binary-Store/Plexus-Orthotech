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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>
    <nav class="side-nav">
        <div class="logo">
            <a href="../../index.html" target="_blank">
            <img src="../assets/images/logo.png" alt="Logo" ></a>
        </div>
        <ul>

            <li><a href="index.php?tab=category">Category</a></li>
            <li><a href="index.php?tab=subcategory">Subcategory</a></li>
            <li><a href="index.php?tab=products">Products</a></li>
            <li><a href="index.php?tab=careers">Careers</a></li>
            <li><a href="index.php?tab=video">Video</a></li>

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
