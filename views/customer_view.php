<?php
session_start();
include("../includes/cdn.php"); 
include("../config/database.php");

// // Check if the user is logged in as admin or employee
// if (isset($_SESSION['AdminID'])) {
//     $_SESSION['AdminRole'] = 'Admin';
// } elseif (isset($_SESSION['EmpID'])) {
//     $_SESSION['AdminRole'] = 'Employee';
// } else {
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

  <?php include("../includes/customer/header.php"); ?>

  <div class="page p-3 mt-3 mb-3" id="Overview">
      <h1>All Products Available</h1>
      <?php include("../modules/sales_management_system/transaction/available_product.php"); ?>
  </div>

  <div class="page p-3 mt-3 mb-3" id="Orders" style="display: none;">
      <h1>Find your orders here</h1>
      <div class="container-fluid mt-5">
        <div class="row">
            <!-- Button 1 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/transaction/customer/transac_read_pending.php" class="text-decoration-none"> 
                    <div class="card p-4">
                        <i class="bi bi-grid icon"></i>
                        <h5 class="card-title">To Pay</h5>
                        <p class="card-text">Waiting for Approval</p>
                    </div>
                </a>
            </div>
            <!-- Button 2 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/transaction/customer/transac_read_approved.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-box icon"></i>
                        <h5 class="card-title">To Receive</h5>
                        <p class="card-text">Preparing to Ship</p>
                    </div>
                </a>
            </div>
            <!-- Button 3 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/transaction/customer/transac_read_delivered.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-house-door icon"></i>
                        <h5 class="card-title">Completed</h5>
                        <p class="card-text">Order Received</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
  </div>


  <br><br><br>
  <script src="../assets/js/navbar.js"></script>
  <?php include("../includes/customer/footer.php"); ?>


</body>

</html>