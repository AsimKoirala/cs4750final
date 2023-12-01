<?php
session_start();
require("connect-db.php");

// Fetch franchises from the database for filters
$queryFranchise = "SELECT Franchise_ID, Franchise_Name FROM Franchise";
$stmtFranchise = $db->prepare($queryFranchise);
$stmtFranchise->execute();
$franchises = $stmtFranchise->fetchAll();
$stmtFranchise->closeCursor();

// Initialize an empty array for selected franchises
$selectedFranchises = [];

// Check if the form has been submitted
if (isset($_POST['filter'])) {
    $selectedFranchises = $_POST['franchise'] ?? [];
}

// Construct the query with optional filters
$query = "SELECT Fighter.Fighter_ID, Fighter.Fighter_Name FROM Fighter ";
if (!empty($selectedFranchises)) {
    $query .= "JOIN Appears_In ON Fighter.Fighter_ID = Appears_In.Fighter_ID WHERE Appears_In.Franchise_ID IN (" . implode(',', array_map('intval', $selectedFranchises)) . ")";
}

$statement = $db->prepare($query);
$statement->execute();
$fighters = $statement->fetchAll();
$statement->closeCursor();

// Placeholder user ID for demonstration purposes
// Replace this with the actual logged-in user ID from your session or authentication logic
$user_id = $_SESSION['user_id'] ?? 1;

// Check if a bookmark button was pressed
if (isset($_POST['bookmark'])) {
    $fighter_id = $_POST['fighter_id'];
    // Check if already bookmarked
    $checkQuery = "SELECT 1 FROM Bookmarks WHERE User_ID = :user_id AND Fighter_ID = :fighter_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute(['user_id' => $user_id, 'fighter_id' => $fighter_id]);
    $isBookmarked = $checkStmt->fetchColumn();

    if ($isBookmarked) {
        // Remove bookmark
        $deleteQuery = "DELETE FROM Bookmarks WHERE User_ID = :user_id AND Fighter_ID = :fighter_id";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->execute(['user_id' => $user_id, 'fighter_id' => $fighter_id]);
    } else {
        // Add bookmark
        $insertQuery = "INSERT INTO Bookmarks (User_ID, Fighter_ID) VALUES (:user_id, :fighter_id)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute(['user_id' => $user_id, 'fighter_id' => $fighter_id]);
    }
    // Refresh the page to reflect bookmark changes
    header("Location: fighters.php");
    exit;
}

// Function to check if a fighter is bookmarked
function isBookmarked($db, $user_id, $fighter_id) {
    $query = "SELECT 1 FROM Bookmarks WHERE User_ID = :user_id AND Fighter_ID = :fighter_id";
    $stmt = $db->prepare($query);
    $stmt->execute(['user_id' => $user_id, 'fighter_id' => $fighter_id]);
    return $stmt->fetchColumn() ? true : false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fighters List</title>
    <!-- Add any additional CSS or JS here -->
    <style>
        /* Quick example style for filters */
        .filters {
            margin-bottom: 20px;
        }
        .filters form {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Fighters List</h1>
    <a href="bookmarks.php">My_Bookmarks</a> <!-- Link to the bookmarks page -->
    
    <!-- Filters Section -->
    <div class="filters">
        <form action="fighters.php" method="post">
            <?php foreach ($franchises as $franchise): ?>
                <label>
                    <input 
                        type="checkbox" 
                        name="franchise[]" 
                        value="<?= htmlspecialchars($franchise['Franchise_ID']) ?>"
                        <?= in_array($franchise['Franchise_ID'], $selectedFranchises) ? 'checked' : '' ?>
                    >
                    <?= htmlspecialchars($franchise['Franchise_Name']) ?>
                </label><br>
            <?php endforeach; ?>
            <input type="submit" name="filter" value="Filter">
        </form>
    </div>
    
    <!-- Fighters List -->
    <ul>
        <?php foreach ($fighters as $fighter): ?>
            <li>
                <a href="fighter-details.php?id=<?= htmlspecialchars($fighter['Fighter_ID']) ?>">
                    <?= htmlspecialchars($fighter['Fighter_Name']) ?>
                </a>
                <!-- Bookmark Button -->
                <form method="post" action="fighters.php">
                    <input type="hidden" name="fighter_id" value="<?= htmlspecialchars($fighter['Fighter_ID']) ?>">
                    <button type="submit" name="bookmark">
                        <?= isBookmarked($db, $user_id, $fighter['Fighter_ID']) ? 'Unbookmark' : 'Bookmark' ?>
                    </button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <a href="homepage.html">Back to Home</a>
</body>
</html>
