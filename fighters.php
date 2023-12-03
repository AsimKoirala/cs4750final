
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="background.css">
    <meta charset="UTF-8">
    <title>Fighters List</title>
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

<nav class="navbar bg-dark border-bottom border-body" data-bs-theme="dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="homepage.html">Smash Bros Catalog</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link"href="homepage.html">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="profilepage.php">Profile Page</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="fighters.php">Characters</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="create_character.php">Create a Character</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="bookmarks.php">Bookmarks</a>
          </li>
        </ul>
  
      </div>
    </div>
  </nav>
  <body>
    <div class="container">
        <h1 class="mb-4">Fighters List</h1>
        
        <div class="row">
            <div class="col-lg-8 col-md-7 col-sm-12">
                <ul class="list-group">
                    <?php foreach ($fighters as $fighter): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
             
                                <?= htmlspecialchars($fighter['Fighter_Name']) ?>
                            
                            <form method="post" action="fighters.php">
                                <input type="hidden" name="fighter_id" value="<?= htmlspecialchars($fighter['Fighter_ID']) ?>">
                                <a class="btn btn-outline-secondary btn-sm" href="fighter-details.php?id=<?= htmlspecialchars($fighter['Fighter_ID']) ?>">View Details</a>
                                <button type="submit" name="bookmark" class="btn btn-outline-secondary btn-sm">
                                    <?= isBookmarked($db, $user_id, $fighter['Fighter_ID']) ? 'Unbookmark' : 'Bookmark' ?>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Filters - Right Side -->
            <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="filters mb-3">
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
                        <input type="submit" name="filter" value="Filter" class="btn btn-primary mt-2">
                        </form>
            </div>
        </div>
    </div>
</div>
<br></br>
</body>

</html>
