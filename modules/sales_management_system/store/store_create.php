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
    echo "<script>alert('A store already exists for this location.'); window.location.href = 'store_read.php';</script>";
    exit;
}

// Handle form submission for creating a new store
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_gcash_num = $_POST['store_gcash_num'];
    $location_id = $_POST['location_id'];
    $store_delivery_fee = $_POST['store_delivery_fee'];
    $store_latitude = $_POST['store_latitude'];
    $store_longitude = $_POST['store_longitude'];

    // Check if file is uploaded and process the BLOB for StoreGcashQR
    if (isset($_FILES['store_gcash_qr']) && $_FILES['store_gcash_qr']['error'] === UPLOAD_ERR_OK) {
        $qr_file = $_FILES['store_gcash_qr']['tmp_name'];
        $qr_blob = file_get_contents($qr_file);
    } else {
        echo "<script>alert('Error: Please upload a valid GCash QR Code image.');</script>";
        exit;
    }

    // Debug: Log latitude and longitude values
    error_log("Latitude: $store_latitude, Longitude: $store_longitude");

    // Validate coordinates
    if (!is_numeric($store_latitude) || !is_numeric($store_longitude) || 
        $store_latitude < -90 || $store_latitude > 90 || 
        $store_longitude < -180 || $store_longitude > 180) {
        echo "<script>alert('Error: Invalid latitude or longitude.');</script>";
        exit;
    }

    // Create coordinates string
    $coordinates = "$store_latitude, $store_longitude";

    // Insert new store record
    $insert_store_query = "INSERT INTO StoreInfoTb (LocationID, StoreGcashNum, StoreGcashQR, StoreDeliveryFee, StoreExactCoordinates) 
                           VALUES (:location_id, :store_gcash_num, :store_gcash_qr, :store_delivery_fee, :store_exact_coordinates)";
    $insert_store_stmt = $conn->prepare($insert_store_query);
    $insert_store_stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
    $insert_store_stmt->bindParam(':store_gcash_num', $store_gcash_num);
    $insert_store_stmt->bindParam(':store_gcash_qr', $qr_blob, PDO::PARAM_LOB);
    $insert_store_stmt->bindParam(':store_delivery_fee', $store_delivery_fee, PDO::PARAM_STR);
    $insert_store_stmt->bindParam(':store_exact_coordinates', $coordinates); // Bind the coordinates

    // Execute and check for errors
    if ($insert_store_stmt->execute()) {
        echo "<script>alert('Store created successfully!'); window.location.href = 'store_update.php';</script>";
    } else {
        $errorInfo = $insert_store_stmt->errorInfo();
        echo "<script>alert('Error: Could not create the store. SQLSTATE: {$errorInfo[0]}, Error Code: {$errorInfo[1]}, Error Message: {$errorInfo[2]}');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Store</title>
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
    var map = L.map('map').setView([13.4, 122.56], 6); // Default view

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Variable to store the user's initial coordinates
    var initialLat, initialLng;

    // Function to center the map on the user's location
    function centerMapOnUserLocation(position) {
        initialLat = position.coords.latitude;
        initialLng = position.coords.longitude;
        map.setView([initialLat, initialLng], 12); // Set zoom level to 12

        // Set the latitude and longitude values immediately when the location is found
        document.getElementById('store_latitude').value = initialLat;
        document.getElementById('store_longitude').value = initialLng;

        // Optionally, add a marker for the user's location
        L.marker([initialLat, initialLng]).addTo(map).bindPopup("You are here").openPopup();
    }

    // Get user's location using Geolocation API
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(centerMapOnUserLocation, function() {
            alert("Unable to retrieve your location.");
        });
    } else {
        alert("Geolocation is not supported by this browser.");
    }

    // Marker to show the selected location
    var marker;

    // Handle map click to place a marker
    map.on('click', function(e) {
        // Remove the previous marker if it exists
        if (marker) {
            map.removeLayer(marker);
        }

        // Add the new marker
        marker = L.marker(e.latlng).addTo(map);
        
        // Set the latitude and longitude values immediately when the marker is placed
        document.getElementById('store_latitude').value = e.latlng.lat;
        document.getElementById('store_longitude').value = e.latlng.lng;

        // Log the selected coordinates to the console
        console.log("Selected coordinates: ", e.latlng.lat, e.latlng.lng);
    });


    });

    function confirmCreation(event) {
        if (!confirm('Are you sure you want to create this store?')) {
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
    <h1 class="mb-4">Store Form</h1>
    <hr style="border-top: 1px solid white;">
    <form method="POST" action="" enctype="multipart/form-data" onsubmit="confirmCreation(event)">
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
        <div class="mb-3">
            <label for="location_id">Location ID:</label>
            <input type="hidden" id="location_id" name="location_id" required>
        </div>

        <h6>Store Details</h6>
        <div class="mb-3">
            <label for="store_gcash_num">GCash Number:</label>
            <input type="text" id="store_gcash_num" name="store_gcash_num" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="store_delivery_fee">Delivery Fee:</label>
            <input type="text" id="store_delivery_fee" name="store_delivery_fee" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="store_gcash_qr">Upload GCash QR Code:</label>
            <input type="file" id="store_gcash_qr" name="store_gcash_qr" accept="image/*" class="form-control" required>
        </div>

        <h6>Select Location on Map</h6>
        <div id="map" style="height: 400px; width: 100%;"></div>
        <input type="hidden" id="store_latitude" name="store_latitude" required>
        <input type="hidden" id="store_longitude" name="store_longitude" required>

        <button type="submit" class="btn btn-primary mt-3">Create Store</button>
    </form>
</body>
</html>
