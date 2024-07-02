<?php
session_start();

include './includes/db.php';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];

    try {
        // Fetch resume path for deletion
        $stmt = $pdo->prepare('SELECT resume_path FROM careers WHERE id = ?');
        $stmt->execute([$deleteId]);
        $career = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($career) {
            // Delete resume file
            $resumePath = $_SERVER['DOCUMENT_ROOT'] . '/Plexus-Orthotech/admin/includes/' . $career['resume_path'];
            if (file_exists($resumePath)) {
                unlink($resumePath);
            }
            // Delete the record from the database
            $stmt = $pdo->prepare('DELETE FROM careers WHERE id = ?');
            $stmt->execute([$deleteId]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Career application not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

try {
    $stmt = $pdo->query('SELECT * FROM careers ORDER BY created_at DESC');
    $careers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers</title>
    <link rel="stylesheet" href="careers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="careers-container">
        <h1>Career Applications</h1>
        <div class="table-responsive">
            <table class="careers-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Resume</th>
                        <th>Applied On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($careers as $career) { ?>
                        <tr data-id="<?php echo htmlspecialchars($career['id']); ?>">
                            <td><?php echo htmlspecialchars($career['name']); ?></td>
                            <td><?php echo htmlspecialchars($career['email']); ?></td>
                            <td><?php echo htmlspecialchars($career['contact']); ?></td>
                            <td><a href="<?php echo htmlspecialchars('./includes/' . $career['resume_path']); ?>" target="_blank"><i class="fas fa-file-pdf"></i> View Resume</a></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($career['created_at']))); ?></td>
                            <td><button class="delete-button" style="background: #d32f2f; color: white; border: none; padding: 5px 10px; cursor: pointer;">Delete</button></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.delete-button').on('click', function() {
                if (confirm('Are you sure you want to delete this application?')) {
                    var row = $(this).closest('tr');
                    var id = row.data('id');

                    $.ajax({
                        url: 'careers.php',
                        type: 'POST',
                        data: { delete_id: id },
                        success: function(response) {
                            var result = JSON.parse(response);
                            if (result.success) {
                                row.remove();
                            } else {
                                alert('Error: ' + result.error);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', error);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
