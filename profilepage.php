<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: sign-up.php');
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
</head>

<body>
<a href="http://localhost/cs4750final/homepage.html">Go back Home</a>
    <h1><center>Welcome to your Profile Page,   <?php echo $_SESSION['username']; ?> </center></h1>
 
</body>
</html>
