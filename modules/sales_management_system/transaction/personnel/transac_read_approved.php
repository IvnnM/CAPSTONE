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

// Fetch all approved transactions
$sql = "SELECT t.TransacID, t.CustName, t.CustNum, t.CustEmail, t.DeliveryFee, t.TotalPrice, t.TransactionDate, 
               l.Province, l.City, t.ExpectedDeliveryDate
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
    <title>To Ship Transactions</title>
    
</head>
<body>
<?php include("../../../../includes/personnel/header.php"); ?>
<?php include("../../../../includes/personnel/navbar.php"); ?>
    <div class="container-fluid"><hr>
        <div class="sticky-top bg-light pb-2">
            <h3>To Ship Transactions</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <!--<li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Transaction">Home</a></li>-->
                    <li class="breadcrumb-item"><a href="transac_read_pending.php">Pending Transactions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">To Ship Transactions</li>
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
                        <th>Date Ship</th>
                        <th>Cart Records</th>
                        <th>Expected Delivery Date</th>
                        <th>Days Left</th>
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
                                <form method="POST" action="../update_expected_delivery.php" class="d-flex align-items-center">
                                    <input type="hidden" name="transac_id" value="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                    <input type="date" name="expected_delivery_date" 
                                           class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($transaction['ExpectedDeliveryDate'] ?? '') ?>"
                                           min="<?= date('Y-m-d') ?>"
                                           required>
                                    <button type="submit" class="btn btn-primary btn-sm ms-2">
                                        <i class="bi bi-calendar-check"></i> Update
                                    </button>
                                </form>
                            </td>
                            <td>
                                <?php 
                                if (!empty($transaction['ExpectedDeliveryDate'])) {
                                    $transactionDate = new DateTime($transaction['TransactionDate']);
                                    $expectedDeliveryDate = new DateTime($transaction['ExpectedDeliveryDate']);
                                    
                                    // Calculate the difference
                                    $interval = $transactionDate->diff($expectedDeliveryDate);
                                    $days = $interval->days;
                                    
                                    // Determine color based on days
                                    $colorClass = '';
                                    if ($days <= 3) {
                                        $colorClass = 'text-danger'; // Less than or equal to 3 days
                                    } elseif ($days <= 5) {
                                        $colorClass = 'text-warning'; // 4-5 days
                                    } else {
                                        $colorClass = 'text-success'; // More than 5 days
                                    }
                                    
                                    echo '<span class="' . $colorClass . '">' . $days . ' days</span>';
                                } else {
                                    echo 'Not set';
                                }
                                ?>
                            </td>
                            <td>
                                <form method="POST" action="../transac_update.php" class="d-flex align-items-center">
                                    <input type="hidden" name="transac_id" value="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                    <input type="hidden" name="action" value="deliver"> <!-- Set action to 'deliver' -->
                                    <button type="submit" class="btn btn-success btn-sm me-2">
                                        <i class="bi bi-check-circle"></i> <!-- Complete icon -->
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
                                    <th>Product Name</th>
                                    <th>Product Category</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody id="cartRecordsBody">
                                <!-- Cart records will be populated here via JavaScript -->
                                <tr>
                                    <td data-label="Product Name">[Product Name]</td>
                                    <td data-label="Product Category">[Product Category]</td>
                                    <td data-label="Quantity">[Quantity]</td>
                                    <td data-label="Price">[Price]</td>
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
            "order": [[11, 'asc']]
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
            // Add validation to date inputs
    $('input[type="date"]').each(function() {
        // Set min date to today to prevent selecting past dates
        $(this).attr('min', function(){
            return new Date().toISOString().split('T')[0];
        });
    });

    // Handle form submission for both expected delivery date and complete transaction
        $('form[action="../update_expected_delivery.php"], form[action="../transac_update.php"]').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            var $form = $(this);
            var isDeliveryDateForm = $form.attr('action').includes('update_expected_delivery.php');
            
            // Validate date input for delivery date form
            if (isDeliveryDateForm) {
                var expectedDate = $form.find('input[type="date"]').val();
                var transacId = $form.find('input[name="transac_id"]').val();
                
                if (!expectedDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select an expected delivery date!'
                    });
                    return;
                }
            }

            // Determine confirmation message based on form type
            var title, text, successTitle, successText;
            if (isDeliveryDateForm) {
                title = 'Update Expected Delivery Date';
                text = 'Are you sure you want to set the expected delivery date to ' + expectedDate + '?';
                successTitle = 'Delivery Date Updated!';
                successText = 'Expected delivery date updated successfully.';
            } else {
                title = 'Complete Transaction';
                text = 'Are you sure you want to complete this transaction?';
                successTitle = 'Transaction Completed!';
                successText = 'The transaction has been successfully marked as delivered.';
            }

            // Show confirmation dialog
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit via AJAX
                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        success: function(response) {
                            try {
                                var result = JSON.parse(response);
                                if (result.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: successTitle,
                                        text: successText
                                    }).then(() => {
                                        location.reload(); // Reload to show updated data
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: result.message || 'Operation failed.'
                                    });
                                }
                            } catch (err) {
                                // If response is not JSON, show generic success message
                                Swal.fire({
                                    icon: 'success',
                                    title: successTitle,
                                    text: successText
                                }).then(() => {
                                    location.reload(); // Reload to show updated data
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Operation failed. Please try again.'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
</body>
</html>
