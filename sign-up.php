<?php
session_start();
require("connect-db.php");
if (isset($_SESSION['user_id'])) {
    header("Location: profilepage.php");
    exit;
}
function addUser($username, $password, $email)
{
    global $db;

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO User (Username, Password, Email) VALUES (:Username, :Password, :Email)";

    $statement = $db->prepare($query);

    $statement->bindValue(':Username', $username);
    $statement->bindValue(':Password', $hashedPassword); // Store the hashed password
    $statement->bindValue(':Email', $email);
    $statement->execute();
    $statement->closeCursor();
}





if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    
    if(!empty($_POST['sign-upBtn']))
    {
        addUser($_POST['username'], $_POST['password'], $_POST['email']);
        $_SESSION['user_id'] = $userId; 
        $_SESSION['username'] = $username; 
    }
    if (!empty($_POST['login_Btn'])) {
        $username = $_POST['username'];
        $password = $_POST['password']; 
    
        // SQL to check the user
        $sql = "SELECT user_id, username, password FROM User WHERE username = :username";
    
        // Prepare statement
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $username); // Using bindValue with PDO
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            // User exists, verify password
            if (password_verify($password, $result['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['username'] = $result['username'];
                // Redirect to user dashboard or another page
                header("Location: profilepage.php");
                exit;
            } else {
                // Password is incorrect
                echo "Invalid password.";
            }
        } else {
            // No user found with that username
            echo "No user found with that username.";
        }
    }
    
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
</head>
<body>
    <form action="sign-up.php" method="post">
        Email: <input type="email" name="email" required><br>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Sign Up" name ="sign-upBtn">

    </form>
    Log in
    <form action="sign-up.php" method="post">

        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Login" name ="login_Btn">

    </form>
    <a href="http://localhost/cs4750final/homepage.html">Go back Home</a>
</body>
</html>
