<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Fetch delivered transactions for the current customer
$cust_email = $_SESSION['cust_email'] ?? '';
$query = "SELECT t.TransacID, t.CustName, t.CustNum, t.CustEmail, 
                 l.Province, l.City, t.DeliveryFee, t.TotalPrice, t.TransactionDate 
          FROM TransacTb t
          JOIN LocationTb l ON t.LocationID = l.LocationID
          WHERE t.CustEmail = :cust_email AND t.Status = 'Delivered' 
          ORDER BY t.TransactionDate DESC";
$stmt = $conn->prepare($query);
$stmt->execute(['cust_email' => $cust_email]);
$delivered_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivered Transactions</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="../../../../assets/css/table.css">
    <style>
        .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="sticky-top bg-light pb-2">
            <h3>Delivered Transactions</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/customer_view.php#Overview">Home</a></li>
                    <li class="breadcrumb-item"><a href="./order.php"><span class="status-badge status-pending">Pending</span></a></li>
                    <li class="breadcrumb-item"><a href="./toShip.php"><span class="status-badge status-accepted">Accepted</span></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><span class="status-badge status-completed">Completed</span></li>
                </ol>
            </nav>
            <hr>
            <!-- Button for Navigation -->
            <!--<div class="d-flex justify-content-end">-->
            <!--    <button type="button" class="btn btn-secondary" onclick="window.history.back();">Back</button>-->
            <!--</div>-->
        </div>
        <!-- Table to display delivered transactions -->
        <div class="table-responsive">
            <table id="transactionTable" class="display table table-light table-hover border-secondary pt-2">
                <thead class="table-info">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Email</th>
                        <th>Address</th> <!-- Province and City -->
                        <th>Delivery Fee</th>
                        <th>Total Price</th>
                        <th>Transaction Date</th>
                        <th>Cart Records</th> <!-- New Column -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($delivered_transactions)): ?>
                        <tr>
                            <td colspan="9">No delivered transactions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($delivered_transactions as $transaction): ?>
                            <tr>
                                <td data-label="Transaction ID"><?= htmlspecialchars($transaction['TransacID']) ?></td>
                                <td data-label="Name"><?= htmlspecialchars($transaction['CustName']) ?></td>
                                <td data-label="Number"><?= htmlspecialchars($transaction['CustNum']) ?></td>
                                <td data-label="Email"><?= htmlspecialchars($transaction['CustEmail']) ?></td>
                                <td data-label="Address"><?= htmlspecialchars($transaction['Province'] . ', ' . $transaction['City']) ?></td> <!-- Display Province and City -->
                                <td data-label="Delivery Fee"><?= number_format(htmlspecialchars($transaction['DeliveryFee']), 2) ?></td>
                                <td data-label="Total Price"><?= number_format(htmlspecialchars($transaction['TotalPrice']), 2) ?></td>
                                <td data-label="Transaction Date"><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                                <td data-label="Actions">
                                    <!-- Button to trigger the modal for cart records -->
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#cartModal" data-transac-id="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                        <i class="bi bi-eye"></i> View Items
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
            $('#transactionTable').DataTable({
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
                    url: '../personnel/fetch_cart_records.php', // URL to fetch cart records
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
