<?php
session_start();
include("./../../../includes/cdn.php"); 
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = './../../../login.php';</script>";
    exit;
}

// Determine the search value, either from URL or form submission
$search_value = '';
if (isset($_GET['search_value']) && !empty($_GET['search_value'])) {
    $search_value = trim($_GET['search_value']);
}

// Fetch inventory records along with product details based on search value
$inventory_query = "
    SELECT i.InventoryID, p.ProductID, p.ProductName, p.ProductDesc, i.InventoryQty, c.CategoryName 
    FROM InventoryTb i 
    JOIN ProductTb p ON i.ProductID = p.ProductID 
    JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID
";

if (!empty($search_value)) {
    $inventory_query .= " WHERE p.ProductName LIKE :search_value OR p.ProductDesc LIKE :search_value";
}

$inventory_stmt = $conn->prepare($inventory_query);

if (!empty($search_value)) {
    $search_param = '%' . $search_value . '%'; // Wildcard search for partial matches
    $inventory_stmt->bindParam(':search_value', $search_param);
}

$inventory_stmt->execute();
$inventory_records = $inventory_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory List</title>
    <link rel="stylesheet" href="path-to-bootstrap.css"> <!-- Add bootstrap link if needed -->
    <style>
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
        <h3>Inventory List</h3>
        <form method="GET" action="">
            <div class="form-group">
                <label for="search_value">Search by Product Name or Description:</label>
                <input type="text" name="search_value" id="search_value" class="form-control" 
                       value="<?= htmlspecialchars($search_value) ?>">
            </div>
            <button type="submit" class="btn btn-primary mt-2">Search</button>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Inventory ID</th>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Product Description</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($inventory_records) > 0): ?>
                    <?php foreach ($inventory_records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['InventoryID']) ?></td>
                            <td><?= htmlspecialchars($record['ProductID']) ?></td>
                            <td><?= htmlspecialchars($record['ProductName']) ?></td>
                            <td><?= htmlspecialchars($record['ProductDesc']) ?></td>
                            <td><?= htmlspecialchars($record['CategoryName']) ?></td>
                            <td><?= htmlspecialchars($record['InventoryQty']) ?></td>
                            <td>
                                <a href="inventory_update.php?id=<?= htmlspecialchars($record['InventoryID']) ?>" class="btn btn-warning btn-sm">Update</a> | 
                                <a href="inventory_delete.php?id=<?= htmlspecialchars($record['InventoryID']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this inventory?');">Delete</a> |
                                <a href="inventory_create.php?product_id=<?= htmlspecialchars($record['ProductID']) ?>" class="btn btn-info btn-sm">Add Stocks</a> |
                                <a href="../../sales_management_system/onhand/onhand_create.php?inventory_id=<?= htmlspecialchars($record['InventoryID']) ?>" class="btn btn-success btn-sm">Add to Onhand</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No inventory records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a href="inventory_create.php" class="btn btn-success">Add New Inventory</a>
        <br><br>
        <a href="../../sales_management_system/onhand/onhand_read.php" class="btn btn-secondary">Go to Onhand List</a>
    </div>
</body>
</html>
