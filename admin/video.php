<?php
session_start();

include './includes/db.php';

function convertToEmbedUrl($url) {
    parse_str(parse_url($url, PHP_URL_QUERY), $vars);
    return "https://www.youtube.com/embed/" . $vars['v'];
}

// Handle adding new video link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_Link'])) {
    $video_Link = $_POST['video_Link'];
    $embedUrl = convertToEmbedUrl($video_Link);
    
    try {
        $stmt = $pdo->prepare('INSERT INTO video_Link (video_Link) VALUES (?)');
        $stmt->execute([$embedUrl]);
    } catch (PDOException $e) {
        die("Error adding video link: " . $e->getMessage());
    }
}

// Handle deleting a video link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    
    try {
        $stmt = $pdo->prepare('DELETE FROM video_Link WHERE id = ?');
        $stmt->execute([$delete_id]);
    } catch (PDOException $e) {
        die("Error deleting video link: " . $e->getMessage());
    }
    // Redirect to avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF'] . "?tab=video");
    exit();
}

// Fetch video links from the database
try {
    $stmt = $pdo->query('SELECT * FROM video_Link ORDER BY id DESC');
    $videoLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching video links: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Links</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .input-box {
            width: calc(100% - 110px);
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px 0 0 4px;
            outline: none;
        }
        .add-box {
            width: 100px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            font-size: 16px;
        }
        .add-box:hover {
            background-color: #45a049;
        }
        .video-list {
            margin-top: 20px;
        }
        .card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            position: relative;
        }
        .ratio-16x9 {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
        }
        .ratio-16x9 iframe {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            border: none;
            border-radius: 8px;
        }
        .delete-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .delete-button:hover {
            background-color: #d32f2f;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2), 0 6px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .modal-buttons {
            margin-top: 20px;
        }
        .confirm-delete {
            background-color: #f44336 !important;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        .confirm-delete:hover {
            background-color: #d32f2f;
        }
        .cancel-delete {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .cancel-delete:hover {
            background-color: #0b7dda;
        }
        .confirm-delete{
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Video Links</h1>
        <form method="POST" action="">
            <input type="text" name="video_Link" class="input-box" placeholder="Paste YouTube video link here" required>
            <button type="submit" class="add-box">Add Video</button>
        </form>

        <div class="video-list">
            <?php foreach ($videoLinks as $videoLink) { ?>
                <div class="card">
                    <div class="ratio ratio-16x9">
                        <iframe width="560" height="315" src="<?php echo htmlspecialchars($videoLink['video_Link']); ?>"
                            title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    <form method="POST" action="" class="delete-form">
                        <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($videoLink['id']); ?>">
                        <button type="button" class="delete-button">Delete</button>
                        <!-- Modal for confirmation -->
                        <div id="myModal_<?php echo $videoLink['id']; ?>" class="modal">
                            <div class="modal-content">
                                <p>Are you sure you want to delete this video link?</p>
                                <div class="modal-buttons">
                                    <button type="submit" class="confirm-delete">Confirm Delete</button>
                                    <button type="button" class="cancel-delete">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        // JavaScript to handle delete button confirmation
        const deleteForms = document.querySelectorAll('.delete-form');

        deleteForms.forEach(form => {
            const deleteButton = form.querySelector('.delete-button');
            const modal = document.getElementById('myModal_' + form.querySelector('[name="delete_id"]').value);
            const cancelDelete = modal.querySelector('.cancel-delete');

            deleteButton.addEventListener('click', () => {
                modal.style.display = 'block';
            });

            cancelDelete.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        });
    </script>
</body>
</html>
