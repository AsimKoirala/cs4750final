<?php
session_start();
require("connect-db.php");

$user_id = $_SESSION['user_id'] ?? null;
$fighter_id = $_GET['id'] ?? null; // Assuming Fighter_ID is passed via GET request

if (!$user_id || !$fighter_id) {
    // Redirect or handle the error
    header("Location: login.php");
    exit;
}

// Check if the logged-in user created the fighter
$stmt = $db->prepare("SELECT COUNT(*) FROM Creates WHERE User_ID = ? AND Fighter_ID = ?");
$stmt->execute([$user_id, $fighter_id]);
$is_creator = $stmt->fetchColumn() > 0;

// HTML for Edit and Delete buttons (only shown if the user is the creator)


// Handle the delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'delete') {
    $fighter_id_to_delete = $_POST['fighter_id'];

    try {
        // Start transaction
        $db->beginTransaction();
    
        // Deleting references in the specified order
        $db->exec("DELETE FROM Appears_In WHERE Fighter_ID = $fighter_id_to_delete");
        $db->exec("DELETE FROM Bookmarks WHERE Fighter_ID = $fighter_id_to_delete");
        $db->exec("DELETE FROM Can_Do WHERE Fighter_ID = $fighter_id_to_delete");
        $db->exec("DELETE FROM Is_Built_With WHERE Fighter_ID = $fighter_id_to_delete");
        $db->exec("DELETE FROM Creates WHERE Fighter_ID = $fighter_id_to_delete");

        // Delete from Characteristics and Movesets
        $stmt = $db->prepare("SELECT Move_ID FROM Fighter WHERE Fighter_ID = ?");
        $stmt->execute([$fighter_id_to_delete]);
        $move_id = $stmt->fetchColumn();

        // Retrieve Franchise_ID and Move_ID for the fighter
        $stmt = $db->prepare("SELECT Franchise_ID, Move_ID FROM Fighter WHERE Fighter_ID = ?");
        $stmt->execute([$fighter_id_to_delete]);
        $fighter_info = $stmt->fetch(PDO::FETCH_ASSOC);


        $db->exec("DELETE FROM Characteristics WHERE Fighter_ID = $fighter_id_to_delete");
        // Finally, delete the fighter
        $db->exec("DELETE FROM Fighter WHERE Fighter_ID = $fighter_id_to_delete");
        $db->exec("DELETE FROM Movesets WHERE Move_ID = $move_id");
        
        // Check if the franchise is used by other fighters
        $stmt = $db->prepare("SELECT COUNT(*) FROM Fighter WHERE Franchise_ID = ?");
        $stmt->execute([$fighter_info['Franchise_ID']]);
        $franchise_use_count = $stmt->fetchColumn();

        // If this is the only fighter using the franchise, delete the franchise
        if ($franchise_use_count <= 1) {
            $db->exec("DELETE FROM Franchise WHERE Franchise_ID = {$fighter_info['Franchise_ID']}");
        }

        $db->commit();
        header("Location: fighters.php"); // Redirect after successful deletion
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        echo "Error deleting fighter: " . $e->getMessage();
    }
}


// Check if the Fighter_ID is passed in the URL
if (isset($_GET['id'])) {
    $fighter_id = htmlspecialchars($_GET['id']);

    // Query to fetch all related data for the fighter
    $queryDetails = "SELECT F.Fighter_Name, Fr.Franchise_Name, M.*, C.*
                     FROM Fighter F
                     INNER JOIN Franchise Fr ON F.Franchise_ID = Fr.Franchise_ID
                     INNER JOIN Movesets M ON F.Move_ID = M.Move_ID
                     INNER JOIN Characteristics C ON F.Fighter_ID = C.Fighter_ID
                     WHERE F.Fighter_ID = :fighter_id";

    $stmtDetails = $db->prepare($queryDetails);
    $stmtDetails->bindValue(':fighter_id', $fighter_id);
    $stmtDetails->execute();
    $details = $stmtDetails->fetch(PDO::FETCH_ASSOC);
    $stmtDetails->closeCursor();

} else {
    // Redirect back to fighters page or handle the error appropriately
    header("Location: fighters.php");
    exit;
}

?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="background.css">

    <title>Fighter Details</title>
    <!-- Add additional CSS or JS here -->
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
    <div class="container my-4">
        <div class="card my-3">
            <div class="card-body">
                <h2 class="card-title"><?= htmlspecialchars($details['Fighter_Name']) ?> Details</h2>
                <h4>Franchise: <?= htmlspecialchars($details['Franchise_Name']) ?></h4>
            </div>
        </div>

        <?php
        if ($is_creator) {
            echo "<a class='btn btn-secondary btn-sm' href='edit_fighter.php?fighter_id=$fighter_id'>Edit Fighter</a><br>"; 
            echo "<form method='post'>
                    <input type='hidden' name='action' value='delete'>
                    <input type='hidden' name='fighter_id' value='$fighter_id'>
                    <input type='submit' class='btn btn-danger btn-sm' value='Delete Fighter'>
                  </form>"; 
        }
        ?>

        <div class="row">
            <!-- First Row -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3>Moveset:</h3>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">Up B: <?= htmlspecialchars($details['Up_B']) ?></li>
                            <li class="list-group-item">Down B: <?= htmlspecialchars($details['Down_B']) ?></li>
                            <li class="list-group-item">Side B: <?= htmlspecialchars($details['Side_B']) ?></li>
                            <li class="list-group-item">Neutral B: <?= htmlspecialchars($details['Neutral_B']) ?></li>
                            <li class="list-group-item">Neutral A: <?= htmlspecialchars($details['Neutral_A']) ?></li>
                            <li class="list-group-item">Down A: <?= htmlspecialchars($details['Down_A']) ?></li>
                            <li class="list-group-item">Up A: <?= htmlspecialchars($details['Up_A']) ?></li>
                            <li class="list-group-item">Side A: <?= htmlspecialchars($details['Side_A']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3>Characteristics:</h3>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">Gender: <?= htmlspecialchars($details['Gender']) ?></li>
                            <li class="list-group-item">Weight: <?= htmlspecialchars($details['Weight_Value']) ?></li>
                            <li class="list-group-item">Height: <?= htmlspecialchars($details['Height']) ?></li>
                            <li class="list-group-item">Speed: <?= htmlspecialchars($details['Fast']) ?> (on a scale of 1 to 5)</li>
                        </ul>
                    </div>
                </div>
            </div>

          
          
        </div>
    </div>
</body>

</html>
