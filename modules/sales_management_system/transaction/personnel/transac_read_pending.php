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

// Fetch all pending transactions
$sql = "SELECT t.*, o.OnhandQty, o.RetailPrice, o.PromoPrice, p.ProductName
        FROM TransacTb t
        JOIN OnhandTb o ON t.OnhandID = o.OnhandID
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID
        JOIN ProductTb p ON i.ProductID = p.ProductID
        WHERE t.Status = 'Pending'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status change to approved
if (isset($_POST['approve_transaction'])) {
    $transac_id = $_POST['transac_id'];

    // Fetch the quantity, OnhandID, and DeliveryFee of the transaction being approved
    $transaction_query = "SELECT Quantity, OnhandID, DeliveryFee FROM TransacTb WHERE TransacID = :transac_id";
    $transaction_stmt = $conn->prepare($transaction_query);
    $transaction_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
    $transaction_stmt->execute();
    $transaction = $transaction_stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        $quantity_sold = $transaction['Quantity'];
        $onhand_id = $transaction['OnhandID'];
        $delivery_fee = $transaction['DeliveryFee']; // Get the DeliveryFee

        // Update the stock quantity in OnhandTb
        $update_stock_query = "UPDATE OnhandTb SET OnhandQty = OnhandQty - :quantity_sold WHERE OnhandID = :onhand_id";
        $update_stock_stmt = $conn->prepare($update_stock_query);
        $update_stock_stmt->bindParam(':quantity_sold', $quantity_sold, PDO::PARAM_INT);
        $update_stock_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);

        // Start the transaction for data integrity
        $conn->beginTransaction();

        try {
            // Execute the stock quantity update
            if (!$update_stock_stmt->execute()) {
                throw new Exception('Could not update product quantity.');
            }

            // Update the status to 'Approved'
            $update_sql = "UPDATE TransacTb SET Status = 'ToShip', TransactionDate = NOW() WHERE TransacID = :transac_id"; // Update date to now
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
            if (!$update_stmt->execute()) {
                throw new Exception('Could not approve transaction.');
            }

            // Commit the transaction
            $conn->commit();
            echo "<script>alert('Transaction Ship successfully!');</script>";
            echo "<script>window.history.back();</script>";
            exit;

        } catch (Exception $e) {
            // Roll back the transaction in case of error
            $conn->rollBack();
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Transaction details not found.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Transactions</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
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
            <h4 class="mt-4">Orders</h4>
            <div class="container">
                <div class="table-responsive">
                    <table id="transactionsTable" class="display table table-bordered table-striped table-hover fixed-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Number</th>
                                <th>Email</th>
                                <th>Product</th>
                                <th>Qtty</th>
                                <th>Price</th>
                                <th>Delivery Fee</th> <!-- Added Delivery Fee Column -->
                                <th>Total Cost</th>
                                <th>Date</th>
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
                                    <td><?= htmlspecialchars($transaction['ProductName']) ?></td>
                                    <td><?= htmlspecialchars($transaction['Quantity']) ?></td>
                                    <td><?= htmlspecialchars($transaction['Price']) ?></td>
                                    <td><?= htmlspecialchars($transaction['DeliveryFee']) ?></td> <!-- Display Delivery Fee -->
                                    <td><?= htmlspecialchars($transaction['TotalPrice']) ?></td>
                                    <td><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                                    <td>
                                        <form method="POST" action="" class="d-flex align-items-center">
                                            <input type="hidden" name="transac_id" value="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                            <button type="submit" name="approve_transaction" class="btn btn-success btn-sm me-2" onclick="return confirm('Are you sure you want to mark this transaction as to ship?');">
                                                <i class="bi bi-check-lg"></i> <!-- Approve icon -->
                                            </button>
                                            <a href="../transac_delete.php?id=<?= htmlspecialchars($transaction['TransacID']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transaction?');">
                                                <i class="bi bi-trash"></i> <!-- Delete icon -->
                                            </a>
                                        </form>
                                    </td>
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
        });
    </script>
</body>
</html>
