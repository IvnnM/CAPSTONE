<?php
// /modules/geographic_information_system/dashboard.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geographic Information System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../modules/geographic_information_system/js/map/gis_map.js"></script>
    <script src="../modules/geographic_information_system/js/map/map_functions.js"></script>
    <!-- <script src="../modules/geographic_information_system/chart_functions.js"></script> -->
    <script src="../modules/geographic_information_system/js/charts/total_transactions_bar_graph.js"></script>
    <script src="../modules/geographic_information_system/js/charts/total_transactions_line_graph.js"></script>


    <!-- Additional styling -->
    <style>
        body {
            color: white;
        }
        .p-3 {
            padding: 1rem;
        }
        #map {
            width: 100%;
            height: 400px; /* Adjust height as necessary */
        }
        .col-6, .col-12 {
            border: solid white;
        }
        .dropdown-container {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container text-center">
  
  <div class="row g-2">
    
    <!-- Two columns at the top -->
    <div class="col-md-6 col-12">
      <div class="p-3"></div> <!-- Left side: blank -->
    </div>
    <div class="col-md-6 col-12">
      <div class="p-3">
        <canvas id="allTransactionsChart" width="400px" height="200px"></canvas> <!-- Right side: All Transactions Chart -->
      </div>
    </div>

    <!-- Two columns in the middle -->
    <div class="col-md-6 col-12">
      <div class="p-3">
        <!-- Map container with two rows -->
        <div>
          <!-- Dropdowns for Province and City -->
          <div class="dropdown-container">
            <label for="province">Select Province:</label>
            <select id="province" class="form-control">
              <option value="">--Select Province--</option>
            </select>
          </div>
          <div class="dropdown-container">
            <label for="city">Select City:</label>
            <select id="city" class="form-control">
              <option value="">--Select City--</option>
            </select>
          </div>
        </div>
        <div id="map" style="height: 400px;"></div> <!-- Map -->
      </div>
    </div>
    <div class="col-md-6 col-12">
      <div class="p-3">
        <canvas id="transactionsChart" height="270px"></canvas> <!-- Right side: Transactions Chart -->
      </div>
    </div>

    <!-- One column at the bottom -->
    <div class="col-12">
      <div class="p-3">
          
      </div> 
    </div>

  </div>
</div>

</body>
</html>
