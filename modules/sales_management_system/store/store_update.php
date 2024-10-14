<?php
session_start();
include("./../../../includes/cdn.html"); 
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Get user ID from session
$user_id = isset($_SESSION['EmpID']) ? $_SESSION['EmpID'] : $_SESSION['AdminID'];

// Query to get the existing store record without checking user location
$check_store_query = "
    SELECT s.*, l.LocationID 
    FROM StoreInfoTb s
    JOIN LocationTb l ON s.LocationID = l.LocationID
    LIMIT 1
";
$check_store_stmt = $conn->prepare($check_store_query);
$check_store_stmt->execute();
$existing_store = $check_store_stmt->fetch(PDO::FETCH_ASSOC);

// If no store exists, redirect or show an alert
if (!$existing_store) {
    echo "<script>alert('No store found.'); window.location.href = 'store_create.php';</script>";
    exit;
}

// Handle form submission for updating a store
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_gcash_num = $_POST['store_gcash_num'];
    $location_id = $_POST['location_id'];
    $store_delivery_fee = $_POST['store_delivery_fee'];
    $store_latitude = $_POST['store_latitude'];
    $store_longitude = $_POST['store_longitude'];

    // Check if file is uploaded and process the BLOB for StoreGcashQR
    $qr_blob = $existing_store['StoreGcashQR']; // Default to existing QR
    if (isset($_FILES['store_gcash_qr']) && $_FILES['store_gcash_qr']['error'] === UPLOAD_ERR_OK) {
        $qr_file = $_FILES['store_gcash_qr']['tmp_name'];
        $qr_blob = file_get_contents($qr_file);
    }

    // Validate coordinates
    if (!is_numeric($store_latitude) || !is_numeric($store_longitude) || 
        $store_latitude < -90 || $store_latitude > 90 || 
        $store_longitude < -180 || $store_longitude > 180) {
        echo "<script>alert('Error: Invalid latitude or longitude.');</script>";
        exit;
    }

    // Create coordinates string
    $coordinates = "$store_latitude, $store_longitude";

    // Update existing store record
    $update_store_query = "UPDATE StoreInfoTb SET 
                            LocationID = :location_id, 
                            StoreGcashNum = :store_gcash_num, 
                            StoreGcashQR = :store_gcash_qr, 
                            StoreDeliveryFee = :store_delivery_fee, 
                            StoreExactCoordinates = :store_exact_coordinates 
                           WHERE StoreInfoID = :store_id";
    $update_store_stmt = $conn->prepare($update_store_query);
    $update_store_stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
    $update_store_stmt->bindParam(':store_gcash_num', $store_gcash_num);
    $update_store_stmt->bindParam(':store_gcash_qr', $qr_blob, PDO::PARAM_LOB);
    $update_store_stmt->bindParam(':store_delivery_fee', $store_delivery_fee, PDO::PARAM_STR);
    $update_store_stmt->bindParam(':store_exact_coordinates', $coordinates);
    $update_store_stmt->bindParam(':store_id', $existing_store['StoreInfoID'], PDO::PARAM_INT); // Use the fetched store ID

    // Execute and check for errors
    if ($update_store_stmt->execute()) {
        echo "<script>alert('Store updated successfully!'); window.location.href = 'store_read.php';</script>";
    } else {
        $errorInfo = $update_store_stmt->errorInfo();
        echo "<script>alert('Error: Could not update the store. SQLSTATE: {$errorInfo[0]}, Error Code: {$errorInfo[1]}, Error Message: {$errorInfo[2]}');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Store</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script>
    $(document).ready(function() {
        // Fetch and populate province and city data
        $.ajax({
            url: "./../../../includes/get_location_data.php",
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

        // Update LocationID based on selected city
        $("#city").change(function() {
            $("#location_id").val($(this).val());
        });

        // Initialize Leaflet map
        var map = L.map('map').setView([<?php echo explode(",", $existing_store['StoreExactCoordinates'])[0]; ?>, <?php echo explode(",", $existing_store['StoreExactCoordinates'])[1]; ?>], 12);
        // Default view using existing coordinates

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Marker to show the existing location
        var marker;

        // Function to place a marker for the existing location
        function placeMarker(latlng) {
            // Remove the previous marker if it exists
            if (marker) {
                map.removeLayer(marker);
            }
            // Add the new marker
            marker = L.marker(latlng).addTo(map);
            document.getElementById('store_latitude').value = latlng.lat;
            document.getElementById('store_longitude').value = latlng.lng;
        }

        // Place marker for existing coordinates
        var existingCoords = "<?php echo $existing_store['StoreExactCoordinates'] ?>".split(",");
        var existingLatLng = new L.LatLng(existingCoords[0], existingCoords[1]);
        placeMarker(existingLatLng);


        // Handle map click to update the marker
        map.on('click', function(e) {
            placeMarker(e.latlng);
            console.log("Selected coordinates: ", e.latlng.lat, e.latlng.lng);
        });
    });

    function confirmUpdate(event) {
        if (!confirm('Are you sure you want to update this store?')) {
            event.preventDefault();
        }
    }
    </script>

    <style>
        label, .form-control {
            font-size: small;
        }
    </style>
</head>
<body>
    <h1 class="mb-4">Update Store</h1>
    <hr style="border-top: 1px solid white;">
    <form method="POST" action="" enctype="multipart/form-data" onsubmit="confirmUpdate(event)">
        <h6>Set Store Address</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="province">Province:</label>
                <select id="province" name="province" class="form-control" required>
                    <option value="">Select Province</option>
                    <!-- Options populated via AJAX -->
                </select>
            </div>
            <div class="col-md-6">
                <label for="city">City:</label>
                <select id="city" name="city" class="form-control" required>
                    <option value="">Select City</option>
                    <!-- Options populated via AJAX -->
                </select>
                <input type="hidden" id="location_id" name="location_id" value="<?php echo $existing_store['LocationID']; ?>">
            </div>
        </div>
        <div class="mb-3">
            <label for="store_gcash_num">GCash Number:</label>
            <input type="text" id="store_gcash_num" name="store_gcash_num" class="form-control" value="<?php echo $existing_store['StoreGcashNum']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="store_delivery_fee">Delivery Fee:</label>
            <input type="number" id="store_delivery_fee" name="store_delivery_fee" class="form-control" value="<?php echo $existing_store['StoreDeliveryFee']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="store_gcash_qr">GCash QR Code:</label>
            <input type="file" id="store_gcash_qr" name="store_gcash_qr" class="form-control">
        </div>
        <h6>Store Location</h6>
        <div id="map" style="height: 400px;"></div>
        <input type="hidden" id="store_latitude" name="store_latitude">
        <input type="hidden" id="store_longitude" name="store_longitude">
        <button type="submit" class="btn btn-primary mt-3">Update Store</button>
        <a href="store_read.php" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</body>
</html>
