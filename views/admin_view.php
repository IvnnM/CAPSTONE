<?php
session_start();
include("../includes/cdn.php"); 
include("../config/database.php");

// Check if the admin session variable is set
// if (!isset($_SESSION['AdminID'])) {
//     header("Location: ../index.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  
  <?php include("../includes/admin/header.php"); ?>
  <br><br><br>
  <br><br><br><br><br><br><br><br><br>
  <!-- <h1>Content</h1>
  <a href=""><button>Go to</button></a>
  <a href=""><button>Go to</button></a>
  <a href=""><button>Go to</button></a>
  <a href=""><button>Go to</button></a> -->

  <br><br><br>
  
  <?php include("../includes/admin/footer.php"); ?>

</body>
</html>
