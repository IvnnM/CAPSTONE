<?php
session_start();
include("../../../../includes/cdn.php"); 
include("../../../../config/database.php");

// Determine the search value, either from URL or form submission
$search_value = '';
if (isset($_GET['cust_num']) && !empty($_GET['cust_num'])) {
    $search_value = trim($_GET['cust_num']);
} elseif (isset($_GET['search_value']) && !empty($_GET['search_value'])) {
    $search_value = trim($_GET['search_value']);
}

// Initialize the transactions array
$transactions = [];

// Fetch the transactions only if there is a search value
if (!empty($search_value)) {
    $sql = "SELECT t.*, o.OnhandQty, o.RetailPrice, o.PromoPrice, p.ProductName
            FROM TransacTb t
            JOIN OnhandTb o ON t.OnhandID = o.OnhandID
            JOIN InventoryTb i ON o.InventoryID = i.InventoryID
            JOIN ProductTb p ON i.ProductID = p.ProductID
            WHERE (t.CustNum LIKE :search_value OR t.CustEmail LIKE :search_value) 
            AND t.Status = 'Delivered'";

    $stmt = $conn->prepare($sql);
    $search_param = '%' . $search_value . '%'; // Wildcard search for partial matches
    $stmt->bindParam(':search_value', $search_param);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Transactions</title>
    <link rel="stylesheet" href="path-to-bootstrap.css"> <!-- Add bootstrap link if needed -->
    <style>
        /* Add some basic styling */
        .container {
            margin-top: 30px;
        }
        .table {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Search Delivered Transactions</h3>
                <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../../views/customer_view.php#Orders">Home</a></li>
                <li class="breadcrumb-item"><a href="transac_read_pending.php">To Pay</a></li>
                <li class="breadcrumb-item"><a href="transac_read_approved.php">To Receive</a></li>
                <li class="breadcrumb-item active" aria-current="page">Completed</li>
            </ol>
        </nav>
        <form method="GET" action="">
            <div class="form-group">
                <label for="search_value">Enter Customer Number or Email:</label>
                <input type="text" name="search_value" id="search_value" class="form-control" 
                       value="<?= htmlspecialchars($search_value) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Search</button>
        </form>
        <?php if (!empty($transactions)): ?>
            <h4 class="mt-4">Pending Transaction Records</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer Name</th>
                        <th>Customer Number</th>
                        <th>Customer Email</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Transaction Date</th>
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
                            <td><?= htmlspecialchars($transaction['TotalPrice']) ?></td>
                            <td><?= htmlspecialchars($transaction['Status']) ?></td>
                            <td><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($search_value): ?>
            <p class="mt-4">No pending transactions found for the given Customer Number or Email.</p>
        <?php endif; ?>
    </div>
</body>
</html>
