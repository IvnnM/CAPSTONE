<?php
// store_create.php
session_start();
include("./../../../includes/cdn.html"); 
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = './../../../login.php';</script>";
    exit;
}

// Get user ID from session
$user_id = isset($_SESSION['EmpID']) ? $_SESSION['EmpID'] : $_SESSION['AdminID'];

// // Check if store already exists
// $check_store_query = "SELECT COUNT(*) FROM StoreInfoTb";
// $check_store_stmt = $conn->prepare($check_store_query);
// $check_store_stmt->execute();
// $store_count = $check_store_stmt->fetchColumn();

// if ($store_count > 0) {
//     echo "<script>alert('A store already exists. Please update the existing store instead.'); 
//     window.location.href = 'store_read.php';</script>";
//     exit;
// }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_gcash_num = $_POST['store_gcash_num'];
    $location_id = $_POST['location_id'];
    $store_delivery_fee = $_POST['store_delivery_fee'];
    $store_latitude = $_POST['store_latitude'];
    $store_longitude = $_POST['store_longitude'];

    // Process GCash QR code upload
    if (isset($_FILES['store_gcash_qr']) && $_FILES['store_gcash_qr']['error'] === UPLOAD_ERR_OK) {
        $qr_file = $_FILES['store_gcash_qr']['tmp_name'];
        $qr_blob = file_get_contents($qr_file);
    } else {
        echo "<script>alert('Error: GCash QR code is required.');</script>";
        exit;
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

    // Insert new store record
    $insert_store_query = "INSERT INTO StoreInfoTb (LocationID, StoreGcashNum, StoreGcashQR, StoreDeliveryFee, StoreExactCoordinates) 
                          VALUES (:location_id, :store_gcash_num, :store_gcash_qr, :store_delivery_fee, :store_exact_coordinates)";
    $insert_store_stmt = $conn->prepare($insert_store_query);
    $insert_store_stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
    $insert_store_stmt->bindParam(':store_gcash_num', $store_gcash_num);
    $insert_store_stmt->bindParam(':store_gcash_qr', $qr_blob, PDO::PARAM_LOB);
    $insert_store_stmt->bindParam(':store_delivery_fee', $store_delivery_fee, PDO::PARAM_STR);
    $insert_store_stmt->bindParam(':store_exact_coordinates', $coordinates);

    if ($insert_store_stmt->execute()) {
        echo "<script>alert('Store created successfully!'); 
        window.location.href = 'store_read.php';</script>";
    } else {
        echo "<script>alert('Error: Could not create store.');</script>";
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
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h1 class="mb-4">Create Store</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../views/personnel_view.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Store</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
            <h6>Set Store Address</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <select id="province" name="province" class="form-control" required>
                            <option value="">Select Province</option>
                        </select>
                        <label for="province">Province</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select id="city" name="city" class="form-control" required>
                            <option value="">Select City</option>
                        </select>
                        <label for="city">City</label>
                    </div>
                    <input type="hidden" id="location_id" name="location_id">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" id="store_gcash_num" name="store_gcash_num" class="form-control" required>
                        <label for="store_gcash_num">GCash Number</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" id="store_delivery_fee" name="store_delivery_fee" class="form-control" required>
                        <label for="store_delivery_fee">Delivery Fee</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="store_gcash_qr">GCash QR Code:</label>
                <input type="file" id="store_gcash_qr" name="store_gcash_qr" class="form-control" required>
            </div>

            <h6>Store Location</h6>
            <div id="map" style="height: 400px;" class="mb-3"></div>
            <input type="hidden" id="store_latitude" name="store_latitude">
            <input type="hidden" id="store_longitude" name="store_longitude">

            <button type="submit" class="btn btn-success w-100 mb-2">Create Store</button>
            <a href="store_read.php" class="btn btn-secondary w-100">Cancel</a>
        </form>
    </div>

    <script>
        $(document).ready(function() {
    // Initialize map
    var map = L.map('map').setView([14.5995, 120.9842], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var marker;

    function updateMarker(latlng) {
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng, { draggable: true }).addTo(map);
            
            // Update coordinates when marker is dragged
            marker.on('dragend', function(e) {
                const position = e.target.getLatLng();
                $('#store_latitude').val(position.lat.toFixed(6));
                $('#store_longitude').val(position.lng.toFixed(6));
            });
        }
        $('#store_latitude').val(latlng.lat.toFixed(6));
        $('#store_longitude').val(latlng.lng.toFixed(6));
        map.setView(latlng, 14);
    }

    // Handle map clicks
    map.on('click', function(e) {
        updateMarker(e.latlng);
    });

    function geocodeLocation(province, city) {
        if (!province || !city) {
            console.log("Missing province or city");
            return;
        }
        
        console.log("Searching for:", city, province);
        const searchQuery = `${city}, ${province}, Philippines`;
        
        // Show loading state
        const $citySelect = $("#city");
        $citySelect.prop('disabled', true);
        
        // Use Nominatim geocoding service
        $.ajax({
            url: `https://nominatim.openstreetmap.org/search`,
            method: 'GET',
            data: {
                q: searchQuery,
                format: 'json',
                limit: 1,
                countrycodes: 'ph'
            },
            success: function(results) {
                console.log("Search results:", results);
                $citySelect.prop('disabled', false);
                
                if (results && results.length > 0) {
                    const result = results[0];
                    const latlng = L.latLng(result.lat, result.lon);
                    
                    console.log("Found coordinates:", latlng);
                    updateMarker(latlng);
                    
                    // Zoom to the area
                    if (result.boundingbox) {
                        const bbox = [
                            [result.boundingbox[0], result.boundingbox[2]],
                            [result.boundingbox[1], result.boundingbox[3]]
                        ];
                        map.fitBounds(bbox);
                    } else {
                        map.setView(latlng, 14);
                    }
                } else {
                    console.error('Location not found:', searchQuery);
                    alert('Could not find exact location on map. Please click on the map to mark the store location manually.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Search error:", error);
                $citySelect.prop('disabled', false);
                alert('Error finding location. Please click on the map to mark the store location manually.');
            }
        });
    }

    // Load location data
    $.ajax({
        url: "./../../../includes/get_location_data.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            console.log("Location data loaded:", data);
            var provinces = data.provinces;
            var cities = data.cities;
            var provinceDropdown = $("#province");
            var cityDropdown = $("#city");

            // Populate provinces
            provinces.forEach(function(province) {
                provinceDropdown.append(
                    $("<option>").val(province.Province).text(province.Province)
                );
            });

            // Handle province change
            provinceDropdown.change(function() {
                var selectedProvince = $(this).val();
                console.log("Province selected:", selectedProvince);
                
                // Reset city dropdown
                cityDropdown.empty();
                cityDropdown.append("<option value=''>Select City</option>");
                
                // Populate cities for selected province
                cities.forEach(function(city) {
                    if (city.Province === selectedProvince) {
                        cityDropdown.append(
                            $("<option>").val(city.LocationID).text(city.City)
                        );
                    }
                });
            });

            // Handle city change
            cityDropdown.change(function() {
                const locationId = $(this).val();
                if (!locationId) return;

                $("#location_id").val(locationId);
                
                const selectedCity = $("#city option:selected").text();
                const selectedProvince = $("#province").val();
                console.log("City selected:", selectedCity, "in", selectedProvince);
                
                // Search for the selected location
                geocodeLocation(selectedProvince, selectedCity);
            });
        },
        error: function(xhr, status, error) {
            console.error("Error loading location data:", error);
            alert("Error: Could not retrieve location data. Please try refreshing the page.");
        }
    });

    // Form validation before submit
    $('form').on('submit', function(e) {
        if (!$('#store_latitude').val() || !$('#store_longitude').val()) {
            e.preventDefault();
            alert('Please select a store location on the map');
            return false;
        }
    });
});
    </script>
</body>
</html>