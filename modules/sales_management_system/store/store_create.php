<?php
session_start();
include("./../../../includes/cdn.php"); 
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Check if a store record already exists for the user's location
$user_id = isset($_SESSION['EmpID']) ? $_SESSION['EmpID'] : $_SESSION['AdminID'];
$check_store_query = "
    SELECT * 
    FROM StoreInfoTb s
    JOIN LocationTb l ON s.LocationID = l.LocationID
    JOIN EmpTb e ON e.LocationID = l.LocationID OR e.EmpID = :user_id
    JOIN AdminTb a ON a.LocationID = l.LocationID OR a.AdminID = :user_id
";
$check_store_stmt = $conn->prepare($check_store_query);
$check_store_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$check_store_stmt->execute();
$existing_store = $check_store_stmt->fetch(PDO::FETCH_ASSOC);

// If a store already exists, redirect or show an alert
if ($existing_store) {
    echo "<script>alert('A store already exists for this location.'); window.location.href = 'store_update.php';</script>";
    exit;
}

// Handle form submission for creating a new store
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_gcash_num = $_POST['store_gcash_num'];
    $location_id = $_POST['location_id']; // Will be set by city selection
    $store_delivery_fee = $_POST['store_delivery_fee']; // New field for delivery fee

    // Check if file is uploaded and process the BLOB for StoreGcashQR
    if (isset($_FILES['store_gcash_qr']) && $_FILES['store_gcash_qr']['error'] === UPLOAD_ERR_OK) {
        $qr_file = $_FILES['store_gcash_qr']['tmp_name'];
        $qr_blob = file_get_contents($qr_file);  // Convert the file to BLOB
    } else {
        echo "<script>alert('Error: Please upload a valid GCash QR Code image.');</script>";
        exit;
    }

    // Insert new store record
    $insert_store_query = "INSERT INTO StoreInfoTb (LocationID, StoreGcashNum, StoreGcashQR, StoreDeliveryFee) 
                           VALUES (:location_id, :store_gcash_num, :store_gcash_qr, :store_delivery_fee)";
    $insert_store_stmt = $conn->prepare($insert_store_query);
    $insert_store_stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
    $insert_store_stmt->bindParam(':store_gcash_num', $store_gcash_num);
    $insert_store_stmt->bindParam(':store_gcash_qr', $qr_blob, PDO::PARAM_LOB);
    $insert_store_stmt->bindParam(':store_delivery_fee', $store_delivery_fee, PDO::PARAM_STR); // Bind the new delivery fee

    if ($insert_store_stmt->execute()) {
        echo "<script>alert('Store created successfully!'); window.location.href = 'store_update.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error: Could not create the store.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Store</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Fetch and populate province and city data
            $.ajax({
                url: "./../../../includes/get_location_data.php", // Make sure this file returns location data
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

        function confirmCreation(event) {
            if (!confirm('Are you sure you want to create this store?')) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <h3>Create Store</h3>

    <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
        <div style="color: red;"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>
    <?php if (isset($successMessage) && !empty($successMessage)): ?>
        <div style="color: green;"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" onsubmit="confirmCreation(event)">
        <label for="province">Province:</label>
        <select id="province" name="province" required>
            <option value="">Select Province</option>
        </select><br>

        <label for="city">City:</label>
        <select id="city" name="city" required>
            <option value="">Select City</option>
        </select><br>

        <!-- Hidden field to store the selected LocationID -->
        <input type="hidden" name="location_id" id="location_id" required>

        <label for="store_gcash_num">Store GCash Number:</label>
        <input type="text" name="store_gcash_num" id="store_gcash_num" required><br>

        <label for="store_gcash_qr">Store GCash QR Code (Image):</label>
        <input type="file" name="store_gcash_qr" id="store_gcash_qr" accept="image/*" required><br>

        <label for="store_delivery_fee">Store Delivery Fee per Km:</label>
        <input type="number" name="store_delivery_fee" id="store_delivery_fee" required step="0.01" min="0"><br>

        <button type="submit">Create Store</button>
    </form>
</body>
</html>
