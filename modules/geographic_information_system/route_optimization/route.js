// Global variables
let allCustomerMarkers = [];
let activeRouteControl = null;
let map;
let currentRouteMarkers = [];
let currentRoute = []; // Store current route waypoints

// Initialize the map when the page loads
fetch('get_transactions.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Initialize the map
            map = L.map('map').setView(storeCoords, 11);

            // Set up the tile layer
            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: 'Leaflet &copy; OpenStreetMap'
            }).addTo(map);

            // Add a marker for the store location
            L.marker(storeCoords)
                .addTo(map)
                .bindPopup("<h6>Store Location</h6>")
                .openPopup();

            // Get the user's current position
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const userCoords = [position.coords.latitude, position.coords.longitude];
                    map.setView(userCoords, 11);

                    // Add a marker for the user's location with custom popup
                    L.marker(userCoords)
                        .addTo(map)
                        .bindPopup("<div class='text-center'><h6>Your Location</h6><p>Current Position</p></div>")
                        .openPopup();

                    // Display all customer markers initially
                    displayAllCustomers(data.data);

                }, () => {
                    alert("Unable to retrieve your location.");
                    map.setView(storeCoords, 11);
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    })
    .catch(error => console.error('Fetch error:', error));

// Function to format currency
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Function to format date
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Function to view order details
function viewOrderDetails(transacId) {
    fetch(`get_order_details.php?transacId=${transacId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let total = 0;
                const orderDetails = data.data.map(item => {
                    total += parseFloat(item.Subtotal);
                    return `
                        <tr>
                            <td>
                                <div><strong>${item.ProductName}</strong></div>
                                <div class="text-muted small">${item.CategoryName}</div>
                            </td>
                            <td>${item.ProductDescription || 'N/A'}</td>
                            <td class="text-center">${item.Quantity}</td>
                            <td class="text-end">${formatCurrency(item.Price)}</td>
                            <td class="text-end">${formatCurrency(item.Subtotal)}</td>
                        </tr>
                    `;
                }).join('');

                const modalContent = `
                    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Order Details - Transaction #${transacId}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="min-width: 200px;">Product</th>
                                                    <th style="min-width: 200px;">Description</th>
                                                    <th class="text-center" style="min-width: 100px;">Quantity</th>
                                                    <th class="text-end" style="min-width: 120px;">Price</th>
                                                    <th class="text-end" style="min-width: 120px;">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${orderDetails}
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                                    <td class="text-end"><strong>${formatCurrency(total)}</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Remove existing modal if any
                const existingModal = document.getElementById('orderDetailsModal');
                if (existingModal) {
                    existingModal.remove();
                }

                // Add new modal to body
                document.body.insertAdjacentHTML('beforeend', modalContent);

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                modal.show();

            } else {
                alert(data.error || 'Failed to load order details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load order details');
        });
}


