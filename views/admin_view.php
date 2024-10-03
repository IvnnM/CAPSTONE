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
  <br><br>

  <div class="page p-3 mt-3 mb-3" id="Overview">
      <h1>Dashboard</h1>
      <?php include("../modules/geographic_information_system/dashboard.php"); ?>
  </div>

  <div class="page p-3 mt-3 mb-3" id="Products" style="display: none;">
      <h1>Products</h1>
      <!-- Include relevant product-related modules here -->
  </div>

  <div class="page p-3 mt-3 mb-3" id="Transaction" style="display: none;">
      <h1>Transaction</h1>
      <!-- Include relevant transaction-related modules here -->
  </div>

  <div class="page p-3 mt-3 mb-3" id="Employee" style="display: none;">
      <h1>Employee</h1>
      <!-- Include relevant employee-related modules here -->
  </div>

  <div class="page p-3 mt-3 mb-3" id="Store" style="display: none;">
      <h1>Store</h1>
      <!-- Include relevant store-related modules here -->
  </div>

  <div class="page p-3 mt-3 mb-3" id="Profile" style="display: none;">
      <h1>Profile</h1>
      <h2>Session Information</h2>
      <ul>
          <li>User ID: <?php echo htmlspecialchars($_SESSION['userId']); ?></li>
          <li>User Name: <?php echo htmlspecialchars($_SESSION['userName']); ?></li>
          <li>Role: <?php echo htmlspecialchars($_SESSION['role']); ?></li>
      </ul>
  </div>

  <br><br>
  <?php include("../includes/admin/footer.php"); ?>
  <script src="../assets/js/navbar.js"></script>

</body>

</html>
