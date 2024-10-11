<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Initialize the transactions array
$transactions = [];

// Fetch all pending transactions (only TransacID and relevant info)
$sql = "SELECT t.TransacID, t.CustName, t.CustNum, t.CustEmail, t.DeliveryFee, t.TotalPrice, t.TransactionDate, 
               l.Province, l.City
        FROM TransacTb t
        JOIN LocationTb l ON t.LocationID = l.LocationID
        WHERE t.Status = 'Pending'";


$stmt = $conn->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status change to approved
if (isset($_POST['approve_transaction'])) {
    $transac_id = $_POST['transac_id'];

    // Update the status to 'ToShip'
    $update_sql = "UPDATE TransacTb SET Status = 'ToShip', TransactionDate = NOW() WHERE TransacID = :transac_id"; // Update date to now
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
    if (!$update_stmt->execute()) {
        echo "<script>alert('Could not approve transaction.');</script>";
        exit;
    }

    echo "<script>alert('Transaction marked as To Ship successfully!');</script>";
    echo "<script>window.history.back();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Transactions</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <style>
    .table td {
        vertical-align: middle;
    }
    </style>
</head>
<body>
    <div class="container">
        <h3>Pending Transactions</h3>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../../views/admin_view.php#Transaction">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pending Transactions</li>
                <li class="breadcrumb-item"><a href="transac_read_approved.php">To Ship Transactions</a></li>
                <li class="breadcrumb-item"><a href="transac_read_delivered.php">Delivered Transactions</a></li>
            </ol>
        </nav>
        <?php if (!empty($transactions)): ?>

            <div class="container">
            <h4 class="mt-4">Orders</h4>
            <div class="table-responsive">
                <table id="transactionsTable" class="display table table-light table-bordered table-striped table-hover fixed-table">
                    <thead class="table-info">
                        <tr>
                            <th class="col-auto">ID</th>
                            <th class="col-auto">Name</th>
                            <th class="col-auto">Number</th>
                            <th class="col-auto">Email</th>
                            <th class="col-auto">Delivery Fee</th>
                            <th class="col-auto">Total Cost</th>
                            <th class="col-auto">Date</th>
                            <th class="col-auto">Location</th> <!-- New Column -->
                            <th class="col-auto">Cart Records</th>
                            <th class="col-auto">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?= htmlspecialchars($transaction['TransacID']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustName']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustNum']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustEmail']) ?></td>
                                <td><?= number_format(htmlspecialchars($transaction['DeliveryFee']), 2) ?></td>
                                <td><?= number_format(htmlspecialchars($transaction['TotalPrice']), 2) ?></td>
                                <td><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                                <td><?= htmlspecialchars($transaction['City'] . ', ' . $transaction['Province']) ?></td> <!-- Displaying Location -->
                                <td>
                                    <!-- Button to trigger the modal for cart records -->
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#cartModal" data-transac-id="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                        <i class="bi bi-eye"></i> View Items
                                    </button>
                                </td>
                                <form method="POST" action="../transac_update.php" class="d-flex justify-content-center align-items-center">
                                    <td>
                                    <div class="d-flex justify-content-center">
                                        <input type="hidden" name="transac_id" value="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                        <input type="hidden" name="action" value="ToShip"> <!-- Set action to 'ToShip' -->
                                        <button class="btn btn-success btn-sm w-50 me-2">
                                            <a href="../transac_update.php" class="text-white" onclick="return confirm('Are you sure you want to mark this transaction as to ship?');" style="text-decoration: none;">
                                                <i class="bi bi-truck fs-5"></i> Deliver
                                            </a>
                                        </button>
                                        <button class="btn btn-danger btn-sm w-50">
                                            <a href="../transac_delete.php?id=<?= htmlspecialchars($transaction['TransacID']) ?>" class="text-white" onclick="return confirm('Are you sure you want to delete this transaction?');" style="text-decoration: none;">
                                                <i class="bi bi-trash fs-5"></i> Delete
                                            </a>
                                        </button>
                                    </div>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            </div>
        <?php else: ?>
            <p class="mt-4">No pending transactions found.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Modal -->
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
                "pageLength": 10
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
