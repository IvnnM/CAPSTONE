// Initialize map centered on Calabarzon region with default OSM layer
const map = L.map('map').setView([13.7500, 121.0370], 8);
const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Additional map layers
const baseLayers = {
    "OpenStreetMap": osmLayer,
    "Google Streets": L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; <a href="https://www.google.com/intl/en_us/help/terms_maps.html">Google</a>'
    }),
    "Satellite": L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    })
};
L.control.layers(baseLayers).addTo(map);

// UI Elements
const provinceCheckboxes = document.getElementById('provinceCheckboxes');
const fetchTransactionsButton = document.getElementById('fetchTransactions');

// Store the routing control instance
let routingControl = null;

// Utility Functions
const clearMapLayers = () => {
    map.eachLayer(layer => {
        if (layer instanceof L.Marker || layer instanceof L.Polyline || layer instanceof L.Circle) {
            map.removeLayer(layer);
        }
    });
};

const calculateDistance = ([lat1, lng1], [lat2, lng2]) => {
    const R = 6371e3; // Earth's radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
};

// Fetch and Display Provinces
const loadProvinces = async () => {
    try {
        const res = await fetch('get_provinces.php');
        const data = await res.json();
        data.forEach(location => {
            provinceCheckboxes.innerHTML += `
                <div>
                    <input type="checkbox" class="form-check-input" id="${location.Province}" value="${location.Province}">
                    <label for="${location.Province}">${location.Province}</label>
                </div>`;
        });
    } catch (error) {
        console.error('Error fetching provinces:', error);
    }
};

// Fetch Cities and Display Markers
const fetchTransactionCities = async (selectedProvinces) => {
    clearMapLayers();

    // Remove existing routing control if it exists
    if (routingControl) {
        map.removeControl(routingControl);
        routingControl = null;
    }

    try {
        const res = await fetch(`get_cities_with_transactions.php?province=${encodeURIComponent(selectedProvinces.join(','))}`);
        const data = await res.json();

        if (!data.cities || !data.cities.length) {
            console.warn('No cities found for selected provinces.');
            return;
        }

        let totalRevenue = 0;
        let totalTransactions = 0;
        const transactionCoords = data.cities.map(city => {
            const [lat, lng] = city.LatLng.split(',').map(parseFloat);
            if (isNaN(lat) || isNaN(lng)) {
                console.error(`Invalid coordinates for ${city.City}`);
                return null; // Skip invalid coordinates
            }

            totalRevenue += parseFloat(city.TotalRevenue) || 0;
            totalTransactions += parseInt(city.TransactionCount) || 0;

            // Extract current year data
            const currentYear = new Date().getFullYear();
            const currentYearRevenue = city.HistoricalRevenue[currentYear] || 0; // Current year revenue
            const currentYearTransactions = city.HistoricalTransactions[currentYear] || 0; // Current year transactions

            // Extract forecasted data
            const forecastedRevenue = city.ForecastedRevenue ? city.ForecastedRevenue[currentYear + 1] || 0 : 0; // Next year revenue
            const forecastedTransactions = city.ForecastedTransactions ? city.ForecastedTransactions[currentYear + 1] || 0 : 0; // Next year transactions

            // Prepare forecasted and historical data for URL
            const forecastedRevenueData = city.ForecastedRevenue || {};
            const forecastedTransactionsData = city.ForecastedTransactions || {};
            const historicalRevenueData = city.HistoricalRevenue || {};
            const historicalTransactionsData = city.HistoricalTransactions || {};

            // Prepare data for URL encoding
            const reportUrl = `site_selection_report.php?province=${selectedProvinces.join(',')}&city=${encodeURIComponent(city.City)}&forecastedRevenue=${encodeURIComponent(JSON.stringify(forecastedRevenueData))}&forecastedTransactions=${encodeURIComponent(JSON.stringify(forecastedTransactionsData))}&historicalRevenue=${encodeURIComponent(JSON.stringify(historicalRevenueData))}&historicalTransactions=${encodeURIComponent(JSON.stringify(historicalTransactionsData))}`;

            // Display simplified information in the popup
            L.marker([lat, lng]).addTo(map).bindPopup(`
                <strong>${city.City}</strong><br>
                Total Revenue: ${city.TotalRevenue || 0}<br>
                Total Transactions: ${city.TransactionCount || 0}<br>
                <strong>Current Year Revenue:</strong> ${currentYearRevenue}<br>
                <strong>Current Year Transactions:</strong> ${currentYearTransactions}<br>
                <strong>Next Year Forecasted Revenue:</strong> ${forecastedRevenue}<br>
                <strong>Next Year Forecasted Transactions:</strong> ${forecastedTransactions}<br>
                <a href="${reportUrl}" target="_blank">View Report</a>
            `);

            return [lat, lng];
        }).filter(coords => coords); // Filter out any null values

        // Pass totalRevenue and totalTransactions to routeToCustomers
        routeToCustomers(transactionCoords, data, totalRevenue, totalTransactions);
    } catch (error) {
        console.error('Error fetching cities:', error);
    }
};

