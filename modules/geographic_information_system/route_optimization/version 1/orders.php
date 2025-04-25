<?php
session_start();
include("../../../includes/cdn.html"); 
include("../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
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
    <title>Orders</title>
</head>
<body>
<?php include("../../../includes/personnel/header.php"); ?>
    <div class="container-fluid"><hr>
        <div class="sticky-top bg-light pb-2">
            <h3>Orders</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Transaction">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Orders</li>
                </ol>
            </nav>
            <hr>
        </div>
        <?php if (!empty($transactions)): ?>

        <div class="table-responsive">
            <table id="transactionsTable" class="display table table-light table-bordered table-striped table-hover fixed-table pt-2">
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
                                <form method="POST" action="../transac_update.php" class="d-flex align-items-center">
                                    <input type="hidden" name="transac_id" value="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                    <input type="hidden" name="action" value="deliver">
                                    <button type="submit" class="btn btn-success btn-sm me-2" onclick="return confirm('Are you sure you want to mark this transaction as delivered?');">
                                        <i class="bi bi-check-circle"></i> 
                                        Complete Transaction
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
            <p class="mt-4">No To Ship transactions found.</p>
        <?php endif; ?>
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
                                    <th>Product Name</th> <!-- New Column -->
                                    <th>Category</th> <!-- New Column -->
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
                "pageLength": 5, // Default number of entries per page
                "lengthMenu": [5, 10, 25, 50, 100], // Options for number of entries
            });

            // Handle the modal show event
            $('#cartModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var transacId = button.data('transac-id'); // Extract info from data-* attributes

                // Fetch cart records via AJAX
                $.ajax({
                    url: 'fetch_cart_records.php', // URL to fetch cart records
                    type: 'GET',
                    data: { transac_id: transacId },
                    success: function(data) {
                        $('#cartRecordsBody').html(data); // Populate the modal body with fetched records
                    },
                    error: function() {
                        $('#cartRecordsBody').html('<tr><td colspan="4" class="text-center">Error fetching records.</td></tr>');
                    }
                });
            });
        });
    </script>
</body>
</html>
