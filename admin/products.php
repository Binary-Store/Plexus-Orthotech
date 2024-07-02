<?php
session_start();
include './includes/db.php';

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$type = isset($_SESSION['type']) ? $_SESSION['type'] : '';

unset($_SESSION['error']);
unset($_SESSION['success']);
unset($_SESSION['type']);

$showModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name'])) {
    $productName = $_POST['product_name'];
    $productDescription = $_POST['product_description'];
    $categoryId = $_POST['category_id'];
    $subcategoryId = $_POST['subcategory_id'];
    $imagePath = null;
    $_SESSION['type'] = "add-edit";

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image = $_FILES['product_image'];
        $imageName = uniqid() . '-' . basename($image['name']);
        $imagePath = 'images/' . $imageName;

        if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
            $error = 'Error uploading image.';
        }
    }

    if (!$error) {
        if (isset($_POST['product_id']) && $_POST['product_id'] !== '') {
            $productId = $_POST['product_id'];

            // Fetch the current image path from the database
            $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($currentProduct) {
                $currentImagePath = $currentProduct['image'];
            }

            // If no new image is provided, use the current image path
            if (!$imagePath) {
                $imagePath = $currentImagePath;
            }

            try {
                $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, image = ?, category_id = ?, subcategory_id = ? WHERE id = ?');
                $stmt->execute([$productName, $productDescription, $imagePath, $categoryId, $subcategoryId, $productId]);
                $_SESSION['success'] = "Product updated successfully.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Product name must be unique within the same subcategory.";
                } else {
                    $error = "Error updating product: " . $e->getMessage();
                }
            }
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO products (name, description, image, category_id, subcategory_id) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$productName, $productDescription, $imagePath, $categoryId, $subcategoryId]);
                $_SESSION['success'] = "Product added successfully.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Product name must be unique within the same subcategory.";
                } else {
                    $error = "Error adding product: " . $e->getMessage();
                }
            }
        }
    }

    if ($error) {
        $showModal = true;
        $_SESSION['error'] = $error;
    }
    header("Location: /Plexus-Orthotech/admin/index.php?tab=products");
    exit();
}

if (isset($_GET['delete'])) {
    $productId = $_GET['delete'];
    $_SESSION['type'] = "delete";
    try {
        $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $_SESSION['success'] = "Product deleted successfully.";
        if ($product['image']) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Plexus-Orthotech/admin/' . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
    }

    header("Location: /Plexus-Orthotech/admin/index.php?tab=products");
    exit();
}

