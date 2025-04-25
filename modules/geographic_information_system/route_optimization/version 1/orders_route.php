<?php 
session_start();
include("../../../includes/cdn.html");
include("../../../config/database.php");

// Fetch the store's exact coordinates using PDO
$query_store = "SELECT StoreExactCoordinates FROM StoreInfoTb LIMIT 1";
$stmt_store = $conn->prepare($query_store);
$stmt_store->execute();
$store_data = $stmt_store->fetch(PDO::FETCH_ASSOC);

// Check if store data was retrieved successfully
if ($store_data && !empty($store_data['StoreExactCoordinates'])) {
    $store_coords = explode(',', $store_data['StoreExactCoordinates']);
} else {
    // Set default coordinates if the query fails or returns no results
    $store_coords = [28.2380, 83.9956]; // Example default coordinates
}

// Fetch multiple transaction coordinates and customer names using PDO
$query_transac = "SELECT ExactCoordinates, CustName FROM TransacTb WHERE Status = 'ToShip'";
$stmt_transac = $conn->prepare($query_transac);
$stmt_transac->execute();

$transactions = [];
while ($row = $stmt_transac->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($row['ExactCoordinates'])) {
        $coord = explode(',', $row['ExactCoordinates']);
        $transactions[] = ['coords' => $coord, 'name' => $row['CustName']];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Geolocation</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
  <style>
    body {
      margin: 0;
      padding: 0;
    }
  </style>
</head>

<body>
  <div id="map" style="width:100%; height: 100vh"></div>
  <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

  <script>
    var map;
    var userCoords = [0, 0]; // Placeholder for user coordinates

    // Function to initialize the map
    function initMap(startCoords) {
      map = L.map('map').setView(startCoords, 11);
      L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: 'Leaflet &copy; OpenStreetMap',
        maxZoom: 18
      }).addTo(map);
    }

    // Custom icon for user location (red color)
    var userIcon = L.icon({
      iconUrl: 'https://via.placeholder.com/30/FF0000/FFFFFF?text=U', // Red icon with text "U"
      iconSize: [30, 30],
      iconAnchor: [15, 30],
      popupAnchor: [0, -30]
    });

    // Get the user's current location
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {
        userCoords = [position.coords.latitude, position.coords.longitude];
        console.log("User Coordinates:", userCoords);

        // Initialize the map with user's coordinates
        initMap(userCoords);
        
        // Add a marker for the user's location with the custom icon
        var userMarker = L.marker(userCoords, { icon: userIcon }).addTo(map).bindPopup("You are here").openPopup();

        // Add store marker
        var storeMarker = L.marker([<?php echo implode(',', $store_coords); ?>]).addTo(map).bindPopup("Store Location").openPopup();

        var transactions = <?php echo json_encode($transactions); ?>;
        console.log("Transactions:", transactions); // Debug transaction coordinates

        // Add markers for each transaction
        transactions.forEach(function(transaction) {
            var customerMarker = L.marker(transaction.coords)
                .addTo(map)
                .bindPopup("Customer: " + transaction.name);
        });

        // Start routing from the user's location
        routeToCustomers(userCoords, transactions);

      }, function() {
        alert("Unable to retrieve your location.");
        initMap([<?php echo implode(',', $store_coords); ?>]); // Initialize map with store coordinates if geolocation fails
      });
    } else {
      alert("Geolocation is not supported by this browser.");
      initMap([<?php echo implode(',', $store_coords); ?>]); // Initialize map with store coordinates if geolocation is not supported
    }

    // Function to route to customers one by one
    function routeToCustomers(startCoords, remainingTransactions) {
      if (remainingTransactions.length === 0) return; // Stop if no more transactions

      // Create waypoints for all transaction coordinates
      var waypoints = remainingTransactions.map(function(transaction) {
          return L.latLng(transaction.coords[0], transaction.coords[1]);
      });

      // Use the user's location as the initial waypoint
      var firstWaypoint = L.latLng(startCoords[0], startCoords[1]);

      // Initialize routing control
      var control = L.Routing.control({
          waypoints: [firstWaypoint].concat(waypoints),
          routeWhileDragging: true,
          createMarker: function() { return null; } // Disable markers for waypoints
      }).addTo(map);

      // Create a route for each transaction
      control.on('routesfound', function(e) {
          var routes = e.routes;
          for (var i = 0; i < routes.length; i++) {
              console.log('Route ' + (i + 1) + ':', routes[i]);
          }
      });
    }

  </script>
</body>
</html>
