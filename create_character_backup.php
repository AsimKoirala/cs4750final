<?php
session_start();
require("connect-db.php");

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Function to handle database insertions with prepared statements
function insert($db, $query, $params) {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $db->lastInsertId();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $fighter_name = filter_input(INPUT_POST, 'fighter_name', FILTER_SANITIZE_STRING);
    $franchise_name = filter_input(INPUT_POST, 'franchise_name', FILTER_SANITIZE_STRING);

    // Validation for movesets and other inputs goes here...
        // Gather POST data with basic validation/sanitization for Movesets
    $up_b = filter_input(INPUT_POST, 'Up_B', FILTER_SANITIZE_STRING);
    $down_b = filter_input(INPUT_POST, 'Down_B', FILTER_SANITIZE_STRING);
    $side_b = filter_input(INPUT_POST, 'Side_B', FILTER_SANITIZE_STRING);
    $neutral_b = filter_input(INPUT_POST, 'Neutral_B', FILTER_SANITIZE_STRING);
    $neutral_a = filter_input(INPUT_POST, 'Neutral_A', FILTER_SANITIZE_STRING);
    $down_a = filter_input(INPUT_POST, 'Down_A', FILTER_SANITIZE_STRING);
    $up_a = filter_input(INPUT_POST, 'Up_A', FILTER_SANITIZE_STRING);
    $side_a = filter_input(INPUT_POST, 'Side_A', FILTER_SANITIZE_STRING);
    $up_air = filter_input(INPUT_POST, 'Up_Air', FILTER_SANITIZE_STRING);
    $down_air = filter_input(INPUT_POST, 'Down_Air', FILTER_SANITIZE_STRING);
    $back_air = filter_input(INPUT_POST, 'Back_Air', FILTER_SANITIZE_STRING);
    $forward_air = filter_input(INPUT_POST, 'Forward_Air', FILTER_SANITIZE_STRING);
    $neutral_air = filter_input(INPUT_POST, 'Neutral_Air', FILTER_SANITIZE_STRING);

    // Gather POST data with basic validation/sanitization for Characteristics
    $gender = filter_input(INPUT_POST, 'Gender', FILTER_SANITIZE_STRING);
    $weight_value = filter_input(INPUT_POST, 'Weight_Value', FILTER_SANITIZE_NUMBER_INT);
    $height = filter_input(INPUT_POST, 'Height', FILTER_SANITIZE_NUMBER_INT);
    $fast = filter_input(INPUT_POST, 'Fast', FILTER_SANITIZE_NUMBER_INT);


    try {
        // Start transaction
        $db->beginTransaction();

        // Insert franchise if it doesn't exist and get its ID
        $stmt = $db->prepare("SELECT Franchise_ID FROM Franchise WHERE Franchise_Name = ?");
        $stmt->execute([$franchise_name]);
        $franchise_id = $stmt->fetchColumn();

        if (!$franchise_id) {
            $stmt = $db->prepare("INSERT INTO Franchise (Franchise_Name) VALUES (?)");
            $stmt->execute([$franchise_name]);
            $franchise_id = $db->lastInsertId();
        }

        
        // Insert movesets and other details...
        $up_b = filter_input(INPUT_POST, 'Up_B', FILTER_SANITIZE_STRING);
        $down_b = filter_input(INPUT_POST, 'Down_B', FILTER_SANITIZE_STRING);
        $side_b = filter_input(INPUT_POST, 'Side_B', FILTER_SANITIZE_STRING);
        $neutral_b = filter_input(INPUT_POST, 'Neutral_B', FILTER_SANITIZE_STRING);
        $neutral_a = filter_input(INPUT_POST, 'Neutral_A', FILTER_SANITIZE_STRING);
        $down_a = filter_input(INPUT_POST, 'Down_A', FILTER_SANITIZE_STRING);
        $up_a = filter_input(INPUT_POST, 'Up_A', FILTER_SANITIZE_STRING);
        $side_a = filter_input(INPUT_POST, 'Side_A', FILTER_SANITIZE_STRING);
        $up_air = filter_input(INPUT_POST, 'Up_Air', FILTER_SANITIZE_STRING);
        $down_air = filter_input(INPUT_POST, 'Down_Air', FILTER_SANITIZE_STRING);
        $back_air = filter_input(INPUT_POST, 'Back_Air', FILTER_SANITIZE_STRING);
        $forward_air = filter_input(INPUT_POST, 'Forward_Air', FILTER_SANITIZE_STRING);
        $neutral_air = filter_input(INPUT_POST, 'Neutral_Air', FILTER_SANITIZE_STRING);
 

        // Retrieve the maximum Move_ID from Movesets
        $stmt = $db->prepare("SELECT MAX(Move_ID) FROM Movesets");
        $stmt->execute();
        $max_move_id = $stmt->fetchColumn();

        // Increment the Move_ID by 1
        $new_move_id = $max_move_id + 1;
        print $new_move_id;

        // Insert the new moveset with the new Move_ID
        $stmt = $db->prepare("INSERT INTO Movesets (Move_ID, Up_B, Down_B, Side_B, Neutral_B, Neutral_A, Down_A, Up_A, Side_A, Up_Air, Down_Air, Back_Air, Forward_Air, Neutral_Air) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $new_move_id, $up_b, $down_b, $side_b, $neutral_b, $neutral_a, $down_a, $up_a, $side_a, $up_air, $down_air, $back_air, $forward_air, $neutral_air
        ]);
        
        // Insert fighter and get Fighter_ID
        $stmt = $db->prepare("INSERT INTO Fighter (Fighter_Name, Franchise_ID, Move_ID, User_ID) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fighter_name, $franchise_id, $new_move_id, $user_id]);
        $fighter_id = $db->lastInsertId();

        // Gather and validate characteristics data
        $gender = filter_input(INPUT_POST, 'Gender', FILTER_SANITIZE_STRING);
        $weight_value = filter_input(INPUT_POST, 'Weight_Value', FILTER_SANITIZE_NUMBER_INT);
        $height = filter_input(INPUT_POST, 'Height', FILTER_SANITIZE_NUMBER_INT);
        $fast = filter_input(INPUT_POST, 'Fast', FILTER_SANITIZE_NUMBER_INT);

        if ($weight_value <= 0 || $height <= 0 || $fast <= 0) {
            $_SESSION['error'] = "Error: Weight, Height, and Speed must be positive numbers.";
            header("Location: create_character.php");
            exit;
        }

        if ($weight_value <= 0 || $height <= 0 || $fast <= 0) {
            throw new Exception("Weight, Height, and Speed must be positive numbers.");
        }

        // Insert characteristics
        $stmt = $db->prepare("INSERT INTO Characteristics (Fighter_ID, Gender, Weight_Value, Height, Fast) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fighter_id, $gender, $weight_value, $height, $fast]);
        $characteristics_id = $db->lastInsertId(); // Retrieve the auto-incremented Characteristics_ID

        // Insert into Appears_In table
        $stmt = $db->prepare("INSERT INTO Appears_In (Fighter_ID, Franchise_ID) VALUES (?, ?)");
        $stmt->execute([$fighter_id, $franchise_id]);

        // Insert into Creates table
        $stmt = $db->prepare("INSERT INTO Creates (User_ID, Fighter_ID) VALUES (?, ?)");
        $stmt->execute([$user_id, $fighter_id]);

        // Insert into Can_Do table
        // Assuming Move_ID is linked to the Fighter_ID in the Movesets table
        $stmt = $db->prepare("INSERT INTO Can_Do (Fighter_ID, Move_ID) VALUES (?, ?)");
        $stmt->execute([$fighter_id, $new_move_id]);

        // Insert into Is_Built_With table
        // Assuming you need to link the newly created Characteristics_ID with the Fighter
        $stmt = $db->prepare("INSERT INTO Is_Built_With (Fighter_ID, Characteristics_ID) VALUES (?, ?)");
        $stmt->execute([$fighter_id, $characteristics_id]); // $characteristics_id needs to be retrieved similar to $fighter_id

        $db->commit();
        $_SESSION['message'] = "Fighter created successfully!";
        header("Location: create_character.php");
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "Error creating fighter: " . $e->getMessage();
        header("Location: create_character.php");
        exit;
    }
}

// If an error message was set in the session, retrieve and clear it
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

// ... Rest of the HTML form and other code ...


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Fighter</title>
    <!-- Add any additional CSS or JS here -->
</head>
<body>
    <h1>Create New Fighter</h1>

    <!-- Display messages -->
    <?= !empty($message) ? "<p class='success'>$message</p>" : "" ?>
    <?= !empty($error) ? "<p class='error'>$error</p>" : "" ?>

    <form action="create_character.php" method="post">

        <h2>Basic Info:</h2>
        <label for="fighter_name">Fighter Name:</label>
        <input type="text" id="fighter_name" name="fighter_name" required><br>

        <label for="franchise_name">Franchise Name:</label>
        <input type="text" id="franchise_name" name="franchise_name" required><br>

        <!-- Moveset Details -->
        <h2>Movesets Info:</h2>
        <label for="Up_B">Up B:</label>
        <input type="text" id="Up_B" name="Up_B" required><br>

        <label for="Down_B">Down B:</label>
        <input type="text" id="Down_B" name="Down_B" required><br>

        <label for="Side_B">Side B:</label>
        <input type="text" id="Side_B" name="Side_B" required><br>

        <label for="Neutral_B">Neutral B:</label>
        <input type="text" id="Neutral_B" name="Neutral_B" required><br>

        <label for="Neutral_A">Neutral A:</label>
        <input type="text" id="Neutral_A" name="Neutral_A" required><br>

        <label for="Down_A">Down A:</label>
        <input type="text" id="Down_A" name="Down_A" required><br>

        <label for="Up_A">Up A:</label>
        <input type="text" id="Up_A" name="Up_A" required><br>

        <label for="Side_A">Side A:</label>
        <input type="text" id="Side_A" name="Side_A" required><br>

        <label for="Up_Air">Up Air:</label>
        <input type="text" id="Up_Air" name="Up_Air" required><br>

        <label for="Down_Air">Down Air:</label>
        <input type="text" id="Down_Air" name="Down_Air" required><br>

        <label for="Back_Air">Back Air:</label>
        <input type="text" id="Back_Air" name="Back_Air" required><br>

        <label for="Forward_Air">Forward Air:</label>
        <input type="text" id="Forward_Air" name="Forward_Air" required><br>

        <label for="Neutral_Air">Neutral Air:</label>
        <input type="text" id="Neutral_Air" name="Neutral_Air" required><br>

        <!-- Characteristics Details -->
        <h2>Characteristics Info:</h2>
        <label for="Gender">Gender:</label>
        <input type="text" id="Gender" name="Gender" required><br>

        <label for="Weight_Value">Weight:</label>
        <input type="number" id="Weight_Value" name="Weight_Value" required><br>

        <label for="Height">Height:</label>
        <input type="number" id="Height" name="Height" required><br>

        <label for="Fast">Speed (1-5):</label>
        <input type="number" id="Fast" name="Fast" min="1" max="5" required><br>

        <input type="submit" value="Create Fighter">

    </form>

    <a href="fighters.php">Back to Fighters List</a>
</body>
</html>
