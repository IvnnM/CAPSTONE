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

// Fetch all delivered transactions
$sql = "SELECT t.TransacID, t.CustName, t.CustNum, t.CustEmail, t.DeliveryFee, t.TotalPrice, t.TransactionDate, 
               l.Province, l.City
        FROM TransacTb t
        JOIN LocationTb l ON t.LocationID = l.LocationID
        WHERE t.Status = 'Delivered'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivered Transactions</title>
</head>
<body>
<?php include("../../../../includes/personnel/header.php"); ?>
<?php include("../../../../includes/personnel/navbar.php"); ?>
    <div class="container-fluid"><hr>
        <div class="sticky-top bg-light pb-2">
            <h3>Delivered Transactions</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <!--<li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Transaction">Home</a></li>-->
                    <li class="breadcrumb-item"><a href="transac_read_pending.php">Pending Transactions</a></li>
                    <li class="breadcrumb-item"><a href="transac_read_approved.php">Approved Transactions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Delivered Transactions</li>
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
                        <th>Date Delivered</th>
                        <th>Cart Records</th>
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
                                    <i class="bi bi-eye"></i> View Cart Records
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
            <p class="mt-4">No delivered transactions found.</p>
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
                "pageLength": 5, // Default number of entries per page
                "lengthMenu": [5, 10, 25, 50, 100],
                "order": [[7, 'asc']]
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
                        
                        // Extract total price from the hidden row
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
        });
    </script>
</body>
</html>
