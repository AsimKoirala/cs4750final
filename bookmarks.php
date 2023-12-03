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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="background.css">
    <title>Bookmarked Fighters</title>
</head>
<body>
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


  <h1>Bookmarked Fighters</h1>

<!-- Bootstrap List Group -->
<div class="list-group">
    <?php foreach ($bookmarkedFighters as $fighter): ?>
        <a href="fighter-details.php?id=<?= htmlspecialchars($fighter['Fighter_ID']) ?>" class="list-group-item list-group-item-action">
            <?= htmlspecialchars($fighter['Fighter_Name']) ?>
        </a>
    <?php endforeach; ?>
</div>

</body>
</html>
