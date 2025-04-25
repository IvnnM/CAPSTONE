//////////////////////////////////////////
//               MAP                    //
//////////////////////////////////////////
// Initialize the Leaflet map centered on the Calabarzon region with a zoom level of 8
const map = L.map('map', {
    preferCanvas: true
}).setView([14.0600, 121.4900], 8);

//////////////////////////////////////////
//               LAYERS                 //
//////////////////////////////////////////

// OpenStreetMap tile layer
const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Google Maps tile layer
const googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
    maxZoom: 20,
    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
    attribution: '&copy; <a href="https://www.google.com/intl/en_us/help/terms_maps.html">Google</a>'
});

// Satellite layer
const googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
    maxZoom: 20,
    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
});

// Add default layer to the map
googleSat.addTo(map);

//////////////////////////////////////////
//               GEOJSON                //
//////////////////////////////////////////
// Load each GeoJSON layer into a layer group
const regionLayers = L.layerGroup();

// Function to add each region GeoJSON to the layer group
function addGeoJsonLayer(geojsonData) {
    L.geoJSON(geojsonData).addTo(regionLayers);
}

// Check if each GeoJSON data exists and add it to the map
try {
    if (typeof BATANGASgeojson !== 'undefined') {
        addGeoJsonLayer(BATANGASgeojson);
    }
    if (typeof CAVITEgeojson !== 'undefined') {
        addGeoJsonLayer(CAVITEgeojson);
    }
    if (typeof LAGUNAgeojson !== 'undefined') {
        addGeoJsonLayer(LAGUNAgeojson);
    }
    if (typeof QUEZONgeojson !== 'undefined') {
        addGeoJsonLayer(QUEZONgeojson);
    }
    if (typeof RIZALgeojson !== 'undefined') {
        addGeoJsonLayer(RIZALgeojson);
    }
} catch (error) {
    console.error("Error loading GeoJSON layers:", error);
}

// Add the group of layers to the map
regionLayers.addTo(map);
addLegend();
//////////////////////////////////////////
//               CONTROL                //
//////////////////////////////////////////
const filterTransactionsCheckbox = document.getElementById('filterTransactionsCheckbox');

// Add Layer Control
const baseLayers = {
    "OpenStreetMap": osm,
    "Google Streets": googleStreets,
    "Satellite": googleSat
};
L.control.layers(baseLayers).addTo(map);

// Add scale control
L.control.scale({
    imperial: false,
    position: 'bottomleft'
}).addTo(map);

// Add reset view control
L.Control.ResetView = L.Control.extend({
    onAdd: function(map) {
        const button = L.DomUtil.create('button', 'reset-view-btn');
        button.innerHTML = 'Reset View';
        L.DomEvent.on(button, 'click', function() {
            clearMarkers();
            map.setView([14.0600, 121.4900], 8);
            // Reset location info when view is reset
            document.getElementById('location-details').innerHTML = 
                '<p class="select-prompt">Click on a location to view details</p>';
        });
        return button;
    }
});
new L.Control.ResetView({ position: 'topleft' }).addTo(map);

//////////////////////////////////////////
//               SEARCH                 //
//////////////////////////////////////////
// Helper function to check if a point is within any polygon in regionLayers
function isWithinRegions(latlng) {
    let matchingRegion = null;
    
    regionLayers.eachLayer(layer => {
        if (layer instanceof L.GeoJSON) {
            layer.eachLayer(geoLayer => {
                if (geoLayer.getBounds().contains(latlng)) {
                    // Store the matching region's properties
                    matchingRegion = geoLayer.feature.properties;
                }
            });
        }
    });
    
    return matchingRegion;
}

// Create custom geocoder control
const geocoder = L.Control.geocoder({
    defaultMarkGeocode: false,
    placeholder: 'Search address or place...',
    errorMessage: 'Nothing found.',
    suggestMinLength: 3,
    suggestTimeout: 250,
    queryMinLength: 1,
    geocoder: L.Control.Geocoder.nominatim({
        geocodingQueryParams: {
            viewbox: '120.5,13.5,122.5,14.5', // Adjust these coordinates to match your region
            bounded: 1,
            countrycodes: 'ph' // Restrict to Philippines
        }
    })
}).addTo(map);

// Handle search result selection
geocoder.on('markgeocode', function(e) {
    const latlng = e.geocode.center; // Get the latlng of the selected geocode

    // Check if the latlng point is within any of the polygons in regionLayers
    const matchingRegion = isWithinRegions(latlng);
    
    if (matchingRegion) {
        // Remove any existing marker if necessary
        clearMarkers(); // Implement this function to clear markers from the map

        // Add a marker at the location
        L.marker(latlng).addTo(map)
            .bindPopup(`<strong>${e.geocode.name}</strong><br>Region: ${matchingRegion.REGION}<br>Province: ${matchingRegion.PROVINCE}`)
            .openPopup(); // Open the popup

        // Zoom to the location with animation
        map.flyTo(latlng, 14, {
            duration: 1.5,
            easeLinearity: 0.25
        });
    } else {
        alert('Location is outside the specified region.');
    }
});

