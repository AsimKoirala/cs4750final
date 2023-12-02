<?php
session_start();
require("connect-db.php");

$user_id = $_SESSION['user_id']; // Replace with your session variable that holds the logged-in user's ID

// Fetch bookmarked fighters
$query = "SELECT Fighter.Fighter_ID, Fighter.Fighter_Name 
          FROM Bookmarks 
          JOIN Fighter ON Bookmarks.Fighter_ID = Fighter.Fighter_ID 
          WHERE Bookmarks.User_ID = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$bookmarkedFighters = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookmarked Fighters</title>
    <!-- Add any additional CSS or JS here -->
</head>
<body>
    <h1>Bookmarked Fighters</h1>
    <ul>
        <?php foreach ($bookmarkedFighters as $fighter): ?>
            <li>
                <a href="fighter-details.php?id=<?= htmlspecialchars($fighter['Fighter_ID']) ?>">
                    <?= htmlspecialchars($fighter['Fighter_Name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="fighters.php">Back to Fighters List</a>
</body>
</html>
