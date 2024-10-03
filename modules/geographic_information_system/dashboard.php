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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">

    <script src="gis_map.js"></script>
    <script src="map_functions.js"></script>
    <script src="chart_functions.js"></script>

</head>
<body>
    <h1>Geographic Information System Dashboard</h1>
    
    <div>
        <label for="province">Select Province:</label>
        <select id="province">
            <option value="">--Select Province--</option>
        </select>

        <label for="city">Select City:</label>
        <select id="city">
            <option value="">--Select City--</option>
        </select>
    </div>
<center>
    <div id="map" style="width: 800px; height: 600px;" ></div>

    <canvas id="allTransactionsChart" width="400px" height="200px"></canvas>
    <canvas id="transactionsChart" width="400px" height="200px"></canvas>

</center>


    
</body>
</html>