//////////////////////////////////////////
//               FUNCTIONS              //
//////////////////////////////////////////
// Function to get color based on density or any relevant property
function getColor(density) {
    return density > 200000 ? '#800026' :  // Highest density
           density > 100000 ? '#BD0026' :
           density > 50000  ? '#E31A1C' :
           density > 20000  ? '#FC4E2A' :
           density > 10000  ? '#FD8D3C' :
           density > 5000   ? '#FEB24C' :
           density > 1000   ? '#FED976' :
                              '#FFEDA0'; // Lowest density
}

// Function to style each feature
function style(feature) {
    const density = feature.properties.TOTPOP2010 / (feature.properties.LAND_AREA2 / 10000);
    return {
        fillColor: getColor(density),
        weight: 2,
        opacity: 1,
        color: 'white',
        dashArray: '3',
        fillOpacity: 0.7
    };
}

function addGeoJsonLayer(geojsonData) {
    // Create separate layer groups for municipality and province labels
    const municipalityLabelGroup = L.layerGroup().addTo(map);
    const provinceLabelGroup = L.layerGroup().addTo(map);
    
    // Create Maps to store unique municipalities and provinces
    const municipalityLabels = new Map();
    const provinceLabels = new Map();

    // Add hover effects
    function highlightFeature(e) {
        const layer = e.target;
        layer.setStyle({
            weight: 3,
            color: 'cyan',
            dashArray: '',
            fillOpacity: 0.8
        });
        layer.bringToFront();
    }

    function resetHighlight(e, geojsonLayer) {
        geojsonLayer.resetStyle(e.target);
    }

    function zoomToFeature(e) {
        map.fitBounds(e.target.getBounds());
    }

    // First pass: collect all features for each municipality and province
    geojsonData.features.forEach(feature => {
        const municipalityName = feature.properties.MUNICIPALI;
        const provinceName = feature.properties.PROVINCE;
        
        // Collect municipalities
        if (!municipalityLabels.has(municipalityName)) {
            municipalityLabels.set(municipalityName, {
                features: [],
                properties: feature.properties
            });
        }
        municipalityLabels.get(municipalityName).features.push(feature);

        // Collect provinces
        if (!provinceLabels.has(provinceName)) {
            provinceLabels.set(provinceName, {
                features: [],
                properties: feature.properties
            });
        }
        provinceLabels.get(provinceName).features.push(feature);
    });
    
    function updateLocationInfo(feature) {
        const density = feature.properties.TOTPOP2010 / (feature.properties.LAND_AREA2 / 10000);
        const detailsHtml = `
            <div class="info-row">
                <span class="label">Municipality/City:</span>
                <span>${feature.properties.MUNICIPALI}</span>
            </div>
            <div class="info-row">
                <span class="label">Province:</span>
                <span>${feature.properties.PROVINCE}</span>
            </div>
            <div class="info-row">
                <span class="label">Region:</span>
                <span>${feature.properties.REGION}</span>
            </div>
            <div class="info-row">
                <span class="label">ZIP Code:</span>
                <span>${feature.properties.ZIPCODE}</span>
            </div>
            <div class="info-row">
                <span class="label">Barangay:</span>
                <span>${feature.properties.BRGY}</span>
            </div>
            <div class="info-row">
                <span class="label">District:</span>
                <span>${feature.properties.DISTRICT}</span>
            </div>
            <div class="info-row">
                <span class="label">City Class:</span>
                <span>${feature.properties.CITY_CLASS}</span>
            </div>
            <div class="info-row">
                <span class="label">Income Class:</span>
                <span>${feature.properties.INCOME_CLA}</span>
            </div>
            <div class="info-row">
                <span class="label">Land Area:</span>
                <span>${feature.properties.LAND_AREA2} sq. m</span>
            </div>
            <div class="info-row">
                <span class="label">Population (2010):</span>
                <span>${feature.properties.TOTPOP2010}</span>
            </div>
            <div class="info-row">
                <span class="label">Population Density:</span>
                <span>${parseFloat(density).toFixed(2)}</span>
            </div>
            <div class="info-row">
            <a href="../modules/geographic_information_system/view_data.php?province=${encodeURIComponent(feature.properties.PROVINCE)}&city=${encodeURIComponent(feature.properties.MUNICIPALI)}">View Sales Data</a>
        </div>
        `;
        document.getElementById('location-details').innerHTML = detailsHtml;
    }

    // Add the GeoJSON layer
    const geojsonLayer = L.geoJSON(geojsonData, {
        style: style, // Apply custom style if needed
        onEachFeature: function (feature, layer) {
            // Set up events for each feature
            layer.on({
                mouseover: highlightFeature, // Define function to highlight on hover
                mouseout: (e) => resetHighlight(e, geojsonLayer), // Define function to reset highlight on mouseout
                click: (e) => {
                    zoomToFeature(e); // Define function to zoom on click
                    updateLocationInfo(feature); // Define function to update location info
                }
            });
        }
    });

    // Add the layer to the regionLayers group
    regionLayers.addLayer(geojsonLayer);

    // Function to calculate average position of features
    function calculateAveragePosition(features) {
        let totalLat = 0;
        let totalLng = 0;
        let pointCount = 0;

        features.forEach(feature => {
            const layer = L.geoJSON(feature);
            const center = layer.getBounds().getCenter();
            totalLat += center.lat;
            totalLng += center.lng;
            pointCount++;
        });

        return {
            lat: totalLat / pointCount,
            lng: totalLng / pointCount
        };
    }

    // Create municipality labels
    municipalityLabels.forEach((data, municipalityName) => {
        const position = calculateAveragePosition(data.features);
        
        const label = L.divIcon({
            className: 'map-label municipality-label',
            html: `<div>${municipalityName}</div>`,
            iconSize: [100, 40],
            iconAnchor: [50, 20]
        });

        const labelMarker = L.marker([position.lat, position.lng], {
            icon: label,
            zIndexOffset: 1000
        });

        municipalityLabelGroup.addLayer(labelMarker);
    });

    // Create province labels
    provinceLabels.forEach((data, provinceName) => {
        const position = calculateAveragePosition(data.features);
        
        const label = L.divIcon({
            className: 'map-label province-label',
            html: `<div>${provinceName}</div>`,
            iconSize: [120, 50], // Slightly larger for province names
            iconAnchor: [60, 25]
        });

        const labelMarker = L.marker([position.lat, position.lng], {
            icon: label,
            zIndexOffset: 900 // Slightly lower than municipality labels
        });

        provinceLabelGroup.addLayer(labelMarker);
    });

    // Function to update labels visibility based on zoom level
    function updateLabels() {
        const zoomLevel = map.getZoom();
        
        if (zoomLevel >= 10) {
            // Show municipality labels, hide province labels
            municipalityLabelGroup.addTo(map);
            provinceLabelGroup.remove();
        } else if (zoomLevel >= 8) { // Adjust this threshold as needed
            // Show province labels, hide municipality labels
            municipalityLabelGroup.remove();
            provinceLabelGroup.addTo(map);
        } else {
            // Hide all labels when zoomed out too far
            municipalityLabelGroup.remove();
            provinceLabelGroup.remove();
        }
    }

    // Initial update
    updateLabels();

    // Listen for zoom events
    map.on('zoomend', updateLabels);
}

