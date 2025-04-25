<?php
//store_update.php
session_start();
include("./../../../includes/cdn.html"); 
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    $_SESSION['alert'] = 'You must be logged in to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ./../../../login.php");
    exit;
}

// Check if a Store ID is provided in the URL
if (isset($_GET['store_id'])) {
    $store_id = $_GET['store_id'];

    // Fetch existing store record for display
    $store_query = "SELECT s.*, l.Province, l.City 
                    FROM StoreInfoTb s 
                    JOIN LocationTb l ON s.LocationID = l.LocationID 
                    WHERE s.StoreInfoID = :store_id";
    $store_stmt = $conn->prepare($store_query);
    $store_stmt->bindParam(':store_id', $store_id, PDO::PARAM_INT);
    $store_stmt->execute();
    $store = $store_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$store) {
        $_SESSION['alert'] = 'Store not found.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: store_read.php");
        exit;
    }
} else {
    $_SESSION['alert'] = 'Invalid store ID.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: store_read.php");
    exit;
}

// Handle form submission for updating store record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location_id = $_POST['location_id'];
    $store_gcash_num = $_POST['store_gcash_num'];
    $store_delivery_fee = $_POST['store_delivery_fee'];
    $store_latitude = $_POST['store_latitude'];
    $store_longitude = $_POST['store_longitude'];

    // Check if file is uploaded
    $qr_blob = $store['StoreGcashQR']; // Default to existing QR
    if (isset($_FILES['store_gcash_qr']) && $_FILES['store_gcash_qr']['error'] === UPLOAD_ERR_OK) {
        $qr_file = $_FILES['store_gcash_qr']['tmp_name'];
        $qr_blob = file_get_contents($qr_file);
    }

    // Validate coordinates
    if (!is_numeric($store_latitude) || !is_numeric($store_longitude) || 
        $store_latitude < -90 || $store_latitude > 90 || 
        $store_longitude < -180 || $store_longitude > 180) {
        $_SESSION['alert'] = 'Error: Invalid latitude or longitude.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: store_update.php?store_id=" . $store_id);
        exit;
    }

    // Create coordinates string
    $coordinates = "$store_latitude, $store_longitude";

    // Update the store record
    $update_query = "UPDATE StoreInfoTb 
                     SET LocationID = :location_id,
                         StoreGcashNum = :store_gcash_num,
                         StoreGcashQR = :store_gcash_qr,
                         StoreDeliveryFee = :store_delivery_fee,
                         StoreExactCoordinates = :store_coordinates
                     WHERE StoreInfoID = :store_id";
    
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':store_gcash_num', $store_gcash_num);
    $update_stmt->bindParam(':store_gcash_qr', $qr_blob, PDO::PARAM_LOB);
    $update_stmt->bindParam(':store_delivery_fee', $store_delivery_fee);
    $update_stmt->bindParam(':store_coordinates', $coordinates);
    $update_stmt->bindParam(':store_id', $store_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        $_SESSION['alert'] = 'Store updated successfully!';
        $_SESSION['alert_type'] = 'success';
        header("Location: store_read.php");
        exit;
    } else {
        $_SESSION['alert'] = 'Error: Could not update store.';
        $_SESSION['alert_type'] = 'danger';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Store</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Update Store</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Store">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Store</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to update this store?');">
            <h6>Update Store Information</h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <select id="province" name="province" class="form-control" required>
                            <option value="">Select Province</option>
                            <!-- Options will be populated via AJAX -->
                        </select>
                        <label for="province">Province</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select id="city" name="city" class="form-control" required>
                            <option value="">Select City</option>
                            <!-- Options will be populated via AJAX -->
                        </select>
                        <label for="city">City</label>
                    </div>
                </div>
                <input type="hidden" id="location_id" name="location_id" value="<?= htmlspecialchars($store['LocationID']) ?>">
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="store_gcash_num" name="store_gcash_num" value="<?= htmlspecialchars($store['StoreGcashNum']) ?>" required>
                        <label for="store_gcash_num">GCash Number</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" step="0.01" class="form-control" id="store_delivery_fee" name="store_delivery_fee" value="<?= htmlspecialchars($store['StoreDeliveryFee']) ?>" required>
                        <label for="store_delivery_fee">Delivery Fee</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="store_gcash_qr">GCash QR Code:</label>
                <input type="file" class="form-control" id="store_gcash_qr" name="store_gcash_qr">
                <small class="text-muted">Leave empty to keep the existing QR code</small>
            </div>

            <div class="mb-3">
                <label>Store Location</label>
                <div id="map" style="height: 400px;" class="mb-2"></div>
                <input type="hidden" id="store_latitude" name="store_latitude">
                <input type="hidden" id="store_longitude" name="store_longitude">
            </div>

            <button class="btn btn-success w-100 mb-2" type="submit">Update Store</button>
            <a class="btn btn-secondary w-100" href="store_read.php">Cancel</a>
        </form>
    </div>

    <script>
    $(document).ready(function() {
    // Initialize map with existing coordinates
    var coordinates = "<?= $store['StoreExactCoordinates'] ?>".split(',');
    var map = L.map('map').setView([coordinates[0], coordinates[1]], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker;

    function updateMarker(latlng) {
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng).addTo(map);
        }
        document.getElementById('store_latitude').value = latlng.lat;
        document.getElementById('store_longitude').value = latlng.lng;
    }

    // Set initial marker
    updateMarker(L.latLng(coordinates[0], coordinates[1]));

    // Update marker on map click
    map.on('click', function(e) {
        updateMarker(e.latlng);
    });

    // Load location data via AJAX
    $.ajax({
        url: "./../../../includes/get_location_data.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            var provinces = data.provinces;
            var cities = data.cities;
            
            // Populate province dropdown
            provinces.forEach(function(province) {
                $("#province").append(
                    $("<option>", {
                        value: province.Province,
                        text: province.Province,
                        selected: province.Province === "<?= $store['Province'] ?>"
                    })
                );
            });

            // Function to populate cities
            function populateCities(selectedProvince) {
                $("#city").empty().append("<option value=''>Select City</option>");
                cities.forEach(function(city) {
                    if (city.Province === selectedProvince) {
                        $("#city").append(
                            $("<option>", {
                                value: city.LocationID,
                                text: city.City,
                                selected: city.City === "<?= $store['City'] ?>"
                            })
                        );
                    }
                });
            }

            // Function to geocode location and update map
            function geocodeLocation(province, city) {
                if (!province || !city) return;
                
                const searchQuery = `${city}, ${province}, Philippines`;
                const geocoder = L.Control.Geocoder.nominatim({
                    geocodingQueryParams: {
                        countrycodes: 'ph',
                        limit: 1
                    }
                });

                geocoder.geocode(searchQuery, function(results) {
                    if (results && results.length > 0) {
                        const result = results[0];
                        const latlng = result.center;
                        
                        // Update marker and center map
                        updateMarker(latlng);
                        map.setView(latlng, 14);
                    }
                });
            }

            // Initial population of cities
            populateCities("<?= $store['Province'] ?>");

            // Handle province change
            $("#province").change(function() {
                const selectedProvince = $(this).val();
                populateCities(selectedProvince);
                
                // Clear city selection
                $("#city").val('');
                $("#location_id").val('');
            });

            // Handle city change
            $("#city").change(function() {
                const selectedCity = $("#city option:selected").text();
                const selectedProvince = $("#province").val();
                $("#location_id").val($(this).val());
                
                // Update map based on selected location
                geocodeLocation(selectedProvince, selectedCity);
            });

            // Initial map location if province and city are set
            const initialProvince = $("#province").val();
            const initialCity = $("#city option:selected").text();
            if (initialProvince && initialCity) {
                geocodeLocation(initialProvince, initialCity);
            }
        },
        error: function() {
            alert("Error loading location data");
        }
    });
});
    </script>
</body>
</html>