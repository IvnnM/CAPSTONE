var map;
var userCoords = [0, 0]; // Placeholder for user coordinates

function initMap(startCoords) {
    map = L.map('map').setView(startCoords, 11);
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: 'Leaflet &copy; OpenStreetMap',
        maxZoom: 18
    }).addTo(map);
}

var userIcon = L.icon({
    iconUrl: 'https://via.placeholder.com/30/FF0000/FFFFFF?text=U',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
});

function addMarkers(transactions) {
    transactions.forEach(function(transaction) {
        var customerMarker = L.marker(transaction.coords)
            .addTo(map)
            .bindPopup("Customer: " + transaction.name);
    });
}

function routeToCustomers(startCoords, remainingTransactions) {
    if (remainingTransactions.length === 0) return;

    var waypoints = remainingTransactions.map(function(transaction) {
        return L.latLng(transaction.coords[0], transaction.coords[1]);
    });

    var firstWaypoint = L.latLng(startCoords[0], startCoords[1]);

    var control = L.Routing.control({
        waypoints: [firstWaypoint].concat(waypoints),
        routeWhileDragging: true,
        createMarker: function() { return null; }
    }).addTo(map);

    control.on('routesfound', function(e) {
        var routes = e.routes;
        for (var i = 0; i < routes.length; i++) {
            console.log('Route ' + (i + 1) + ':', routes[i]);
        }
    });
}