// Determine Best Site and Route
const routeToCustomers = async (transactionCoords, data, totalRevenue, totalTransactions) => {
    let bestCity = null;
    let bestScore = -Infinity;
    let averageDistance = 0; // Define averageDistance outside the loop

    // Get the current year
    const currentYear = new Date().getFullYear();

    // Loop through each city to find the best site
    data.cities.forEach((city) => {
        // Extract current year data
        const currentYearRevenue = city.HistoricalRevenue[currentYear] || 0; // Current year revenue
        const currentYearTransactions = city.HistoricalTransactions[currentYear] || 0; // Current year transactions

        // Extract forecasted data
        const forecastedRevenue = city.ForecastedRevenue || {};
        const forecastYearRevenue = forecastedRevenue[currentYear + 1] || 0; // Next year revenue
        const forecastYearTransactions = city.ForecastedTransactions[currentYear + 1] || 0; // Next year transactions

        // Average distance to customer locations
        const cityCoords = [city.LatLng.split(',')[0], city.LatLng.split(',')[1]];
        const totalDistance = transactionCoords.reduce((totalDistance, coords) => {
            return totalDistance + calculateDistance(cityCoords, coords);
        }, 0);
        
        averageDistance = totalDistance / transactionCoords.length; // Update averageDistance

        // Adjust the score calculation to prioritize revenue while also considering distance
        const revenueWeight = 1.0;   
        const transactionWeight = 1.0;        
        const forecastWeight = 1.5;        
        const distancePenalty = 0.5;      


        // Calculate score with more balanced weight towards distance
        const score = (currentYearRevenue * revenueWeight) 
                    + (currentYearTransactions * transactionWeight) 
                    + (forecastYearRevenue * forecastWeight) 
                    + (forecastYearTransactions * transactionWeight) 
                    - (averageDistance * distancePenalty);

        console.log(`City: ${city.City}, Current Year Revenue: ${currentYearRevenue}, Forecasted Revenue: ${forecastYearRevenue}, Current Transactions: ${currentYearTransactions}, Forecasted Transactions: ${forecastYearTransactions}, Average Distance: ${averageDistance.toFixed(2)}m, Score: ${score}`);

        // Update the best city if the score is higher
        if (score > bestScore) {
            bestScore = score;
            bestCity = city; // Set the best city
        }
    });

    if (!bestCity || !bestCity.LatLng) {
        console.error('No valid best site found or missing LatLng for the best city');
        return null;
    }

    const bestSiteCoords = bestCity.LatLng.split(',').map(coord => parseFloat(coord.trim()));
    drawBestSiteCircle(bestSiteCoords);
    createRoutingControl(transactionCoords, bestSiteCoords);

    // Update the HTML element with best site information
    const bestSiteInfoDiv = document.getElementById('best-site-info');
    const bestSiteDetails = document.getElementById('best-site-details');

    bestSiteDetails.innerHTML = `
        <h3>${bestCity.City}</h3>
        <strong>Current Year Revenue:</strong> ${bestCity.HistoricalRevenue[currentYear] || 0}<br>
        <strong>Current Year Transactions:</strong> ${bestCity.HistoricalTransactions[currentYear] || 0}<br>
        <strong>Forecasted Revenue:</strong> ${bestCity.ForecastedRevenue ? bestCity.ForecastedRevenue[currentYear + 1] || 0 : 0}<br>

        <strong>Forecasted Transactions:</strong> ${bestCity.ForecastedTransactions ? bestCity.ForecastedTransactions[currentYear + 1] || 0 : 0}<br>
        <strong>Average Distance to Customers:</strong> ${averageDistance.toFixed(2)} meters
    `;

    // Show the best site info div
    bestSiteInfoDiv.style.display = 'block';
};


// Draw a circle around the best site
const drawBestSiteCircle = (coords) => {
    const circleRadius = 5000; // Radius in meters
    const circleColor = '#FF0000';
    const circleOpacity = 0.5;

    L.circle(coords, {
        color: circleColor,
        fillColor: circleColor,
        fillOpacity: circleOpacity,
        radius: circleRadius
    }).addTo(map);
};

// Create routing control
const createRoutingControl = (transactionCoords, bestSiteCoords) => {
    const waypoints = transactionCoords.map(coord => L.latLng(coord[0], coord[1]));

    routingControl = L.Routing.control({
        waypoints: [L.latLng(bestSiteCoords[0], bestSiteCoords[1]), ...waypoints, L.latLng(bestSiteCoords[0], bestSiteCoords[1])],
        routeWhileDragging: false,
        show: false,
        createMarker: () => null // Disable default markers
    }).on('routesfound', function(e) {
        console.log(e.routes);
    }).addTo(map);
};

// Event Listener for Transactions Button
fetchTransactionsButton.addEventListener('click', () => {
    const selectedProvinces = Array.from(provinceCheckboxes.querySelectorAll('input:checked')).map(cb => cb.value);
    if (selectedProvinces.length) {
        fetchTransactionCities(selectedProvinces);
    } else {
        console.warn('No provinces selected.');
    }
});

// Load provinces on page load
loadProvinces();
