let allCustomerMarkers = [];
let closestCustomerMarker = null;
let map; // Declare map variable in a wider scope

// Initialize the map when the page loads
fetch('get_transactions.php')
    .then(response => response.json())
    .then(data => {
        console.log(data); // Check if the correct data is being fetched
        if (data.success) {
            // Initialize the map
            map = L.map('map').setView(storeCoords, 11);

            // Set up the tile layer
            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: 'Leaflet &copy; OpenStreetMap'
            }).addTo(map);

            // Add a marker for the store location
            L.marker(storeCoords).addTo(map).bindPopup("Store Location").openPopup();

            // Get the user's current position
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const userCoords = [position.coords.latitude, position.coords.longitude];
                    map.setView(userCoords, 11); // Center the map on user's location

                    // Add a marker for the user's location
                    L.marker(userCoords).addTo(map).bindPopup("You are here").openPopup();

                    // Display all customer markers
                    displayAllCustomers(data.data);

                    // Find and display the closest customer
                    const closestCustomer = findClosestCustomer(userCoords, data.data);
                    if (closestCustomer) {
                        const customerCoords = closestCustomer.ExactCoordinates.split(',').map(Number);
                        closestCustomerMarker = L.marker(customerCoords)
                            .addTo(map)
                            .bindPopup("Closest Customer: " + closestCustomer.CustName)
                            .openPopup();

                        // Call the routing function to show the route to the closest customer
                        routeToSingleCustomer(userCoords, customerCoords, map);
                    }

                }, () => {
                    alert("Unable to retrieve your location.");
                    map.setView(storeCoords, 11); // Fallback to store coordinates if geolocation fails
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        } else {
            console.error('Error fetching transactions:', data.error);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
    });

// Function to display all customer markers
function displayAllCustomers(transactions) {
    transactions.forEach(transaction => {
        if (transaction.ExactCoordinates) {
            const customerCoords = transaction.ExactCoordinates.split(',').map(Number);
            const marker = L.marker(customerCoords)
                .addTo(map)
                .bindPopup("Customer: " + transaction.CustName);
            allCustomerMarkers.push(marker); // Store marker reference for later use
        }
    });
}

// Toggle between showing all customers and the closest one
document.getElementById('toggleButton').onclick = function() {
  const buttonText = this.innerText;

  if (buttonText === "Start") {
      // Hide all customer markers
      allCustomerMarkers.forEach(marker => map.removeLayer(marker));
      
      // Show only the closest customer marker
      if (closestCustomerMarker) {
          closestCustomerMarker.addTo(map);
          this.innerText = "Stop"; // Update button text
          this.classList.remove("btn-success");
          this.classList.add("btn-danger"); // Change button to 'danger' style
      }
  } else {
      // Show all customer markers
      allCustomerMarkers.forEach(marker => marker.addTo(map));
      
      // Remove closest customer marker if it exists
      if (closestCustomerMarker) {
          map.removeLayer(closestCustomerMarker);
      }
      this.innerText = "Start"; // Reset button text
      this.classList.remove("btn-danger");
      this.classList.add("btn-success"); // Change button back to 'primary' style
  }
};


// Function to find the closest customer based on distance
function findClosestCustomer(userCoords, transactions) {
    let closestCustomer = null;
    let shortestDistance = Infinity;

    transactions.forEach(transaction => {
        if (transaction.ExactCoordinates) {
            const customerCoords = transaction.ExactCoordinates.split(',').map(Number);
            const distance = calculateDistance(userCoords, customerCoords);

            if (distance < shortestDistance) {
                shortestDistance = distance;
                closestCustomer = transaction;
            }
        }
    });

    return closestCustomer;
}

// Haversine formula to calculate the distance between two coordinates
function calculateDistance(coords1, coords2) {
    const R = 6371; // Radius of the Earth in km
    const dLat = toRad(coords2[0] - coords1[0]);
    const dLon = toRad(coords2[1] - coords1[1]);
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(toRad(coords1[0])) * Math.cos(toRad(coords2[0])) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c; // Distance in km
}

// Convert degrees to radians
function toRad(degrees) {
    return degrees * (Math.PI / 180);
}

// Function to route to a single customer
function routeToSingleCustomer(startCoords, endCoords, map) {
    const control = L.Routing.control({
        waypoints: [
            L.latLng(startCoords[0], startCoords[1]),
            L.latLng(endCoords[0], endCoords[1])
        ],
        routeWhileDragging: true,
        createMarker: function() { return null; } // Disable default markers for waypoints
    }).addTo(map);

    // Event when routes are found
    control.on('routesfound', function(e) {
        const routes = e.routes;
        for (let i = 0; i < routes.length; i++) {
            console.log('Route ' + (i + 1) + ':', routes[i]);
        }
    });
}
