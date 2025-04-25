
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Dropdowns</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Turf.js/6.5.0/turf.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        #map {
            height: 600px; /* Set the height of the map */
            width: 100%; /* Full width */
        }
        .map-label {
            background: transparent;
            border: none;
            text-align: center;
            pointer-events: none;
        }

        .municipality-label {
            color: black;
            font-weight: bold;
            font-size: 12px;
            text-shadow: 
                -1px -1px 0 white,
                1px -1px 0 white,
                -1px 1px 0 white,
                1px 1px 0 white;
        }

        .province-label {
            color: #2c3e50; /* Darker color for province names */
            font-weight: bold;
            font-size: 14px; /* Larger font for province names */
            text-transform: uppercase; /* Make province names uppercase */
            text-shadow: 
                -1.5px -1.5px 0 white,
                1.5px -1.5px 0 white,
                -1.5px 1.5px 0 white,
                1.5px 1.5px 0 white;
        }

        /* Optional: media query for mobile devices */
        @media screen and (max-width: 768px) {
            .municipality-label {
                font-size: 10px;
            }
            .province-label {
                font-size: 12px;
            }
        }
        /* Legend Styles */
    .legend {
        background: white;
        padding: 8px;
        border-radius: 4px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
    .legend i {
        width: 18px;
        height: 18px;
        float: left;
        margin-right: 8px;
        opacity: 0.7;
    }
    .legend h4 {
        margin: 0 0 5px;
        font-weight: bold;
    }

    /* Label Styles */
    .map-label div {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 3px 8px;
        border-radius: 4px;
        border: 1px solid rgba(0,0,0,0.2);
        font-weight: bold;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        white-space: nowrap;
    }
    .province-label div {
        font-size: 14px;
        color: #333;
    }
    .municipality-label div {  
        font-size: 10px;
        color: #444;
    }

    /* Popup Styles */
    .leaflet-popup-content {
        margin: 13px;
        font-family: Arial, sans-serif;
    }
    .leaflet-popup-content strong {
        color: #333;
        display: inline-block;
        width: 140px;
    }
    .leaflet-popup-content br {
        margin-bottom: 5px;
    }

    /* Reset View Button */
    .reset-view-btn {
        padding: 5px 10px;
        background: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        cursor: pointer;
    }
    .reset-view-btn:hover {
        background: #f4f4f4;
    }
    
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 col-9">
        <div id="map" class="border bg-white p-4 rounded-lg shadow"></div>
    </div>
    <div class="container mx-auto px-4 col-3">
        <h4>Filter</h4>
        <label>
            <input type="checkbox" id="filterTransactionsCheckbox"> Mark cities with transactions
        </label>
        <div class="location-info bg-white p-4 rounded-lg shadow">
            <h4>Information</h4>
            <div id="location-details">
                <p class="select-prompt">Click on a location to view details</p>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="../modules/geographic_information_system/geojsonData/BATANGAS.js"></script>
    <script src="../modules/geographic_information_system/geojsonData/CAVITE.js"></script>
    <script src="../modules/geographic_information_system/geojsonData/LAGUNA.js"></script>
    <script src="../modules/geographic_information_system/geojsonData/QUEZON.js"></script>
    <script src="../modules/geographic_information_system/geojsonData/RIZAL.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="../modules/geographic_information_system/map/map.js"></script>
    
</body>
</html>