// Add legend
function addLegend() {
    const legend = L.control({position: 'bottomright'});
    
    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'info legend');
        const grades = [1000, 5000, 10000, 20000, 50000, 100000, 200000];
        
        div.innerHTML = '<h4>Population Density</h4>' +
                       '(people/hectare)<br><br>';

        for (let i = 0; i < grades.length; i++) {
            div.innerHTML +=
                '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' +
                grades[i] + (grades[i + 1] ? '&ndash;' + grades[i + 1] + '<br>' : '+');
        }
        return div;
    };
    legend.addTo(map);
}

// Function to clear all markers from the map
function clearMarkers() {
    map.eachLayer(function (layer) {
        if (layer instanceof L.Marker) {
            map.removeLayer(layer);
        }
    });
}

// Function to fetch and display cities with transactions and their total revenue
function fetchCitiesWithTransactions() {
    clearMarkers(); // Clear existing markers

    const url = '../modules/geographic_information_system/map/get_cities_with_transactions.php';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Ensure response is an array
            if (!Array.isArray(data)) {
                console.error("Expected an array but got:", data);
                return;
            }

            if (data.length === 0) {
                console.warn('No cities with transactions found.');
                return;
            }

            data.forEach(location => {
                const latLng = location.LatLng.split(',').map(coord => parseFloat(coord.trim()));
                
                // Format Total Revenue
                const revenueInfo = location.TotalRevenue != null && location.TotalRevenue !== ''
                    ? `Total Revenue: PHP ${parseFloat(location.TotalRevenue).toLocaleString()}`
                    : 'Total Revenue: PHP 0';

                
                // Create marker with city name and total revenue
                L.marker(latLng).addTo(map)
                    .bindPopup(`<strong>${location.City}</strong><br>${revenueInfo}`);
            });

            const bounds = L.latLngBounds(data.map(location => {
                const latLng = location.LatLng.split(',').map(coord => parseFloat(coord.trim()));
                return latLng;
            }));

            if (bounds.isValid()) {
                map.fitBounds(bounds);
            } else {
                console.warn('Bounds are not valid due to invalid or missing coordinates.');
            }
        })
        .catch(error => console.error('Error fetching cities with transactions:', error));
}

// Checkbox change event for filtering cities with transactions
filterTransactionsCheckbox.addEventListener('change', function() {
    if (this.checked) {
        fetchCitiesWithTransactions();
    } else {
        clearMarkers();
    }
});
