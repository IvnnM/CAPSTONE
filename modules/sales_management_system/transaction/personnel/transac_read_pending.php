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
    <style>
    .table td {
        vertical-align: middle;
    }
    </style>
</head>
<body>
<?php include("../../../../includes/personnel/header.php"); ?>
<?php include("../../../../includes/personnel/navbar.php"); ?>
    <div class="container-fluid"><hr>
        <div class="sticky-top bg-light pb-2">
            <h3>Pending Transactions</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <!--<li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Transaction">Home</a></li>-->
                    <li class="breadcrumb-item active" aria-current="page">Pending Transactions</li>
                    <li class="breadcrumb-item"><a href="transac_read_approved.php">To Ship Transactions</a></li>
                    <li class="breadcrumb-item"><a href="transac_read_delivered.php">Delivered Transactions</a></li>
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
                        <th>Date</th>
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
                                <!-- Button to trigger the modal for cart records -->
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#cartModal" data-transac-id="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                    <i class="bi bi-eye"></i> View Items
                                </button>
                            </td>
                           <form method="POST" action="../transac_update.php" class="d-flex justify-content-center align-items-center">
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <!-- Deliver Button -->
                                        <input type="hidden" name="transac_id" value="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                        <input type="hidden" name="action" value="ToShip">
                                        <button type="submit" class="btn btn-success btn-sm w-50 me-2">
                                            <i class="bi bi-check-circle fs-5"></i> Review
                                        </button>

                                        <!-- Decline Button -->
                                        <a href="../transac_delete.php?id=<?= htmlspecialchars($transaction['TransacID']) ?>" 
                                        class="btn btn-danger btn-sm w-50 text-white" 
                                        style="text-decoration: none;">
                                            <i class="bi bi-trash fs-5"></i> Decline
                                        </a>
                                    </div>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                                    <!--<th>Onhand ID</th>-->
                                    <th>Product Name</th>
                                    <th>Product Category</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <!--<th>Added Date</th>-->
                                </tr>
                            </thead>
                            <tbody id="cartRecordsBody">
                                <!-- Cart records will be populated here via JavaScript, 
                                     now with data-label attributes -->
                                <!-- Example structure for the dynamically populated rows -->
                                <tr>
                                    <!--<td data-label="Onhand ID">[Onhand ID]</td>-->
                                    <td data-label="Product Name">[Product Name]</td>
                                    <td data-label="Product Category">[Product Category]</td>
                                    <td data-label="Quantity">[Quantity]</td>
                                    <td data-label="Price">[Price]</td>
                                    <!--<td data-label="Added Date">[Added Date]</td>-->
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <strong>Total Price: </strong>
                        <span id="cartTotalPrice" class="text-success">₱0.00</span>
                    </div>
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
                "order": [[7, 'desc']]
            });
        
            // Handle cart modal
            $('#cartModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var transacId = button.data('transac-id');
                
                $.ajax({
                    url: 'fetch_cart_records.php',
                    type: 'GET',
                    data: { transac_id: transacId },
                    success: function(data) {
                        $('#cartRecordsBody').html(data);
                        
                        var totalPriceRow = $('#total-price-row');
                        if (totalPriceRow.length) {
                            var totalPrice = totalPriceRow.data('total-price');
                            $('#cartTotalPrice').text('P' + totalPrice);
                        } else {
                            $('#cartTotalPrice').text('P0.00');
                        }
                    },
                    error: function() {
                        $('#cartRecordsBody').html('<tr><td colspan="4" class="text-center">Error fetching records.</td></tr>');
                        $('#cartTotalPrice').text('P0.00');
                    }
                });
            });
        
            // Use event delegation and prevent multiple bindings
            $(document).on('submit', 'form[action="../transac_update.php"]', function(e) {
                e.preventDefault(); // Stop immediate form submission
                
                var $form = $(this);
                
                Swal.fire({
                    title: 'Mark Transaction as To Ship',
                    text: 'Are you sure you want to mark this transaction as ready to ship?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, mark as To Ship!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Use vanilla JavaScript to submit to avoid duplicate events
                        $form[0].submit();
                    }
                });
            });
        
            // Decline transaction with SweetAlert
            $(document).on('click', 'a[href^="../transac_delete.php"]', function(e) {
                e.preventDefault(); // Prevent immediate navigation
                
                var url = $(this).attr('href');
        
                Swal.fire({
                    title: 'Decline Transaction',
                    text: 'Are you sure you want to delete this transaction?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, decline transaction!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Use window location to navigate
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
</body>
</html>
