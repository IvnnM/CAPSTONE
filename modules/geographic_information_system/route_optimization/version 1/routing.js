// Function to initialize the map
function initMap(userCoords, storeCoords, transactions) {
  var map = L.map('map').setView(userCoords, 11);
  L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
      attribution: 'Leaflet &copy; OpenStreetMap',
      maxZoom: 18
  }).addTo(map);

  // Custom icon for user location (red color)
  var userIcon = L.icon({
      iconUrl: 'https://via.placeholder.com/30/FF0000/FFFFFF?text=U', // Red icon with text "U"
      iconSize: [30, 30],
      iconAnchor: [15, 30],
      popupAnchor: [0, -30]
  });

  // Add a marker for the user's location with the custom icon
  var userMarker = L.marker(userCoords, { icon: userIcon }).addTo(map).bindPopup("You are here").openPopup();

  // Add store marker
  var storeMarker = L.marker(storeCoords).addTo(map).bindPopup("Store Location").openPopup();

  // Add markers for each transaction
  transactions.forEach(function(transaction) {
      var customerMarker = L.marker(transaction.coords)
          .addTo(map)
          .bindPopup("Customer: " + transaction.name);
  });

  // Start routing from the user's location
  routeToCustomers(userCoords, transactions, map);
}

// Function to route to customers one by one
function routeToCustomers(startCoords, remainingTransactions, map) {
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
