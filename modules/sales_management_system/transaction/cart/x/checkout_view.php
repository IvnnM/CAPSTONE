<?php
include("./../../../../includes/cdn.html");
include("checkout_process.php"); // Include the PHP logic

// Fetch the store's exact coordinates using PDO
$query_store = "SELECT StoreExactCoordinates, StoreDeliveryFee FROM StoreInfoTb LIMIT 1";
$stmt_store = $conn->prepare($query_store);
$stmt_store->execute();
$store_data = $stmt_store->fetch(PDO::FETCH_ASSOC);

// Check if store data was retrieved successfully
if ($store_data && !empty($store_data['StoreExactCoordinates'])) {
    $store_coords = explode(',', $store_data['StoreExactCoordinates']);
    $store_base_fee = $store_data['StoreDeliveryFee'];
} else {
    // Set default store coordinates if the query fails or returns no results
    $store_coords = [13.9653472,121.0304496]; // Example default coordinates
    $store_base_fee = 50; // Default base fee
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="./../../../../assets/css/customer.css">
    <style>
        #map { height: 500px; }
    </style>
</head>
<body>
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-9 border rounded border-secondary p-4">
                <h2 class="text-center">Checkout</h2>
                <hr>
                <form method="POST" action="checkout_process.php">
                    <div class="mb-3">
                        <label for="cust_num" class="form-label">Contact Number:</label>
                        <input type="text" class="form-control" name="cust_num" id="cust_num" required>
                    </div>

                    <div class="mb-3">
                        <label for="cust_note" class="form-label">Customer Note:</label>
                        <textarea class="form-control" name="cust_note" id="cust_note" rows="3" placeholder="Any specific instructions"></textarea>
                    </div>

                    <h5>Total Price: ₱<?= number_format($total_price, 2) ?></h5>
                    <h5 id="delivery_fee">Delivery Fee: ₱0.00</h5>
                    <hr>
                    <h5 id="grand_total"><strong>Grand Total: ₱<?= number_format($total_price, 2) ?></strong></h5>

                    <div class="mb-3">
                        <div class="location-selector mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="useCurrentLocation">
                                <label class="form-check-label" for="useCurrentLocation">
                                    Use Current Location
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Uncheck to manually select location on map
                            </small>
                        </div>  
                        <label for="map" class="form-label">Select Location:</label>
                        <div id="map"></div>
                        <input type="hidden" name="exact_coordinates" id="exact_coordinates" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="payment_confirmation" name="payment_confirmation" required>
                        <label class="form-check-label" for="payment_confirmation">
                            I confirm that I understand I need to pay before proceeding to checkout.
                        </label>

                    </div>
                    <button type="submit" class="btn btn-primary w-100">Proceed to Checkout</button>
                </form>
                <button type="button" class="btn btn-secondary w-100 mt-2" onclick="window.history.back();">Cancel</button>
            </div>
            <div class="col-9 border border-secondary mt-3 mt-md-0 p-4">
                <?php include('../transac_payment.php'); ?>
            </div>
        </div>
    </div>

    <?php include("../../../../includes/customer/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script>
        
    var storeCoords = <?php echo json_encode($store_coords); ?>;
    var storeBaseFee = <?= $store_base_fee ?>;
    var totalPrice = <?= $total_price ?>;

    // Select the form element
    const form = document.querySelector('form'); // Replace with your form selector
    const exactCoordinates = document.getElementById('exact_coordinates');

    // Add an event listener for form submission
    form.addEventListener('submit', function(e) {
        // Check if exact_coordinates is empty
        if (!exactCoordinates.value) {
            e.preventDefault(); // Prevent form from submitting
            alert("Please select a location on the map or enable 'Use Current Location'.");
        }
    });

        // Initialize the Leaflet map centered on the Calabarzon region with a zoom level of 8
        const map = L.map('map', {
            preferCanvas: true
        }).setView([14.0600, 121.4900], 8);

        // Google Maps tile layer
        const googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; <a href="https://www.google.com/intl/en_us/help/terms_maps.html">Google</a>'
        });

    googleStreets.addTo(map);

    // Variables for location tracking
    var marker;
    var locationWatcher = null;

    // Add a marker for the store location (endpoint)
    var storeMarker = L.marker(storeCoords, { 
        draggable: false, 
        icon: L.divIcon({ className: 'hidden-store-marker' })
    }).addTo(map);

    // Calculate distance between two points using the Haversine formula
    function calculateDistance(lat1, lng1, lat2, lng2) {
        var rad = function(x) { return x * Math.PI / 180; };
        var R = 6378137; // Earth's mean radius in meters
        var dLat = rad(lat2 - lat1);
        var dLng = rad(lng2 - lng1);
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(rad(lat1)) * Math.cos(rad(lat2)) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var distance = R * c;
        return distance;
    }

    // Update delivery fee and grand total
    function updateDeliveryFeeAndTotal(distance) {
        var deliveryFee = (storeBaseFee * (distance / 1000)).toFixed(2);
        var grandTotal = (parseFloat(deliveryFee) + totalPrice).toFixed(2);

        document.getElementById("delivery_fee").textContent = "Delivery Fee: ₱" + deliveryFee;
        document.getElementById("grand_total").textContent = "Grand Total: ₱" + grandTotal;

        $.ajax({
            type: 'POST',
            url: 'update_delivery_fee.php',
            data: { delivery_fee: deliveryFee },
            success: function(response) {
                var data = JSON.parse(response);
                if (!data.success) {
                    console.error(data.message);
                }
            },
            error: function() {
                console.error("Error updating delivery fee.");
            }
        });
    }

    // Function to start location tracking
    function startLocationTracking() {
        if (navigator.geolocation) {
            locationWatcher = navigator.geolocation.watchPosition(function(position) {
                var userLat = position.coords.latitude;
                var userLng = position.coords.longitude;

                // Center the map on the user's current location
                map.setView([userLat, userLng], 13);

                // Update or create marker
                if (marker) {
                    marker.setLatLng([userLat, userLng]);
                } else {
                    marker = L.marker([userLat, userLng], { draggable: true }).addTo(map)
                        .bindPopup('Your Location').openPopup();
                }

                // Update hidden input
                document.getElementById("exact_coordinates").value = userLat + ',' + userLng;

                // Calculate and display the distance
                var distance = calculateDistance(userLat, userLng, storeCoords[0], storeCoords[1]);
                updateDeliveryFeeAndTotal(distance);

            }, function(error) {
                alert("Error getting your location: " + error.message);
                document.getElementById('useCurrentLocation').checked = false;
            });
        } else {
            alert("Geolocation is not supported by your browser.");
            document.getElementById('useCurrentLocation').checked = false;
        }
    }

    // Function to stop location tracking
    function stopLocationTracking() {
        if (locationWatcher !== null) {
            navigator.geolocation.clearWatch(locationWatcher);
            locationWatcher = null;
        }
    }

    // Function to handle marker drag
    function handleMarkerDrag(event) {
        var latlng = event.target.getLatLng();
        document.getElementById("exact_coordinates").value = latlng.lat + ',' + latlng.lng;
        var distance = calculateDistance(latlng.lat, latlng.lng, storeCoords[0], storeCoords[1]);
        updateDeliveryFeeAndTotal(distance);
    }

    // Event listener for checkbox
    document.getElementById('useCurrentLocation').addEventListener('change', function(e) {
        if (this.checked) {
            startLocationTracking();
        } else {
            stopLocationTracking();
            if (marker) {
                marker.dragging.enable();
            }
        }
    });

    // Map click event handler (only active when not using current location)
    map.on('click', function(e) {
        if (!document.getElementById('useCurrentLocation').checked) {
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng, { draggable: true }).addTo(map)
                    .bindPopup('Selected Location').openPopup();
            }

            document.getElementById("exact_coordinates").value = e.latlng.lat + ',' + e.latlng.lng;
            
            var clickedDistance = calculateDistance(e.latlng.lat, e.latlng.lng, storeCoords[0], storeCoords[1]);
            updateDeliveryFeeAndTotal(clickedDistance);

            marker.on('dragend', handleMarkerDrag);
        }
    });

    // Add validation for checkout form
    function validateLocationBeforeCheckout(event) {
        if (!marker) {
            event.preventDefault();
            alert('Please select a delivery location before proceeding to checkout.');
            return false;
        }
        return true;
    }

    // Add validation to checkout form
    document.addEventListener('DOMContentLoaded', function() {
        const checkoutForm = document.querySelector('form[name="checkoutForm"]');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', validateLocationBeforeCheckout);
        }
    });
    </script>

</body>
</html>
