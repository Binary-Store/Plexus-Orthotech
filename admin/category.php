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
    if (isset($_POST['category_name'])) {
        $_SESSION['type'] = "add-edit";
        $categoryName = $_POST['category_name'];

        if (isset($_POST['category_id']) && $_POST['category_id'] !== '') {
            $categoryId = $_POST['category_id'];

            try {
                // Check if category name already exists, excluding the current category being updated
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?');
                $stmt->execute([$categoryName, $categoryId]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    $_SESSION['error'] = "Category name '$categoryName' already exists.";
                } else {
                    // Update category
                    $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
                    $stmt->execute([$categoryName, $categoryId]);
                    $_SESSION['success'] = "Category updated successfully.";
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error updating category: " . $e->getMessage();
            }
        } else {
            try {
                // Check if category name already exists for new category insert
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
                $stmt->execute([$categoryName]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    $_SESSION['error'] = "Category name '$categoryName' already exists.";
                } else {
                    // Insert new category
                    $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
                    $stmt->execute([$categoryName]);
                    $_SESSION['success'] = "Category added successfully.";
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error adding category: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_category_id'])) {
        $_SESSION['type'] = "delete";
        $categoryId = $_POST['delete_category_id'];

        // Check if there are any subcategories in the category
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM subcategories WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['error'] = "Category cannot be deleted because it contains subcategories.";
        } else {
            try {
                // Delete category
                $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
                $stmt->execute([$categoryId]);
                $_SESSION['success'] = "Category deleted successfully.";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?tab=category");
    exit();
}

// Fetch categories from the database
try {
    $stmt = $pdo->query('SELECT * FROM categories');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}
?>

<div class="category-container">
    <div class="category-header-div">
        <h2>Categories</h2>
        <button id="add-category-btn">Add Category</button>
    </div>
    <ul class="category-list">
        <?php foreach ($categories as $category) { ?>
            <li class="category-item">
                <span class="category-name"><?php echo $category['name']; ?></span>
                <span class="category-buttons">
                    <button class="edit-category-btn" data-id="<?php echo $category['id']; ?>" data-name="<?php echo $category['name']; ?>">Edit</button>
                    <button class="delete-category-btn" data-id="<?php echo $category['id']; ?>">Delete</button>
                </span>
            </li>
        <?php } ?>
    </ul>
</div>

<!-- Modal for adding/editing category -->
<div id="add-category-modal" class="modal <?php if (($error || $success) && $type=="add-edit") echo 'show'; ?>">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form id="add-category-form" method="post">
            <input type="hidden" id="category-id" name="category_id">
            <input type="text" id="category-name" name="category_name" placeholder="Enter category name" required>
            <button type="submit">Save Category</button>
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
<div id="delete-category-modal" class="modal <?php if (($error || $success) && $type=="delete") echo 'show'; ?>">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Are you sure you want to delete this category?</p>
        <form id="delete-category-form" method="post">
            <input type="hidden" id="delete-category-id" name="delete_category_id">
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
    document.addEventListener('DOMContentLoaded', (event) => {
        const modal = document.getElementById('add-category-modal');
        const deleteModal = document.getElementById('delete-category-modal');
        var closeModalButtons = document.querySelectorAll(".close");
        const addCategoryBtn = document.getElementById('add-category-btn');
        const categoryForm = document.getElementById('add-category-form');
        const categoryIdInput = document.getElementById('category-id');
        const categoryNameInput = document.getElementById('category-name');
        const editButtons = document.querySelectorAll('.edit-category-btn');
        const deleteButtons = document.querySelectorAll('.delete-category-btn');
        const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
        var messageContainers = document.querySelectorAll(".message-container");

        addCategoryBtn.addEventListener('click', () => {
            categoryIdInput.value = '';
            categoryNameInput.value = '';
            document.getElementById("add-category-modal").style.display = "block";
            clearMessages();
        });

        closeModalButtons.forEach(function(button) {
            button.addEventListener("click", function() {
                this.parentElement.parentElement.style.display = "none";
            });
        });

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        });

        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                categoryIdInput.value = button.getAttribute('data-id');
                categoryNameInput.value = button.getAttribute('data-name');
                document.getElementById("add-category-modal").style.display = "block";
                clearMessages();
            });
        });

        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const categoryId = button.getAttribute('data-id');
                document.getElementById('delete-category-id').value = categoryId;
                document.getElementById("delete-category-modal").style.display = "block";
                clearMessages();
            });
        });

        cancelDeleteBtn.addEventListener('click', () => {
            deleteModal.classList.remove('show');
        });

        function clearMessages() {
            messageContainers.forEach(function(container) {
                container.innerHTML = "";
            });
        }
    });
</script>