// Function to complete delivery
function completeDelivery(transacId, marker) {
    if (confirm('Are you sure you want to mark this delivery as complete?')) {
        const formData = new FormData();
        formData.append('transacId', transacId);

        fetch('update_delivery_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const successAlert = `
                    <div class="alert alert-success" role="alert">
                        <strong>Success!</strong> Delivery marked as complete.
                    </div>
                `;
                marker.closePopup();
                marker.unbindPopup();
                marker.bindPopup(successAlert).openPopup();

                // Remove this marker after a delay
                setTimeout(() => {
                    map.removeLayer(marker);
                    
                    // If this was part of an active route, recalculate the route
                    if (currentRoute.length > 0) {
                        recalculateRemainingRoute();
                    }
                }, 2000);

                // Update the transactions table if it exists
                const transactionsTable = $('#transactionsTable').DataTable();
                if (transactionsTable) {
                    transactionsTable.ajax.reload();
                }
            } else {
                alert('Failed to update delivery status: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update delivery status');
        });
    }
}

// Function to recalculate route after completion
function recalculateRemainingRoute() {
    // Filter out completed deliveries from current route
    const remainingMarkers = currentRouteMarkers.filter(marker => map.hasLayer(marker));
    const remainingWaypoints = remainingMarkers.map(marker => marker.getLatLng());

    if (remainingWaypoints.length > 1) {
        // Clear existing route
        if (activeRouteControl) {
            map.removeControl(activeRouteControl);
        }

        // Create new route with remaining waypoints
        activeRouteControl = L.Routing.control({
            waypoints: remainingWaypoints,
            routeWhileDragging: false,
            createMarker: function() { return null; }
        }).addTo(map);
    }
}

// Updated function to create customer popup content
function createCustomerPopupContent(transaction) {
    return `
        <div class="customer-popup">
            <h6 class="mb-2">Customer Information</h6>
            <hr class="my-2">
            <p><strong>Transaction ID:</strong> ${transaction.TransacID}</p>
            <p><strong>Name:</strong> ${transaction.CustName}</p>
            <p><strong>Contact:</strong> ${transaction.CustNum}</p>
            <p><strong>Email:</strong> ${transaction.CustEmail}</p>
            ${transaction.CustNote ? `<p><strong>Note:</strong> ${transaction.CustNote}</p>` : ''}
            <hr class="my-2">
            <p><strong>Delivery Fee:</strong> ${formatCurrency(transaction.DeliveryFee)}</p>
            <p><strong>Total Amount:</strong> ${formatCurrency(transaction.TotalPrice)}</p>
            <p><strong>Delivery Date:</strong> ${formatDate(transaction.TransactionDate)}</p>
            <hr class="my-2">
            <div class="text-center">
                <button class="btn btn-info mb-2 w-100" 
                        onclick="viewOrderDetails('${transaction.TransacID}')">
                    <i class="bi bi-eye"></i> View Order Details
                </button>
                <button class="btn btn-success w-100 complete-delivery-btn" 
                        onclick="completeDelivery('${transaction.TransacID}', this._marker)">
                    <i class="bi bi-check-circle"></i> Complete Delivery
                </button>
            </div>
        </div>
    `;
}

// Function to display all customer markers
function displayAllCustomers(transactions) {
    allCustomerMarkers = transactions.map(transaction => {
        if (transaction.ExactCoordinates) {
            const customerCoords = transaction.ExactCoordinates.split(',').map(Number);
            const marker = L.marker(customerCoords).addTo(map);
            
            // Create popup content
            const popupContent = createCustomerPopupContent(transaction);
            marker.bindPopup(popupContent);
            
            // Store marker reference in popup button
            marker.on('popupopen', () => {
                const popup = marker.getPopup();
                const completeBtn = popup.getElement().querySelector('.complete-delivery-btn');
                if (completeBtn) {
                    completeBtn._marker = marker;
                }
            });

            return {
                marker,
                coords: customerCoords,
                name: transaction.CustName,
                transaction: transaction
            };
        }
        return null;
    }).filter(Boolean);
}

// Function to clear existing route
function clearRoute() {
    if (activeRouteControl) {
        map.removeControl(activeRouteControl);
        activeRouteControl = null;
    }
    currentRouteMarkers.forEach(marker => map.removeLayer(marker));
    currentRouteMarkers = [];
}

// Dijkstra's Algorithm Implementation
class Graph {
    constructor() {
        this.nodes = new Map();
    }

    addNode(node) {
        if (!this.nodes.has(node)) {
            this.nodes.set(node, new Map());
        }
    }

    addEdge(node1, node2, weight) {
        this.addNode(node1);
        this.addNode(node2);
        this.nodes.get(node1).set(node2, weight);
        this.nodes.get(node2).set(node1, weight); // Undirected graph
    }

    dijkstra(startNode) {
        const distances = new Map();
        const previous = new Map();
        const unvisited = new Set();

        // Initialize
        for (let node of this.nodes.keys()) {
            distances.set(node, Infinity);
            previous.set(node, null);
            unvisited.add(node);
        }
        distances.set(startNode, 0);

        while (unvisited.size > 0) {
            // Find the unvisited node with the smallest distance
            const currentNode = Array.from(unvisited).reduce((minNode, node) => 
                distances.get(node) < distances.get(minNode) ? node : minNode
            );

            // Remove current node from unvisited
            unvisited.delete(currentNode);

            // Check neighbors
            for (let [neighbor, weight] of this.nodes.get(currentNode)) {
                if (unvisited.has(neighbor)) {
                    const tentativeDistance = distances.get(currentNode) + weight;
                    
                    if (tentativeDistance < distances.get(neighbor)) {
                        distances.set(neighbor, tentativeDistance);
                        previous.set(neighbor, currentNode);
                    }
                }
            }
        }

        return { distances, previous };
    }

    getShortestPath(start, end) {
        const { distances, previous } = this.dijkstra(start);
        
        const path = [];
        let currentNode = end;
        
        while (currentNode !== null) {
            path.unshift(currentNode);
            currentNode = previous.get(currentNode);
        }
        
        return {
            path,
            distance: distances.get(end)
        };
    }
}

// Modified route optimization function using Dijkstra's Algorithm
function calculateOptimalRouteDijkstra(startCoords, customers, numStops) {
    // Create a graph representing the network of locations
    const graph = new Graph();
    
    // Add start and end coordinates as nodes
    const locations = [
        { id: 'start', coords: startCoords },
        ...customers.slice(0, numStops),
        { id: 'store', coords: storeCoords }
    ];

    // Add all nodes to the graph
    locations.forEach((loc, index) => {
        graph.addNode(`node_${index}`);
    });

    // Calculate and add edges (weights) between all nodes
    for (let i = 0; i < locations.length; i++) {
        for (let j = i + 1; j < locations.length; j++) {
            const distance = calculateDistance(
                locations[i].coords, 
                locations[j].coords
            );
            graph.addEdge(`node_${i}`, `node_${j}`, distance);
        }
    }

    // Find the optimal route
    const route = [startCoords];
    let currentNode = 'node_0'; // Start node
    const visitedNodes = new Set(['node_0']);

    // Find the next closest unvisited customer
    for (let i = 0; i < numStops; i++) {
        let shortestPath = null;
        let nextNode = null;

        // Find the shortest path to an unvisited customer
        for (let j = 1; j < locations.length - 1; j++) {
            const nodeId = `node_${j}`;
            if (!visitedNodes.has(nodeId)) {
                const pathResult = graph.getShortestPath(currentNode, nodeId);
                
                // Update shortest path if this is shorter or first found
                if (!shortestPath || pathResult.distance < shortestPath.distance) {
                    shortestPath = pathResult;
                    nextNode = nodeId;
                }
            }
        }

        // If no path found, break
        if (!nextNode) break;

        // Add the next customer's coordinates to the route
        const nextNodeIndex = parseInt(nextNode.split('_')[1]);
        route.push(locations[nextNodeIndex].coords);
        
        // Mark as visited and update current node
        visitedNodes.add(nextNode);
        currentNode = nextNode;
    }

    // Add store as final destination
    route.push(storeCoords);

    // Calculate time savings (similar to previous implementation)
    const totalDistance = route.reduce((total, coord, index) => {
        return index > 0 
            ? total + calculateDistance(route[index-1], coord)
            : total;
    }, 0);

    const randomRoute = [...customers]
        .sort(() => 0.5 - Math.random())
        .slice(0, numStops)
        .map(c => c.coords);
    randomRoute.unshift(startCoords);
    randomRoute.push(storeCoords);

    const randomTotalDistance = randomRoute.reduce((total, coord, index) => {
        return index > 0 
            ? total + calculateDistance(randomRoute[index-1], coord)
            : total;
    }, 0);

    const optimalTotalTime = estimateTravelTime(totalDistance);
    const randomTotalTime = estimateTravelTime(randomTotalDistance);
    const timeSaved = randomTotalTime - optimalTotalTime;

    displayTimeSavingsInfo(optimalTotalTime, randomTotalTime, timeSaved);

    return route;
}
// Function to find closest point from current position
function findClosestPoint(startCoords, points) {
    return points.reduce((closest, current) => {
        const distance = calculateDistance(startCoords, current.coords);
        if (distance < closest.distance) {
            return { ...current, distance };
        }
        return closest;
    }, { distance: Infinity });
}

// Function to create route markers with enhanced info
function createRouteMarkers(waypoints, transactions) {
    return waypoints.map((coords, index) => {
        let popupContent;
        
        if (index === 0) {
            popupContent = "<div class='text-center'><h6>Start</h6><p>Your Location</p></div>";
        } else if (index === waypoints.length - 1) {
            popupContent = "<div class='text-center'><h6>Final Stop</h6><p>Store Location</p></div>";
        } else {
            // Find matching transaction for this waypoint
            const matchingMarker = allCustomerMarkers.find(marker => 
                marker.coords[0] === coords[0] && marker.coords[1] === coords[1]
            );
            
            if (matchingMarker) {
                popupContent = createCustomerPopupContent(matchingMarker.transaction);
            } else {
                popupContent = `<div class='text-center'><h6>Stop ${index}</h6></div>`;
            }
        }
        
        const marker = L.marker(coords)
            .addTo(map)
            .bindPopup(popupContent);
        return marker;
    });
}

// Modified createRoute function to store current route
function createRoute(waypoints) {
    clearRoute();
    currentRoute = waypoints;
    
    // Create markers for each waypoint
    currentRouteMarkers = waypoints.map((coords, index) => {
        let popupContent;
        
        if (index === 0) {
            popupContent = "<div class='text-center'><h6>Start</h6><p>Your Location</p></div>";
        } else if (index === waypoints.length - 1) {
            popupContent = "<div class='text-center'><h6>Final Stop</h6><p>Store Location</p></div>";
        } else {
            // Find matching transaction for this waypoint
            const matchingMarker = allCustomerMarkers.find(marker => 
                marker.coords[0] === coords[0] && marker.coords[1] === coords[1]
            );
            
            if (matchingMarker) {
                popupContent = createCustomerPopupContent(matchingMarker.transaction);
            } else {
                popupContent = `<div class='text-center'><h6>Stop ${index}</h6></div>`;
            }
        }
        
        const marker = L.marker(coords).addTo(map);
        marker.bindPopup(popupContent);
        
        // Store marker reference in popup button
        marker.on('popupopen', () => {
            const popup = marker.getPopup();
            const completeBtn = popup.getElement().querySelector('.complete-delivery-btn');
            if (completeBtn) {
                completeBtn._marker = marker;
            }
        });
        
        return marker;
    });

    // Create the route
    activeRouteControl = L.Routing.control({
        waypoints: waypoints.map(coords => L.latLng(coords[0], coords[1])),
        routeWhileDragging: false,
        createMarker: function() { return null; }
    }).addTo(map);
}

// Haversine formula for calculating distance
function calculateDistance(coords1, coords2) {
    const R = 6371;
    const dLat = toRad(coords2[0] - coords1[0]);
    const dLon = toRad(coords2[1] - coords1[1]);
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(toRad(coords1[0])) * Math.cos(toRad(coords2[0])) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function toRad(degrees) {
    return degrees * (Math.PI / 180);
}

// Add a function to estimate travel time between two points
function estimateTravelTime(distance) {
    // Assume average urban driving speed of 30 km/h
    const averageSpeed = 35; // km/h
    const timeInHours = distance / averageSpeed;
    const timeInMinutes = Math.round(timeInHours * 60);
    return timeInMinutes;
}

// Modified calculateOptimalRoute function to track time savings
function calculateOptimalRoute(startCoords, customers, numStops) {
    let route = [startCoords];
    let remainingCustomers = [...customers];
    let currentPosition = startCoords;
    let totalDistance = 0;
    let randomRouteDistance = 0;

    // Calculate optimal route
    const optimalRoute = [startCoords];
    for (let i = 0; i < numStops && remainingCustomers.length > 0; i++) {
        const nextCustomer = findClosestPoint(currentPosition, remainingCustomers);
        optimalRoute.push(nextCustomer.coords);
        
        // Calculate distance between current position and next customer
        const legDistance = calculateDistance(currentPosition, nextCustomer.coords);
        totalDistance += legDistance;
        
        currentPosition = nextCustomer.coords;
        remainingCustomers = remainingCustomers.filter(c => 
            c.coords[0] !== nextCustomer.coords[0] || 
            c.coords[1] !== nextCustomer.coords[1]
        );
    }
    optimalRoute.push(storeCoords);

    // Calculate a random route for comparison
    let randomRoute = [startCoords];
    remainingCustomers = [...customers];
    currentPosition = startCoords;
    const shuffledCustomers = remainingCustomers.sort(() => 0.5 - Math.random()).slice(0, numStops);
    
    shuffledCustomers.forEach(customer => {
        randomRoute.push(customer.coords);
    
        // Calculate distance for random route
        const legDistance = calculateDistance(currentPosition, customer.coords);
        randomRouteDistance += legDistance;
    
        currentPosition = customer.coords;
    });
    randomRoute.push(storeCoords);
    
    // Calculate time estimations
    const optimalTotalTime = estimateTravelTime(totalDistance);
    const randomTotalTime = estimateTravelTime(randomRouteDistance);
    const timeSaved = randomTotalTime - optimalTotalTime;

    // Display time savings information
    displayTimeSavingsInfo(optimalTotalTime, randomTotalTime, timeSaved);

    return optimalRoute;
}

// Function to display time savings information
function displayTimeSavingsInfo(optimalTime, randomTime, timeSaved) {
  document.getElementById('optimal-time').textContent = optimalTime;
  document.getElementById('random-time').textContent = randomTime;

  const timeSavingsText = timeSaved > 0
    ? `Saves ${Math.abs(timeSaved.toFixed(0))} minutes compared to random route`
    : `Random route takes ${Math.abs(timeSaved.toFixed(0))} minutes longer`;

  const timeSavingsElement = document.getElementById('time-savings-text');
  timeSavingsElement.textContent = timeSavingsText;
  timeSavingsElement.style.color = timeSaved > 0 ? 'green' : 'red';
}

// Function to reset time savings display
function resetTimeSavingsDisplay() {
  document.getElementById('optimal-time').textContent = '0';
  document.getElementById('random-time').textContent = '0';
  
  const timeSavingsElement = document.getElementById('time-savings-text');
  timeSavingsElement.textContent = '';
  timeSavingsElement.style.color = '';
}

// Toggle button handler
document.getElementById('toggleButton').onclick = function() {
    const buttonText = this.innerText;
    const timeSavingsElement = document.getElementById('time-savings-info');
    
    if (buttonText === "Start") {
        // Use SweetAlert for input
        Swal.fire({
            title: 'Delivery Count',
            input: 'number',
            inputLabel: 'Enter the number of customers to deliver items to',
            inputPlaceholder: 'Number of stops',
            showCancelButton: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to enter a number!';
                }
                if (value < 1) {
                    return 'Number of stops must be at least 1!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const numStops = parseInt(result.value);
                
                // Hide all customer markers
                allCustomerMarkers.forEach(({ marker }) => map.removeLayer(marker));
                
                // Get current position and calculate route
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        const userCoords = [position.coords.latitude, position.coords.longitude];
                        const optimalRoute = calculateOptimalRoute(userCoords, allCustomerMarkers, numStops);
                        createRoute(optimalRoute);
                    });
                }
                
                this.innerText = "Stop";
                this.classList.remove("btn-success");
                this.classList.add("btn-danger");
                // Remove any previous time savings info if it exists
                if (timeSavingsElement) {
                    timeSavingsElement.remove();
                }
            }
        });
    } else {
        // Show all customer markers
        allCustomerMarkers.forEach(({ marker }) => marker.addTo(map));
        
        // Clear the optimized route
        clearRoute();
        // Reset time savings display
        resetTimeSavingsDisplay();
        // Create route from user location to store
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                const userCoords = [position.coords.latitude, position.coords.longitude];
                createRoute([userCoords, storeCoords]);
            });
        }
        
        this.innerText = "Start";
        this.classList.remove("btn-danger");
        this.classList.add("btn-success");
        // Remove time savings info
        if (timeSavingsElement) {
            timeSavingsElement.remove();
        }
    }
};



