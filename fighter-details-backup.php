<?php
session_start();
require("connect-db.php");

$user_id = $_SESSION['user_id'] ?? null;
$fighter_id = $_GET['id'] ?? null; // Assuming Fighter_ID is passed via GET request

if (!$user_id || !$fighter_id) {
    // Redirect or handle the error
    header("Location: sign-up.php");
    exit;
}

// Check if the logged-in user created the fighter
$stmt = $db->prepare("SELECT COUNT(*) FROM Creates WHERE User_ID = ? AND Fighter_ID = ?");
$stmt->execute([$user_id, $fighter_id]);
$is_creator = $stmt->fetchColumn() > 0;

// HTML for Edit and Delete buttons (only shown if the user is the creator)
if ($is_creator) {
    echo "<a href='edit_fighter.php?fighter_id=$fighter_id'>Edit Fighter</a><br>"; // Link to edit fighter page
    echo "<form method='post'>
            <input type='hidden' name='action' value='delete'>
            <input type='hidden' name='fighter_id' value='$fighter_id'>
            <input type='submit' value='Delete Fighter'>
          </form>"; // Form for deleting fighter
}

// Handle the delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'delete') {
    $fighter_id_to_delete = $_POST['fighter_id'];

    try {
        // Assuming you have the $fighter_id you want to delete
        $db->beginTransaction();
    
        // Delete from Is_Built_With, Can_Do, Appears_In, and Bookmarks
        $db->exec("DELETE FROM Is_Built_With WHERE Fighter_ID = $fighter_id");
        $db->exec("DELETE FROM Can_Do WHERE Fighter_ID = $fighter_id");
        $db->exec("DELETE FROM Appears_In WHERE Fighter_ID = $fighter_id");
        $db->exec("DELETE FROM Bookmarks WHERE Fighter_ID = $fighter_id");
    
        // Retrieve Characteristics_ID and Move_ID for the fighter
        $stmt = $db->prepare("SELECT Characteristics_ID, Move_ID FROM Fighter WHERE Fighter_ID = ?");
        $stmt->execute([$fighter_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Delete from Characteristics
        $db->exec("DELETE FROM Characteristics WHERE Characteristics_ID = {$result['Characteristics_ID']}");
    
        // Delete from Movesets (if not shared with other fighters)
        $db->exec("DELETE FROM Movesets WHERE Move_ID = {$result['Move_ID']}");
    
        // Delete the fighter
        $db->exec("DELETE FROM Fighter WHERE Fighter_ID = $fighter_id");
    
        $db->commit();
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fighter Details</title>
    <!-- Add additional CSS or JS here -->
</head>
<body>
    <h1><?= htmlspecialchars($details['Fighter_Name']) ?> Details</h1>

    <h2>Franchise: <?= htmlspecialchars($details['Franchise_Name']) ?></h2>

    <h3>Movesets:</h3>
    <!-- Display movesets details -->
    <p>Up B: <?= htmlspecialchars($details['Up_B']) ?></p>
    <p>Down B: <?= htmlspecialchars($details['Down_B']) ?></p>
    <p>Side B: <?= htmlspecialchars($details['Side_B']) ?></p>
    <p>Neutral B: <?= htmlspecialchars($details['Neutral_B']) ?></p>
    <p>Neutral A: <?= htmlspecialchars($details['Neutral_A']) ?></p>
    <p>Down A: <?= htmlspecialchars($details['Down_A']) ?></p>
    <p>Up A: <?= htmlspecialchars($details['Up_A']) ?></p>
    <p>Side A: <?= htmlspecialchars($details['Side_A']) ?></p>
    <p>Up Air: <?= htmlspecialchars($details['Up_Air']) ?></p>
    <p>Down Air: <?= htmlspecialchars($details['Down_Air']) ?></p>
    <p>Back Air: <?= htmlspecialchars($details['Back_Air']) ?></p>
    <p>Forward Air: <?= htmlspecialchars($details['Forward_Air']) ?></p>
    <p>Neutral Air: <?= htmlspecialchars($details['Neutral_Air']) ?></p>


    <h3>Characteristics:</h3>
    <!-- Display characteristics details -->
    <p>Gender: <?= htmlspecialchars($details['Gender']) ?></p>
    <p>Weight: <?= htmlspecialchars($details['Weight_Value']) ?></p>
    <!-- Add all other characteristics here using the same pattern -->
    <p>Height: <?= htmlspecialchars($details['Height']) ?></p>
    <p>Speed: <?= htmlspecialchars($details['Fast']) ?> (on a scale of 1 to 5)</p>
    <!-- ... continue for all other characteristics columns ... -->

    <!-- Add links to other pages or additional content here -->
    <a href="fighters.php">Back to Fighters List</a>
</body>
</html>
