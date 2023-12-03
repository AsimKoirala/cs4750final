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
        $_SESSION['email'] = $email; 
    }
    if (!empty($_POST['login_Btn'])) {
        $username = $_POST['username'];
        $password = $_POST['password']; 
   
        $sql = "SELECT user_id, username,  email, password FROM User WHERE username = :username";
    
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
                $_SESSION['email'] = $result['email'];
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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
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
