<?php
session_start();
include("../includes/cdn.php"); 
include("../config/database.php");

// Check if the user is logged in as admin or employee
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

  <?php include("../includes/admin/header.php"); ?>

  <div class="page p-3 mt-3 mb-3" id="Overview">
      <h1>Dashboard</h1>
      <?php include("../modules/geographic_information_system/dashboard.php"); ?>
  </div>

  <div class="page p-3 mt-3 mb-3" id="Products" style="display: none;">
      <h1>Products</h1>
      <div class="container-fluid mt-5">
        <div class="row">
            <!-- Button 1 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/inventory_management_system/product/category/category_read.php" class="text-decoration-none"> 
                    <div class="card p-4">
                        <i class="bi bi-grid icon"></i>
                        <h5 class="card-title">Category</h5>
                        <p class="card-text">Product Category</p>
                    </div>
                </a>
            </div>
            <!-- Button 2 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/inventory_management_system/product/product_read.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-box icon"></i>
                        <h5 class="card-title">Product</h5>
                        <p class="card-text">Manage Product</p>
                    </div>
                </a>
            </div>
            <!-- Button 3 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/inventory_management_system/inventory/inventory_read.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-house-door icon"></i>
                        <h5 class="card-title">Inventory</h5>
                        <p class="card-text">Manage Inventory</p>
                    </div>
                </a>
            </div>
            <!-- Button 4 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/onhand/onhand_read.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-box-seam icon"></i>
                        <h5 class="card-title">Onhand</h5>
                        <p class="card-text">Manage Onhand Product</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
  </div>

  <div class="page p-3 mt-3 mb-3" id="Transaction" style="display: none;">
      <h1>Transaction</h1>
      <div class="container-fluid mt-5">
        <div class="row">
            <!-- Button 1 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/transaction/personnel/transac_read_pending.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-clock icon"></i>
                        <h5 class="card-title">Pending Transactions</h5>
                        <p class="card-text">Accept Orders</p>
                    </div>
                </a>
            </div>
            <!-- Button 2 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/transaction/personnel/transac_read_approved.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-check-circle icon"></i>
                        <h5 class="card-title">Approved Transactions</h5>
                        <p class="card-text">Deliver Orders</p>
                    </div>
                </a>
            </div>
            <!-- Button 3 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/transaction/personnel/transac_read_delivered.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-check2-circle icon"></i>
                        <h5 class="card-title">Complete Transactions</h5>
                        <p class="card-text">Delivered Orders</p>
                    </div>
                </a>
            </div>
            <!-- Button 4 -->
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-file-earmark-text icon"></i>
                        <h5 class="card-title">Sales Report</h5>
                        <p class="card-text">Print Report</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
  </div>

  <div class="page p-3 mt-3 mb-3" id="Employee" style="display: none;">
      <h1>Employee</h1>
      <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="../modules/inventory_management_system/user_management/employee/employee_create.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-person-plus icon"></i>
                        <h5 class="card-title">Add New Employee</h5>
                        <p class="card-text">Create Account</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="../modules/inventory_management_system/user_management/employee/employee_read.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-person-dash icon"></i>
                        <h5 class="card-title">Employee Account</h5>
                        <p class="card-text">Manage Account</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
  </div>

  <div class="page p-3 mt-3 mb-3" id="Store" style="display: none;">
      <h1>Store</h1>
      <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="../modules/sales_management_system/store/store_create.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-shop icon"></i>
                        <h5 class="card-title">Store Information</h5>
                        <p class="card-text">Manage Information</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="../modules/geographic_information_system/site_selection_report.php" class="text-decoration-none">
                    <div class="card p-4">
                        <i class="bi bi-bar-chart icon"></i>
                        <h5 class="card-title">Site Selection Report</h5>
                        <p class="card-text">Print Report</p>
                    </div>
                </a>
            </div>
        </div>
      </div>
  </div>

  <div class="page p-3 mt-3 mb-3" id="Profile" style="display: none;">
      <h1>Profile</h1>
      <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="../modules/inventory_management_system/user_management/admin/admin_update.php" class="text-decoration-none">
                <div class="card p-4 d-flex flex-row align-items-start" style="width: 300px;">
                    <div class="me-3">
                        <!-- Icon for User Image -->
                        <i class="bi bi-person-circle" style="font-size: 80px;"></i>
                    </div>
                    <div>
                        <h4 class="card-title mb-3" style="margin: 0; text-align: left;"><strong><?php echo htmlspecialchars($_SESSION['AdminRole']); ?></strong>
                        </h4>
                        <p class="mb-1" style="margin: 0;"><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['AdminName']); ?></p>
                    </div>
                </div>

                </a>
            </div>
        </div>
      </div>
  </div>

  <br><br>
  <script src="../assets/js/navbar.js"></script>
  <?php include("../includes/admin/footer.php"); ?>


</body>

</html>
