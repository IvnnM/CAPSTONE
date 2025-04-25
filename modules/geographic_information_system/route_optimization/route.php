<?php
session_start();
include("../../../includes/cdn.html");
include("../../../config/database.php");

// Fetch store coordinates from the database
$query = "SELECT StoreExactCoordinates FROM StoreInfoTb LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$store_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Use default coordinates if store coordinates are not available
if ($store_data && !empty($store_data['StoreExactCoordinates'])) {
    $store_coords = explode(',', $store_data['StoreExactCoordinates']);
} else {
    $store_coords = [13.9653472, 121.0304496]; // Default coordinates
}

// Initialize the transactions array
$transactions = [];

// Fetch all approved transactions
$sql = "SELECT t.TransacID, t.CustName, t.CustNum, t.CustEmail, t.DeliveryFee, t.TotalPrice, t.TransactionDate, 
               l.Province, l.City
        FROM TransacTb t
        JOIN LocationTb l ON t.LocationID = l.LocationID
        WHERE t.Status = 'ToShip'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Optimization</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
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
    <?php include("../../../includes/personnel/header.php"); ?>
    <?php include("../../../includes/personnel/navbar.php"); ?>
    <div class="page-container">
        <!--<div class="header-section">-->
        <!--    <nav aria-label="breadcrumb">-->
        <!--        <ol class="breadcrumb">-->
        <!--            <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Overview">Home</a></li>-->
        <!--            <li class="breadcrumb-item active" aria-current="page">Route Map</li>-->
        <!--        </ol>-->
        <!--    </nav>-->
        <!--</div>-->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Route Optimization</h5>
          </div>
          <div class="card-body">
            <p class="card-text">Optimal Route Time: <span id="optimal-time"></span> minutes</p>
            <p class="card-text">Random Route Time: <span id="random-time"></span> minutes</p>
            <p class="card-text" id="time-savings-text" style="color: green;"></p>
          </div>
        </div>
        <div class="map-container">
            <div id="map"></div>
        </div>
        <br><br><br><br>
        <div class="controls-panel">
            <div class="controls-container">
                <button class="btn btn-success" id="toggleButton" style="width: 200px;">
                    Start
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cartRecordsContainer" class="table-responsive">
                        <table id="cartTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Date Added</th>
                                </tr>
                            </thead>
                            <tbody id="cartRecordsBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script>
        const storeCoords = <?php echo json_encode($store_coords); ?>;
    </script>
    <script src="route.js"></script>
</body>
</html>