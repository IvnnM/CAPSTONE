<?php
session_start();
include("../includes/cdn.html"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analysis Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest"></script>
        <!-- Add DataTables CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <!-- Add these to your HTML -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <!-- Add custom DataTables styling -->
    <link rel="stylesheet" href="predictive.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #059669;
            --border-radius: 0.5rem;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            min-height: 100vh;
        }

        .page-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: 1rem;
            gap: 1rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-section {
            background: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb {
            display: flex;
            gap: 0.5rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #64748b;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            padding-right: 0.5rem;
            color: #94a3b8;
        }

        .map-container {
            flex: 1;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        #map {
            height: calc(100vh - 250px);
            width: 100%;
            border-radius: var(--border-radius);
        }

        .controls-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .controls-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #047857;
        }

        

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-container {
                padding: 0.5rem;
            }

            #map {
                height: calc(100vh - 200px);
            }

            .controls-panel {
                padding: 0.75rem;
            }

            .btn {
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>
<body>
<?php include("../includes/personnel/header.php"); ?>
<?php include("../includes/personnel/navbar.php"); ?>
<div class="page-container">
    <div class="header-section">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Overview">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Site Selection Map</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Site Selection Map</h5>
        </div>
        <div class="card-body">
            <p class="card-text">Analyze sales data and forecast performance for optimal site selection.</p>
        </div>
    </div>

    <div class="map-container">
        <div id="map"></div>
    </div>

    <div class="charts-container bg-dark p-3" style="display: none;" id="charts">
        <div class="chart">
            <h2 class="chart-title">Sales Trend Analysis</h2>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart">
            <h2 class="chart-title">Daily Sales Pattern</h2>
            <canvas id="patternChart"></canvas>
        </div>
        <div class="row" id="metrics"></div>
    </div>

    <div class="chart-container p-3">
        <div class="legend">
            <h3 class="legend-title">Top 3 Ranked Locations</h3>
        </div>
        <canvas id="top3ForecastChart"></canvas>
    </div>

    <div id="top10Metrics"></div>
</div>

    <script type="module" src="SalesDashboard.js"></script>
    <script type="module" src="SiteSelection.js"></script>
    <script>
    window.addEventListener('load', function() {
        if (typeof tf !== 'undefined') {
            console.log('TensorFlow.js loaded successfully');
        } else {
            console.error('TensorFlow.js failed to load');
        }
    });
    </script>
</body>
</html>

