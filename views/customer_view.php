<?php
session_start();
include("../includes/cdn.html"); 
include("../config/database.php");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and store input values in session
    $_SESSION['cust_name'] = htmlspecialchars($_POST['cust_name']);
    // $_SESSION['cust_num'] = htmlspecialchars($_POST['cust_num']);
    $_SESSION['cust_email'] = htmlspecialchars($_POST['cust_email']);
    $_SESSION['location_id'] = htmlspecialchars($_POST['location_id']); // Store the selected location
}

// Check if session values are set
$cust_name = $_SESSION['cust_name'] ?? '';
$cust_num = $_SESSION['cust_num'] ?? '';
$cust_email = $_SESSION['cust_email'] ?? '';
$location_id = $_SESSION['location_id'] ?? '';
// Fetch the real location (city name) based on location_id
$city_name = '';
if ($location_id) {
    $query = "SELECT City FROM LocationTb WHERE LocationID = :location_id"; // Adjust table name and column as needed
    $stmt = $conn->prepare($query);
    $stmt->execute(['location_id' => $location_id]);
    $city = $stmt->fetch(PDO::FETCH_ASSOC);
    $city_name = $city['City'] ?? ''; // Get the city name
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DKAT Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page" id="Overview">
        <?php include("../includes/customer/header.php"); ?>
        <div class="container-fluid">
            <div class="row mx-auto w-80" style="border: solid;">
                <div class="col col-7" style="max-height: 300px; overflow-y: auto;">
                    <?php include('../modules/sales_management_system/transaction/cart/cart_read.php'); ?>
                </div>
                <div class="col col-5">
                    <?php if (!isset($_SESSION['cust_email'])): ?>
                        <h2>Customer Information</h2>
                        <form id="custform" action="" method="POST">
                            <div class="mb-3">
                                <label for="cust_name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="cust_name" name="cust_name" required>
                            </div>
                            <!-- <div class="mb-3">
                                <label for="cust_num" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="cust_num" name="cust_num" required>
                            </div> -->
                            <div class="mb-3">
                                <label for="cust_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="cust_email" name="cust_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="province" class="form-label">Province</label>
                                <select id="province" name="province" class="form-select" required>
                                    <option value="">Select Province</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <select id="city" name="city" class="form-select" required>
                                    <option value="">Select City</option>
                                </select>
                                <input type="hidden" name="location_id" id="location_id" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    <?php else: ?>
                        <div class="container-fluid">
                            <h2>Welcome, <?= htmlspecialchars($cust_name); ?>!</h2>
                            <p>Contact Number: <?= htmlspecialchars($cust_num); ?></p>
                            <p>Email Address: <?= htmlspecialchars($cust_email); ?></p>
                            <p>Address: <?= htmlspecialchars($city_name); ?></p>
                            <p>You can now proceed with your purchases!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div><hr>
        <div class="row m-0">
            <div class="col col-12 p-0">
                <?php include("../modules/sales_management_system/transaction/available_product.php"); ?>
            </div>
        </div>
        <?php include("../includes/customer/footer.php"); ?>
    </div>

    <div class="page" id="Orders" style="display: none;">
        <?php include("../includes/customer/header.php"); ?>
        <h1>Find your orders here</h1>
        <div class="container-fluid mt-5">
            <div class="row">
                <!-- Button 1 -->
                <div class="col-md-3 mb-3">
                    <a href="../modules/sales_management_system/transaction/customer/order.php" class="text-decoration-none"> 
                        <div class="card p-4">
                            <i class="bi bi-grid icon"></i>
                            <h5 class="card-title">Order</h5>
                            <p class="card-text">Waiting for Approval</p>
                        </div>
                    </a>
                </div>
                <!-- Button 2 -->
                <div class="col-md-3 mb-3">
                    <a href="../modules/sales_management_system/transaction/customer/toShip.php" class="text-decoration-none">
                        <div class="card p-4">
                            <i class="bi bi-box icon"></i>
                            <h5 class="card-title">To Receive</h5>
                            <p class="card-text">Preparing to Ship</p>
                        </div>
                    </a>
                </div>
                <!-- Button 3 -->
                <div class="col-md-3 mb-3">
                    <a href="../modules/sales_management_system/transaction/customer/completed.php" class="text-decoration-none">
                        <div class="card p-4">
                            <i class="bi bi-house-door icon"></i>
                            <h5 class="card-title">Completed</h5>
                            <p class="card-text">Order Received</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="fixed-bottom">
            <?php include("../includes/customer/footer.php"); ?>
        </div>

    </div>


    <script>
        $(document).ready(function() {
            // Fetch and populate province and city data
            $.ajax({
                url: "../includes/get_location_data.php",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    var provinces = data.provinces;
                    var cities = data.cities;
                    var provinceDropdown = $("#province");
                    var cityDropdown = $("#city");

                    // Populate province dropdown
                    provinces.forEach(function(province) {
                        provinceDropdown.append(
                            $("<option>").val(province.Province).text(province.Province)
                        );
                    });

                    // Event listener for province change
                    provinceDropdown.change(function() {
                        var selectedProvince = $(this).val();
                        cityDropdown.empty();
                        cityDropdown.append("<option value=''>Select City</option>");

                        // Filter and populate city dropdown based on selected province
                        cities.forEach(function(city) {
                            if (city.Province === selectedProvince) {
                                cityDropdown.append(
                                    $("<option>").val(city.LocationID).text(city.City)
                                );
                            }
                        });
                    });
                },
                error: function() {
                    alert("Error: Could not retrieve location data.");
                }
            });

            // When city is selected, set location_id
            $("#city").change(function() {
                var locationID = $(this).val();
                $("#location_id").val(locationID);
            });
        });
    </script>
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