try {
    $stmt = $pdo->query('SELECT p.*, c.name as category_name, s.name as subcategory_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN subcategories s ON p.subcategory_id = s.id');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query('SELECT * FROM categories');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query('SELECT * FROM subcategories');
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/product.css">
    <title>Product Management</title>
</head>
<body>
<div class="container">
    <div class="product-header">
        <input type="text" id="product-search" placeholder="Search for products...">
        <button id="add-product-btn">Add Product</button>
    </div>

    <div class="product-grid" id="product-grid">
        <?php foreach ($products as $product) { ?>
            <div class="product-card">
                <img src="<?php echo $product['image'] ?: 'placeholder.png'; ?>" alt="Product Image">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <p>Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                <p>Subcategory: <?php echo htmlspecialchars($product['subcategory_name']); ?></p>
                <button class="edit-product-btn" data-id="<?php echo $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-description="<?php echo htmlspecialchars($product['description']); ?>" data-category-id="<?php echo $product['category_id']; ?>" data-subcategory-id="<?php echo $product['subcategory_id']; ?>" data-image="<?php echo $product['image']; ?>">Edit</button>
                <button class="delete-product-btn" data-id="<?php echo $product['id']; ?>">Delete</button>
            </div>
        <?php } ?>
    </div>
</div>

<div id="add-product-modal" class="modal <?php if (($error || $success) && $type == "add-edit") echo 'show'; ?>">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form id="add-product-form" action="index.php?tab=products" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="product-id" name="product_id" value="<?php echo isset($_POST['product_id']) ? htmlspecialchars($_POST['product_id']) : ''; ?>">
            <input type="hidden" id="current-image" name="current_image" value="">
            <input type="text" id="product-name" name="product_name" placeholder="Product Name" value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>" required>
            <textarea id="product-description" name="product_description" placeholder="Product Description" required><?php echo isset($_POST['product_description']) ? htmlspecialchars($_POST['product_description']) : ''; ?></textarea>
            <input type="file" id="product-image" name="product_image" accept="image/*">
            <select id="category-id" name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php } ?>
            </select>
            <select id="subcategory-id" name="subcategory_id" required>
                <option value="">Select Subcategory</option>
                <?php foreach ($subcategories as $subcategory) { ?>
                    <option value="<?php echo $subcategory['id']; ?>" data-category-id="<?php echo $subcategory['category_id']; ?>" <?php echo isset($_POST['subcategory_id']) && $_POST['subcategory_id'] == $subcategory['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($subcategory['name']); ?></option>
                <?php } ?>
            </select>
            <button type="submit">Save Product</button>
        </form>
        <div class="message-container">
            <?php if ($error) { ?>
                <p class="error"><?php echo $error; ?></p>
            <?php } elseif ($success) { ?>
                <p class="success"><?php echo $success; ?></p>
            <?php } ?>
        </div>
    </div>
</div>

<div id="delete-confirm-modal" class="modal <?php if (($error || $success) && $type == "delete") echo 'show'; ?>">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Are you sure you want to delete this product?</p>
        <button id="confirm-delete-btn">Yes</button>
        <button id="cancel-delete-btn">No</button>

        <div class="message-container">
            <?php
            if ($error) {
                echo "<p class='error'>$error</p>";
            } elseif ($success) {
                echo "<p class='success'>$success</p>";
            }
            ?>
        </div>
    </div>
    
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var addProductBtn = document.getElementById("add-product-btn");
        var addProductModal = document.getElementById("add-product-modal");
        var deleteConfirmModal = document.getElementById("delete-confirm-modal");
        var closeModalBtns = document.querySelectorAll(".close");
        var confirmDeleteBtn = document.getElementById("confirm-delete-btn");
        var cancelDeleteBtn = document.getElementById("cancel-delete-btn");

        var deleteProductId = null;

        addProductBtn.addEventListener("click", function() {
            document.getElementById("add-product-form").reset();
            addProductModal.style.display = "block";
        });

        closeModalBtns.forEach(function(btn) {
            btn.addEventListener("click", function() {
                addProductModal.style.display = "none";
                deleteConfirmModal.style.display = "none";
                clearMessages();
            });
        });

        cancelDeleteBtn.addEventListener("click", function() {
            deleteConfirmModal.style.display = "none";
        });

        confirmDeleteBtn.addEventListener("click", function() {
            if (deleteProductId) {
                window.location.href = "index.php?tab=products&delete=" + deleteProductId;
            }
        });

        var editProductBtns = document.querySelectorAll(".edit-product-btn");
        editProductBtns.forEach(function(button) {
            button.addEventListener("click", function() {
                var productId = this.getAttribute("data-id");
                var productName = this.getAttribute("data-name");
                var productDescription = this.getAttribute("data-description");
                var categoryId = this.getAttribute("data-category-id");
                var subcategoryId = this.getAttribute("data-subcategory-id");
                var imageUrl = this.getAttribute("data-image");

                document.getElementById("product-id").value = productId;
                document.getElementById("product-name").value = productName;
                document.getElementById("product-description").value = productDescription;
                document.getElementById("category-id").value = categoryId;
                document.getElementById("subcategory-id").value = subcategoryId;
                document.getElementById("current-image").value = imageUrl;

                addProductModal.style.display = "block";
            });
        });

        var deleteProductBtns = document.querySelectorAll(".delete-product-btn");
        deleteProductBtns.forEach(function(button) {
            button.addEventListener("click", function() {
                deleteProductId = this.getAttribute("data-id");
                deleteConfirmModal.style.display = "block";
            });
        });

        function clearMessages() {
            var messageContainers = document.querySelectorAll(".message-container");
            messageContainers.forEach(function(container) {
                container.innerHTML = "";
            });
        }

        var categorySelect = document.getElementById("category-id");
        var subcategorySelect = document.getElementById("subcategory-id");

        categorySelect.addEventListener("change", function() {
            var selectedCategoryId = this.value;
            var subcategoryOptions = subcategorySelect.querySelectorAll("option");

            subcategoryOptions.forEach(function(option) {
                if (option.getAttribute("data-category-id") === selectedCategoryId) {
                    option.style.display = "block";
                } else {
                    option.style.display = "none";
                }
            });

            subcategorySelect.value = '';
        });

        // Search Functionality
        var searchInput = document.getElementById("product-search");

        searchInput.addEventListener("input", function() {
            var filter = searchInput.value.toLowerCase();
            var productCards = document.querySelectorAll(".product-card");

            productCards.forEach(function(card) {
                var name = card.querySelector("h3").textContent.toLowerCase();
                var category = card.querySelector("p:nth-of-type(2)").textContent.toLowerCase();
                var subcategory = card.querySelector("p:nth-of-type(3)").textContent.toLowerCase();

                if (name.includes(filter) || category.includes(filter) || subcategory.includes(filter)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
    });
</script>
</body>
</html>
