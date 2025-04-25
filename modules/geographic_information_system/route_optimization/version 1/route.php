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
        #map { height: 600px; }
        #offcanvas { height: 75%; }
    </style>
</head>
<body>
<?php include("../../../includes/personnel/header.php"); ?>
<div class="container-fluid"><hr>
    <div class="sticky-top bg-light pb-2">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Overview">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Route Map</li>
            </ol>
        </nav><hr class="bg-dark">
    </div>

    <div class="row justify-content-center align-items-center g-2">
      <div class="col-9">
        <!-- Map Container -->
        <div id="map"></div>
      </div>
    </div>

    <div class="row bg-body-secondary fixed-bottom justify-content-center align-items-center g-2">
      <div class="col-9 d-flex justify-content-between align-items-center p-3">

        <!-- Offcanvas Toggle Button with icon -->
        <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" aria-controls="offcanvas">
            <i class="bi bi-list fs-4"></i> <!-- Bootstrap icon for "menu" or "collapse" -->
        </button>

        <!-- Toggle button for customers -->
        <button class="btn btn-success w-50" id="toggleButton">Start</button> 

        <!-- Offcanvas Component from the Bottom -->
        <div class="offcanvas offcanvas-bottom justify-content-center align-items-center g-2 bg-light p-2" tabindex="-1" id="offcanvas" aria-labelledby="offcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasLabel">Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <?php if (!empty($transactions)): ?>
                <div class="table-responsive">
                    <table id="transactionsTable" class="display table table-light table-bordered table-striped table-hover">
                        <thead class="table-info">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Number</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Delivery Fee</th>
                                <th>Total Cost</th>
                                <th>Date Ship</th>
                                <th>Cart Records</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction['TransacID']) ?></td>
                                    <td><?= htmlspecialchars($transaction['CustName']) ?></td>
                                    <td><?= htmlspecialchars($transaction['CustNum']) ?></td>
                                    <td><?= htmlspecialchars($transaction['CustEmail']) ?></td>
                                    <td><?= htmlspecialchars($transaction['City'] . ', ' . $transaction['Province']) ?></td>
                                    <td><?= number_format(htmlspecialchars($transaction['DeliveryFee']), 2) ?></td>
                                    <td><?= number_format(htmlspecialchars($transaction['TotalPrice']), 2) ?></td>
                                    <td><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#cartModal" data-transac-id="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                            <i class="bi bi-eye"></i> View Cart Records
                                        </button>
                                    </td>
                                    <td>
                                        <form method="POST" action="orders_update.php" class="d-flex align-items-center">
                                            <input type="hidden" name="transac_id" value="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                            <input type="hidden" name="action" value="deliver">
                                            <button type="submit" class="btn btn-success btn-sm me-2" onclick="return confirm('Are you sure you want to mark this transaction as delivered?');">
                                                <i class="bi bi-check-circle"></i> Complete Transaction
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p>No To Ship transactions found.</p>
                <?php endif; ?>
            </div>
        </div>
      </div>
    </div>
</div>

<!-- Bootstrap Modal for Cart Records -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">Cart Records</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cartRecordsContainer" class="table-responsive">
                    <table id="cartTable" class="display table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Onhand ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Added Date</th>
                            </tr>
                        </thead>
                        <tbody id="cartRecordsBody">
                            <!-- Cart records will be populated here via JavaScript -->
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

<script>
// Initialize DataTables
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50, 100],
    });

    // Handle the modal show event
    $('#cartModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var transacId = button.data('transac-id');

        // Fetch cart records via AJAX
        $.ajax({
            url: 'get_cart_records.php',
            type: 'GET',
            data: { transac_id: transacId },
            success: function(data) {
                $('#cartRecordsBody').html(data);
            },
            error: function() {
                $('#cartRecordsBody').html('<tr><td colspan="6" class="text-center">Error fetching records.</td></tr>');
            }
        });
    });
});
</script>

<!-- Leaflet and Routing Machine JavaScript -->
<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
    const storeCoords = <?php echo json_encode($store_coords); ?>;
</script>
<script src="route.js"></script> <!-- Link to the external JavaScript file -->

</body>
</html>
