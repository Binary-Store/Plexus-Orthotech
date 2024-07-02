<?php
session_start();

include './includes/db.php';

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$type = isset($_SESSION['type']) ? $_SESSION['type'] : '';

// Clear session messages
unset($_SESSION['error']);
unset($_SESSION['success']);
unset($_SESSION['type']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['subcategory_name'], $_POST['category_id'])) {
        $_SESSION['type'] = "add-edit";
        $subcategoryName = $_POST['subcategory_name'];
        $categoryId = $_POST['category_id'];

        if (isset($_POST['subcategory_id']) && $_POST['subcategory_id'] !== '') {
            $subcategoryId = $_POST['subcategory_id'];

            // Check if the subcategory name already exists (excluding the current subcategory)
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM subcategories WHERE name = ? AND category_id = ? AND id != ?');
            $stmt->execute([$subcategoryName, $categoryId, $subcategoryId]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $_SESSION['error'] = "Subcategory name must be unique within the same category.";
            } else {
                try {
                    $stmt = $pdo->prepare('UPDATE subcategories SET name = ? WHERE id = ?');
                    $stmt->execute([$subcategoryName, $subcategoryId]);
                    $_SESSION['success'] = "Subcategory updated successfully.";
                } catch (PDOException $e) {
                    $_SESSION['error'] = "Error updating subcategory: " . $e->getMessage();
                }
            }
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM subcategories WHERE name = ? AND category_id = ?');
            $stmt->execute([$subcategoryName, $categoryId]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $_SESSION['error'] = "Subcategory name must be unique within the same category.";
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO subcategories (name, category_id) VALUES (?, ?)');
                    $stmt->execute([$subcategoryName, $categoryId]);
                    $_SESSION['success'] = "Subcategory added successfully.";
                } catch (PDOException $e) {
                    $_SESSION['error'] = "Error adding subcategory: " . $e->getMessage();
                }
            }
        }
    } elseif (isset($_POST['delete_subcategory_id'])) {
        $_SESSION['type'] = "delete";
        $subcategoryId = $_POST['delete_subcategory_id'];

        // Check if there are any products in the subcategory
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE subcategory_id = ?');
        $stmt->execute([$subcategoryId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['error'] = "Subcategory cannot be deleted because it contains products.";
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM subcategories WHERE id = ?');
                $stmt->execute([$subcategoryId]);
                $_SESSION['success'] = "Subcategory deleted successfully.";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error deleting subcategory: " . $e->getMessage();
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?tab=subcategory");
    exit();
}

// Fetch categories and their subcategories from the database
try {
    $stmt = $pdo->query('SELECT * FROM categories');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query('SELECT * FROM subcategories');
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subcategoriesByCategory = [];
    foreach ($subcategories as $subcategory) {
        $subcategoriesByCategory[$subcategory['category_id']][] = $subcategory;
    }
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<div class="subcategory-container">
    <div class="search-container">
        <input type="text" id="category-search" placeholder="Search categories...">
    </div>
    <?php foreach ($categories as $category) { ?>
        <div class="category-section" data-category-name="<?php echo strtolower($category['name']); ?>">
            <div class="subcategory-header-div">
                <h3 class="category-header" data-category-id="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></h3>
                <button class="add-subcategory-btn" data-category-id="<?php echo $category['id']; ?>">Add Subcategory</button>
            </div>
            <ul class="subcategory-list" id="subcategory-list-<?php echo $category['id']; ?>" style="display: none;">
                <?php
                if (isset($subcategoriesByCategory[$category['id']])) {
                    foreach ($subcategoriesByCategory[$category['id']] as $subcategory) { ?>
                        <li>
                            <span class="subcategory-name"><?php echo $subcategory['name']; ?></span>
                            <span class="subcategory-buttons">
                                <button class="edit-subcategory-btn" data-id="<?php echo $subcategory['id']; ?>" data-name="<?php echo $subcategory['name']; ?>" data-category-id="<?php echo $category['id']; ?>">Edit</button>
                                <button class="delete-subcategory-btn" data-id="<?php echo $subcategory['id']; ?>">Delete</button>
                            </span>
                        </li>
                    <?php }
                } ?>
            </ul>
        </div>
    <?php } ?>
</div>

<!-- Modal for adding/editing subcategory -->
<div id="add-subcategory-modal" class="modal <?php if (($error || $success) && $type == "add-edit") echo 'show'; ?>">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form id="add-subcategory-form" method="post">
            <input type="hidden" id="subcategory-id" name="subcategory_id">
            <input type="hidden" id="category-id" name="category_id">
            <input type="text" id="subcategory-name" name="subcategory_name" placeholder="Enter subcategory name" required>
            <button type="submit">Save Subcategory</button>
        </form>
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

<!-- Modal for delete confirmation -->
<div id="delete-confirmation-modal" class="modal <?php if (($error || $success) && $type == "delete") echo 'show'; ?>">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Are you sure you want to delete this subcategory?</p>
        <form id="delete-subcategory-form" method="post">
            <input type="hidden" id="delete-subcategory-id" name="delete_subcategory_id">
            <div>
                <button id="confirm-delete-btn" type="submit">Yes, Delete</button>
                <button type="button" id="cancel-delete-btn">Cancel</button>
            </div>
        </form>
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
        // Toggle subcategory list visibility when clicking category header
        var categoryHeaders = document.querySelectorAll(".category-header");
        categoryHeaders.forEach(function(header) {
            header.addEventListener("click", function() {
                var categoryId = this.getAttribute("data-category-id");
                var list = document.getElementById("subcategory-list-" + categoryId);
                list.style.display = (list.style.display === "none" || list.style.display === "") ? "block" : "none";
            });
        });

        // Open add subcategory modal and pre-fill category ID
        var addSubcategoryBtns = document.querySelectorAll(".add-subcategory-btn");
        addSubcategoryBtns.forEach(function(button) {
            button.addEventListener("click", function() {
                var categoryId = this.getAttribute("data-category-id");
                document.getElementById("category-id").value = categoryId;
                document.getElementById("add-subcategory-modal").style.display = "block";
                clearMessages();
            });
        });

        // Open edit subcategory modal and pre-fill data
        var editSubcategoryBtns = document.querySelectorAll(".edit-subcategory-btn");
        editSubcategoryBtns.forEach(function(button) {
            button.addEventListener("click", function() {
                var subcategoryId = this.getAttribute("data-id");
                var subcategoryName = this.getAttribute("data-name");
                var categoryId = this.getAttribute("data-category-id");

                document.getElementById("subcategory-id").value = subcategoryId;
                document.getElementById("category-id").value = categoryId;
                document.getElementById("subcategory-name").value = subcategoryName;

                document.getElementById("add-subcategory-modal").style.display = "block";
                clearMessages();
            });
        });

        // Open delete confirmation modal and set subcategory ID
        var deleteSubcategoryBtns = document.querySelectorAll(".delete-subcategory-btn");
        deleteSubcategoryBtns.forEach(function(button) {
            button.addEventListener("click", function() {
                var subcategoryId = this.getAttribute("data-id");
                document.getElementById("delete-subcategory-id").value = subcategoryId;
                document.getElementById("delete-confirmation-modal").style.display = "block";
                clearMessages();
            });
        });

        // Close modals
        var closeModalButtons = document.querySelectorAll(".close");
        closeModalButtons.forEach(function(button) {
            button.addEventListener("click", function() {
                this.parentElement.parentElement.style.display = "none";
            });
        });

        var searchInput = document.getElementById("category-search");

        searchInput.addEventListener("input", function() {
            var filter = searchInput.value.toLowerCase();
            var categorySections = document.querySelectorAll(".category-section");

            categorySections.forEach(function(section) {
                var categoryName = section.getAttribute("data-category-name");
                
                if (categoryName.includes(filter)) {
                    section.style.display = "";
                } else {
                    section.style.display = "none";
                }
            });
        });

        // Cancel delete
        document.getElementById("cancel-delete-btn").addEventListener("click", function() {
            document.getElementById("delete-confirmation-modal").style.display = "none";
        });

        

        // Clear messages
        function clearMessages() {
            var messageContainers = document.querySelectorAll(".message-container");
            messageContainers.forEach(function(container) {
                container.innerHTML = "";
            });
        }
    });
</script>
