<?php
session_start();
include("./../../../includes/cdn.php"); 
include("./../../../config/database.php");
// $_SESSION['EmpID']='1';
// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Use the logged-in employee or admin's ID from the session
$user_id = isset($_SESSION['EmpID']) ? $_SESSION['EmpID'] : $_SESSION['AdminID'];

// Fetch store details for display based on user location
$store_query = "
    SELECT s.*, CONCAT(l.Province, ', ', l.City) AS Location 
    FROM StoreInfoTb s
    JOIN LocationTb l ON s.LocationID = l.LocationID
    JOIN EmpTb e ON e.LocationID = l.LocationID OR e.EmpID = :user_id
    JOIN AdminTb a ON a.LocationID = l.LocationID OR a.AdminID = :user_id
";
$store_stmt = $conn->prepare($store_query);
$store_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$store_stmt->execute();
$store = $store_stmt->fetch(PDO::FETCH_ASSOC);

if (!$store) {
    echo "<script>alert('Store not found.'); window.history.back();</script>";
    exit;
}

// Handle form submission for updating store details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_gcash_num = $_POST['store_gcash_num'] ?? null;
    $location_id = $_POST['location_id'] ?? null; // Get the LocationID from form
    $store_delivery_fee = $_POST['store_delivery_fee'] ?? null; // Get the delivery fee

    // Handle the QR Code upload
    $qr_blob = null;
    if (isset($_FILES['store_gcash_qr']) && $_FILES['store_gcash_qr']['error'] === UPLOAD_ERR_OK) {
        $qr_file = $_FILES['store_gcash_qr']['tmp_name'];
        $qr_blob = file_get_contents($qr_file);  // Convert the file to BLOB
    }

    // Prepare the update query
    $update_query = "UPDATE StoreInfoTb SET ";
    $params = [];

    if (!empty($store_gcash_num)) {
        $update_query .= "StoreGcashNum = :store_gcash_num, ";
        $params[':store_gcash_num'] = $store_gcash_num;
    }

    if (!empty($qr_blob)) {
        $update_query .= "StoreGcashQR = :store_gcash_qr, ";
        $params[':store_gcash_qr'] = $qr_blob; // Update the QR code BLOB
    }

    if (!empty($location_id)) {
        $update_query .= "LocationID = :location_id, ";
        $params[':location_id'] = $location_id;
    }

    if (!empty($store_delivery_fee)) {
        $update_query .= "StoreDeliveryFee = :store_delivery_fee ";
        $params[':store_delivery_fee'] = $store_delivery_fee; // Bind delivery fee
    } else {
        // Remove the trailing comma if no delivery fee is updated
        $update_query = rtrim($update_query, ", ");
    }

    // Complete the update query with a WHERE clause
    $update_query .= " WHERE StoreInfoID = :store_info_id";
    $params[':store_info_id'] = $store['StoreInfoID'];

    // Prepare and execute the update query
    $update_stmt = $conn->prepare($update_query);
    foreach ($params as $key => &$val) {
        $update_stmt->bindParam($key, $val);
    }

    // Execute the update query
    if ($update_stmt->execute()) {
        echo "<script>alert('Store details updated successfully!'); window.location.href = 'store_update.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error: Could not update store details.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Store Information</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script>
        $(document).ready(function() {
            // Fetch and populate province and city data
            $.ajax({
                url: "./../../../includes/get_location_data.php", // Ensure this file returns location data
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
                        cityDropdown.empty(); // Clear existing cities
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

            // Update LocationID based on selected city
            $("#city").change(function() {
                $("#location_id").val($(this).val());
            });
        });

        function confirmUpdate(event) {
            if (!confirm('Are you sure you want to update store details?')) {
                event.preventDefault(); // Prevent form submission if user cancels confirmation
            }
        }
    </script>
</head>
<body>
    <h1 class="mb-4">Store Form</h1>
    <hr style="border-top: 1px solid white;">
    <form method="POST" action="" enctype="multipart/form-data" onsubmit="confirmUpdate(event)">
        <h6>Set Payment Information</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="store_gcash_num">Store GCash Number:</label>
                <input type="text" name="store_gcash_num" id="store_gcash_num" class="form-control" value="<?= htmlspecialchars($store['StoreGcashNum']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="store_delivery_fee">Store Delivery Fee per Km:</label>
                <input type="text" name="store_delivery_fee" id="store_delivery_fee" class="form-control" value="<?= htmlspecialchars($store['StoreDeliveryFee']) ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="store_gcash_qr">Store GCash QR Code (Image):</label>
                <input type="file" name="store_gcash_qr" id="store_gcash_qr" class="form-control" accept="image/*"> <!-- Made it optional -->
            </div>
        </div>
        <hr style="border-top: 1px solid white;">
        <h6>Set Store Address</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="province">Province:</label>
                <select id="province" name="province" class="form-control" required>
                    <option value="">Select Province</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="city">City:</label>
                <select id="city" name="city" class="form-control" required>
                    <option value="">Select City</option>
                </select>
            </div>
        </div>

        <!-- Hidden field to store the selected LocationID -->
        <input type="hidden" name="location_id" id="location_id" value="<?= htmlspecialchars($store['LocationID']) ?>" required>



        <button class="btn btn-success" type="submit">Update</button>
    </form>

    <br>
    <a href="store_read.php">Go to Store Information</a>
</body>
</html>
