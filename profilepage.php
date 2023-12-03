<?php
session_start();
require("connect-db.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign-up.php');
    exit();
}


?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="background.css">

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

<div class="container mt-3">
    <div class="row">
        <div class="col-md-6">
            <?php
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT * FROM Fighter WHERE User_ID = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                echo "<table class='table'>"; // Start of the table
                echo "<thead>"; // Table header
                echo "<tr><th>Fighter Name</th><th>Actions</th></tr>";
                echo "</thead>";
                echo "<tbody>"; // Table body
        
                foreach ($result as $row) {
                    echo "<tr>"; // Table row for each fighter
                    echo "<td>" . htmlspecialchars($row['Fighter_Name']) . "</td>"; // Fighter name column
        
                    // Actions column with 'View Details' button
                    echo "<td>";
                    echo "<a class='btn btn-outline-secondary btn-sm' href='fighter-details.php?id=" . htmlspecialchars($row['Fighter_ID']) . "'>View Details</a> ";
                    echo "</td>";
        
                    echo "</tr>";
                }
        
                echo "</tbody>"; 
                echo "</table>"; 
            } else {
                echo "<p>No fighters found.</p>";
            }
            ?>
        </div>

        <div class="col-md-6">
            <div class="card" style="width: 18rem; margin: 20px;">
                <div class="card-body">
                    <h3 class="card-title">Account Details</h3>
                    <p class="card-text">User ID: <?= htmlspecialchars($_SESSION['user_id']); ?></p>
                    <p class="card-text">Username: <?= htmlspecialchars($_SESSION['username']); ?></p>
                    <p class="card-text">Email: <?= htmlspecialchars($_SESSION['email']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>
