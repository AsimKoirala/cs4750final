<?php
session_start();
require("connect-db.php");

$user_id = $_SESSION['user_id'] ?? null;
$fighter_id = $_GET['fighter_id'] ?? null;

if (!isset($_SESSION['user_id'])) {
    header('Location: sign-up.php');
    exit();
}

// Fetch existing fighter data
$stmt = $db->prepare("SELECT * FROM Fighter WHERE Fighter_ID = ? AND User_ID = ?");
$stmt->execute([$fighter_id, $user_id]);
$fighter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fighter) {
    echo "No such fighter found or you do not have permission to edit this fighter.";
    exit;
}

// Fetching moveset and characteristics
$stmtMoveset = $db->prepare("SELECT * FROM Movesets WHERE Move_ID = ?");
$stmtMoveset->execute([$fighter['Move_ID']]);
$moveset = $stmtMoveset->fetch(PDO::FETCH_ASSOC);

$stmtCharacteristics = $db->prepare("SELECT * FROM Characteristics WHERE Fighter_ID = ?");
$stmtCharacteristics->execute([$fighter_id]);
$characteristics = $stmtCharacteristics->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve updated data from the form
    $updated_fighter_name = $_POST['fighter_name'];
    // ... other fields ...

    // Update the database
    $db->beginTransaction();
    try {
        // Update Fighter table
        $stmt = $db->prepare("UPDATE Fighter SET Fighter_Name = ? WHERE Fighter_ID = ?");
        $stmt->execute([$updated_fighter_name, $fighter_id]);
        
        // Update Movesets table with all moves
        $stmt = $db->prepare("UPDATE Movesets SET Up_B = ?, Down_B = ?, Side_B = ?, Neutral_B = ?, Neutral_A = ?, Down_A = ?, Up_A = ?, Side_A = ?, Up_Air = ?, Down_Air = ?, Back_Air = ?, Forward_Air = ?, Neutral_Air = ? WHERE Move_ID = ?");
        $stmt->execute([
            $_POST['up_b'], $_POST['down_b'], $_POST['side_b'], $_POST['neutral_b'],
            $_POST['neutral_a'], $_POST['down_a'], $_POST['up_a'], $_POST['side_a'],
            $_POST['up_air'], $_POST['down_air'], $_POST['back_air'], $_POST['forward_air'], $_POST['neutral_air'],
            $fighter['Move_ID']
        ]);

        // Update Characteristics table with all characteristics
        $stmt = $db->prepare("UPDATE Characteristics SET Gender = ?, Weight_Value = ?, Height = ?, Fast = ? WHERE Fighter_ID = ?");
        $stmt->execute([
            $_POST['gender'], $_POST['weight_value'], $_POST['height'], $_POST['fast'],
            $fighter_id
        ]);

        $db->commit();
        echo "Fighter updated successfully!";
        header("Location: fighters.php");
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        echo "Error updating fighter: " . $e->getMessage();
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="background.css">

    <title>Edit Fighter</title>
    <!-- Additional CSS or JS here -->
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
<div class="container mt-4">
        <h1 class="mb-4">Edit Fighter: <?= htmlspecialchars($fighter['Fighter_Name']) ?></h1>
        <form method="post">
  
        <div class="mb-3">
                <label for="fighter_name" class="form-label">Fighter Name:</label>
                <input type="text" class="form-control" id="fighter_name" name="fighter_name" value="<?= htmlspecialchars($fighter['Fighter_Name']) ?>" required>
            </div>
        
        <!-- Movesets -->
        <label for="up_b">Up B:</label>
        <input type="text" class = "form-control" id="up_b" name="up_b" value="<?= htmlspecialchars($moveset['Up_B']) ?>"><br>

        <label for="down_b">Down B:</label>
        <input type="text" class = "form-control"  id="down_b" name="down_b" value="<?= htmlspecialchars($moveset['Down_B']) ?>"><br>

        <label for="side_b">Side B:</label>
        <input type="text" class = "form-control"  id="side_b" name="side_b" value="<?= htmlspecialchars($moveset['Side_B']) ?>"><br>

        <label for="neutral_b">Neutral B:</label>
        <input type="text" class = "form-control"  id="neutral_b" name="neutral_b" value="<?= htmlspecialchars($moveset['Neutral_B']) ?>"><br>

        <label for="neutral_a">Neutral A:</label>
        <input type="text" class = "form-control"  id="neutral_a" name="neutral_a" value="<?= htmlspecialchars($moveset['Neutral_A']) ?>"><br>

        <label for="down_a">Down A:</label>
        <input type="text" class = "form-control"  id="down_a" name="down_a" value="<?= htmlspecialchars($moveset['Down_A']) ?>"><br>

        <label for="up_a">Up A:</label>
        <input type="text" class = "form-control"  id="up_a" name="up_a" value="<?= htmlspecialchars($moveset['Up_A']) ?>"><br>

        <label for="side_a">Side A:</label>
        <input type="text" class = "form-control"  id="side_a" name="side_a" value="<?= htmlspecialchars($moveset['Side_A']) ?>"><br>

        <label for="up_air">Up Air:</label>
        <input type="text" class = "form-control"  id="up_air" name="up_air" value="<?= htmlspecialchars($moveset['Up_Air']) ?>"><br>

        <label for="down_air">Down Air:</label>
        <input type="text" class = "form-control"  id="down_air" name="down_air" value="<?= htmlspecialchars($moveset['Down_Air']) ?>"><br>

        <label for="back_air">Back Air:</label>
        <input type="text" class = "form-control"  id="back_air" name="back_air" value="<?= htmlspecialchars($moveset['Back_Air']) ?>"><br>

        <label for="forward_air">Forward Air:</label>
        <input type="text" class = "form-control"  id="forward_air" name="forward_air" value="<?= htmlspecialchars($moveset['Forward_Air']) ?>"><br>

        <label for="neutral_air">Neutral Air:</label>
        <input type="text" class = "form-control"  id="neutral_air" name="neutral_air" value="<?= htmlspecialchars($moveset['Neutral_Air']) ?>"><br>


        <!-- characteristic -->
        <label for="gender">Gender:</label>
        <input type="text" class = "form-control"  id="gender" name="gender" value="<?= htmlspecialchars($characteristics['Gender']) ?>"><br>

        <label for="weight_value">Weight:</label>
        <input type="number" class = "form-control"  id="weight_value" name="weight_value" value="<?= htmlspecialchars($characteristics['Weight_Value']) ?>"><br>

        <label for="height">Height:</label>
        <input type="number" class = "form-control"  id="height" name="height" value="<?= htmlspecialchars($characteristics['Height']) ?>"><br>

        <label for="fast">Speed (1-5):</label>
        <input type="number" class = "form-control"  id="fast" name="fast" value="<?= htmlspecialchars($characteristics['Fast']) ?>" min="1" max="5"><br>

        <input type="submit" value="Update Fighter">
    </form>

    </div>
</body>
</div>
</div>
</html>
