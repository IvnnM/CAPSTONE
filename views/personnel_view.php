<?php
session_start();
include("../includes/cdn.html"); 
include("../config/database.php");

// Check if the user is logged in as admin or employee
if (isset($_SESSION['AdminID'])) {
    $_SESSION['AdminRole'] = 'Admin';
    // Fetch additional admin details, if needed (e.g., AdminName)
    $adminID = $_SESSION['AdminID'];
    $stmt = $conn->prepare("SELECT AdminName FROM AdminTb WHERE AdminID = :adminID");
    $stmt->execute(['adminID' => $adminID]);
    $adminData = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['AdminName'] = $adminData['AdminName'] ?? 'Admin';  // Use AdminName instead of Name
} elseif (isset($_SESSION['EmpID'])) {
    $_SESSION['AdminRole'] = 'Employee';
    // Fetch additional employee details, if needed (e.g., EmployeeName)
    $empID = $_SESSION['EmpID'];
    $stmt = $conn->prepare("SELECT EmpName FROM EmpTb WHERE EmpID = :empID");  // Use EmpName instead of Name
    $stmt->execute(['empID' => $empID]);
    $employeeData = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['AdminName'] = $employeeData['EmpName'] ?? 'Employee';  // Use EmpName instead of Name
} else {
    header("Location: ../index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href="../assets/css/personnel.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid p-0">
        <!-- Main Content Area -->
        <?php include("../includes/personnel/header.php"); ?>
        <?php include("../includes/personnel/navbar.php"); ?>
        <?php include("../includes/personnel/sidebar.php"); ?>
        <hr class="bg-dark">
        <main class="text-dark">
            <div class="page fade-element container relative " id="Overview">
               <!-- Dashboard -->
                <div class="row justify-content-center align-items-center g-2">
                    <?php include("../modules/geographic_information_system/dashboard.php"); ?>

                </div>
                <div class="row">
                    <?php include("../modules/geographic_information_system/map.php"); ?>
                </div><br><br><br>

            </div>

            <!--<div class="page fade-element container relative " id="Products">-->
            <!--    <h1>Products</h1>-->
            <!--    <div class="container-fluid mt-5">-->
            <!--        <div class="row text-center text-center">-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/inventory_management_system/product/category/category_read.php" class="text-decoration-none"> -->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-grid icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Category</h5>-->
            <!--                        <p class="card-text">Product Category</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/inventory_management_system/product/product_read.php" class="text-decoration-none">-->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-box icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Product</h5>-->
            <!--                        <p class="card-text">Manage Product</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/inventory_management_system/inventory/inventory_read.php" class="text-decoration-none">-->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-house-door icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Inventory</h5>-->
            <!--                        <p class="card-text">Manage Inventory</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/sales_management_system/onhand/onhand_read.php" class="text-decoration-none">-->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-box-seam icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Onhand</h5>-->
            <!--                        <p class="card-text">Manage Onhand Product</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->

            <!--<div class="page fade-element container relative " id="Transaction">-->
            <!--    <h1>Transaction</h1>-->
            <!--    <div class="container-fluid mt-5">-->
            <!--        <div class="row text-center">-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/sales_management_system/transaction/personnel/transac_read_pending.php" class="text-decoration-none">-->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-clock icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Customer Pending Orders</h5>-->
            <!--                        <p class="card-text">Confirm Orders</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/sales_management_system/transaction/personnel/transac_read_approved.php" class="text-decoration-none">-->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-check-circle icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Accepted for Delivery</h5>-->
            <!--                        <p class="card-text">Ship Orders</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/sales_management_system/transaction/personnel/transac_read_delivered.php" class="text-decoration-none">-->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-check2-circle icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Complete Transaction</h5>-->
            <!--                        <p class="card-text">Delivered Orders</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->

            <!--<div class="page fade-element container relative " id="Employee">-->
            <!--    <h1>Employee</h1>-->
            <!--    <div class="container-fluid mt-5">-->
            <!--        <div class="row text-center">-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/inventory_management_system/user_management/employee/employee_read.php" class="text-decoration-none">-->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-person icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Employee Account</h5>-->
            <!--                        <p class="card-text">Manage Account</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->

            <!--<div class="page fade-element container relative " id="Store">-->
            <!--    <h1>Store</h1>-->
            <!--    <div class="container-fluid mt-5">-->
            <!--        <div class="row text-center">-->
            <!--            <div class="col-md-3 mb-3">-->
            <!--                <a href="../modules/sales_management_system/store/store_read.php" class="text-decoration-none">-->
            <!--                    <div class="card p-4 shadow">-->
            <!--                        <i class="bi bi-shop icon fs-1 mb-1"></i>-->
            <!--                        <h5 class="card-title">Store Information</h5>-->
            <!--                        <p class="card-text">Manage Information</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->

            
        </main>
        <?php //include("../includes/personnel/footer.php"); ?>
        

        <script src="../assets/js/navbar.js"></script>

   
</div>  
</body>
</html>

