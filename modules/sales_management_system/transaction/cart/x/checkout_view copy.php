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
        #map { height: 200px; }
    </style>
</head>
<body>
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4 border rounded border-secondary p-4">
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
            <div class="col-12 col-md-6 col-lg-4 border border-secondary mt-3 mt-md-0">
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

// Initialize the Leaflet map
var map = L.map('map').setView([13.9444821, 121.1332977], 13); // Default center if geolocation fails
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(map);
console.log("Store Coordinates:", storeCoords);
console.log("Exact Coordinates (on click):", document.getElementById("exact_coordinates").value);

// Marker for the selected location
var marker;

// Add a marker for the store location (endpoint)
var storeMarker = L.marker(storeCoords, { 
    draggable: false, 
    icon: L.divIcon({ className: 'hidden-store-marker' }) // Use a custom icon to hide the marker
}).addTo(map);

// Calculate distance between two points using the Haversine formula
function calculateDistance(lat1, lng1, lat2, lng2) {
    var rad = function(x) { return x * Math.PI / 180; };
    var R = 6378137; // Earth’s mean radius in meters
    var dLat = rad(lat2 - lat1);
    var dLng = rad(lng2 - lng1);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(rad(lat1)) * Math.cos(rad(lat2)) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var distance = R * c; // returns the distance in meters
    return distance;
}

// Update delivery fee and grand total
function updateDeliveryFeeAndTotal(distance) {
    var deliveryFee = (storeBaseFee * (distance / 1000)).toFixed(2); // Fee per km
    var grandTotal = (parseFloat(deliveryFee) + totalPrice).toFixed(2);

    document.getElementById("delivery_fee").textContent = "Delivery Fee: ₱" + deliveryFee;
    document.getElementById("grand_total").textContent = "Grand Total: ₱" + grandTotal;

    // Send AJAX request to update the session
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

// Get the user's current location
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
        var userLat = position.coords.latitude;
        var userLng = position.coords.longitude;

        // Center the map on the user's current location
        map.setView([userLat, userLng], 13);

        // Add a marker for the user's current location
        marker = L.marker([userLat, userLng], { draggable: true }).addTo(map)
            .bindPopup('Your Location').openPopup();

        // Update hidden input when the marker is dragged
        marker.on('dragend', function(event) {
            var latlng = event.target.getLatLng();
            document.getElementById("exact_coordinates").value = latlng.lat + ',' + latlng.lng;

            // Calculate and display the distance
            var distance = calculateDistance(latlng.lat, latlng.lng, storeCoords[0], storeCoords[1]);
            updateDeliveryFeeAndTotal(distance);
        });

        // Set the initial coordinates
        document.getElementById("exact_coordinates").value = userLat + ',' + userLng;

        // Calculate and display the initial distance
        var initialDistance = calculateDistance(userLat, userLng, storeCoords[0], storeCoords[1]);
        updateDeliveryFeeAndTotal(initialDistance);
    });
}

// Allow the user to place the marker by clicking on the map
map.on('click', function(e) {
    if (marker) {
        marker.setLatLng(e.latlng).update();
    } else {
        marker = L.marker(e.latlng, { draggable: true }).addTo(map);
    }

    document.getElementById("exact_coordinates").value = e.latlng.lat + ',' + e.latlng.lng;

    // Calculate and display the distance
    var clickedDistance = calculateDistance(e.latlng.lat, e.latlng.lng, storeCoords[0], storeCoords[1]);
    updateDeliveryFeeAndTotal(clickedDistance);
});
</script>

</body>
</html>
