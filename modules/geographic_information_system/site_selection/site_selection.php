<?php

include_once('../../../config/database.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Selection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Leaflet Routing Machine CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    
    <style>
        #map { height: 600px; } /* Adjust the height as needed */
    </style>
</head>
<body>
    
<div class="container-fluid"><hr class="bg-dark">
        <div class="sticky-top bg-light pb-2">
            <h3>Site Selection Map</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Overview">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Site Selection Map</li>
                </ol>
            </nav><hr class="bg-dark">
        </div>

        <div class="row">
            <div class="col-10">
                <div id="map"></div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <label for="provinceCheckboxes">Select Provinces:</label>
                    <div id="provinceCheckboxes" class="form-check">
                        <!-- Checkboxes will be populated dynamically -->
                    </div>
                

                <button id="fetchTransactions" class="btn btn-primary">Find Site</button>
                </div>
                <hr class="bg-dark">
            


                <div id="best-site-info" style="display: none;">
                    <!-- <h3>Best Site Information</h3> -->
                    <p id="best-site-details"></p>
                </div>
            </div>
        </div>
</div>




    <div class="container mt-4">

    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <script src="site_selection.js"></script> <!-- Include your JavaScript file -->
</body>
</html>
