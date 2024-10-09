<?php
session_start();
include("../includes/cdn.html"); 
include("../config/database.php");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and store input values in session
    $_SESSION['cust_name'] = htmlspecialchars($_POST['cust_name']);
    $_SESSION['cust_num'] = htmlspecialchars($_POST['cust_num']);
    $_SESSION['cust_email'] = htmlspecialchars($_POST['cust_email']);
}

// Check if session values are set
$cust_name = $_SESSION['cust_name'] ?? '';
$cust_num = $_SESSION['cust_num'] ?? '';
$cust_email = $_SESSION['cust_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DKAT Store</title>
    <style>
        /* body {
            background-color: #343a40 !important;
            color: white !important;
        } */
        /* .container {
            border: solid black;
        }
        .row  {
            border: solid red;
        } */
        /* .col  {
            border: solid blue;
        } */
    </style>
</head>
<body>
    <div class="page" id="Overview">
        <?php include("../includes/customer/header.php"); ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col col-7">
                    <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                            <img src="..." class="d-block w-100" alt="...">
                            </div>
                            <div class="carousel-item">
                            <img src="..." class="d-block w-100" alt="...">
                            </div>
                            <div class="carousel-item">
                            <img src="..." class="d-block w-100" alt="...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col col-5">
                    <?php if (!isset($_SESSION['cust_email'])): ?>
                        <h2>Customer Information</h2>
                        <form id="custform" action="" method="POST">
                            <div class="mb-3">
                                <label for="cust_name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="cust_name" name="cust_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="cust_num" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="cust_num" name="cust_num" required>
                            </div>
                            <div class="mb-3">
                                <label for="cust_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="cust_email" name="cust_email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    <?php else: ?>
                        <div class="container-fluid">
                            <h2>Welcome, <?= htmlspecialchars($cust_name); ?>!</h2>
                            <p>Contact Number: <?= htmlspecialchars($cust_num); ?></p>
                            <p>Email Address: <?= htmlspecialchars($cust_email); ?></p>
                            <p>You can now proceed with your purchases!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div><hr>
        <div class="row">
            <div class="col col-12">
                <?php include("../modules/sales_management_system/transaction/available_product.php"); ?>
            </div>
        </div>
    </div>

    <div class="page" id="Orders" style="display: none;">
        <?php include("../includes/customer/header.php"); ?>
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

    <script src="../assets/js/navbar.js"></script>
    <?php include("../includes/customer/footer.php"); ?>
    
</body>
</html>
