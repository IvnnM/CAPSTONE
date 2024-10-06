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

// Fetch on-hand records along with product and inventory details based on search value
$onhand_query = "
    SELECT o.OnhandID, o.OnhandQty, o.RetailPrice, o.MinPromoQty, o.PromoPrice, 
           i.InventoryID, p.ProductID, p.ProductName, p.ProductDesc, c.CategoryName 
    FROM OnhandTb o 
    JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
    JOIN ProductTb p ON i.ProductID = p.ProductID 
    JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID
";

if (!empty($search_value)) {
    $onhand_query .= " WHERE p.ProductName LIKE :search_value OR p.ProductDesc LIKE :search_value";
}

$onhand_stmt = $conn->prepare($onhand_query);

if (!empty($search_value)) {
    $search_param = '%' . $search_value . '%'; // Wildcard search for partial matches
    $onhand_stmt->bindParam(':search_value', $search_param);
}

$onhand_stmt->execute();
$onhand_records = $onhand_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onhand List</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="./../../../assets/css/form.css">
    <style>

    </style>
</head>
<body>
    <div class="container">
        <h3>Onhand List</h3>
                <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../views/admin_view.php#Products">Home</a></li>
                <li class="breadcrumb-item"><a href="../../inventory_management_system/inventory/inventory_read.php">Add New Onhand</a></li>
                <li class="breadcrumb-item"><a href="../../inventory_management_system/product/product_read.php">Go to Product List</a></li>
                <li class="breadcrumb-item"><a href="../../inventory_management_system/inventory/inventory_read.php">Go to Inventory List</a></li>
                <li class="breadcrumb-item active" aria-current="page">Onhand List</li>
            </ol>
        </nav>
        <h4 class="mt-4">Onhand Records</h4>
        <div class="container">
            <div class="table-responsive">
                <table id="onhandTable" class="display table table-bordered table-striped table-hover fixed-table">
                    <thead>
                        <tr>
                            <th>Onhand ID</th>
                            <th>Inventory ID</th>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Product Description</th>
                            <th>Category</th>
                            <th>Onhand Quantity</th>
                            <th>Retail Price</th>
                            <th>Minimum Promo Quantity</th>
                            <th>Promo Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($onhand_records) > 0): ?>
                            <?php foreach ($onhand_records as $record): ?>
                                <tr>
                                    <td><?= htmlspecialchars($record['OnhandID']) ?></td>
                                    <td><?= htmlspecialchars($record['InventoryID']) ?></td>
                                    <td><?= htmlspecialchars($record['ProductID']) ?></td>
                                    <td><?= htmlspecialchars($record['ProductName']) ?></td>
                                    <td><?= htmlspecialchars($record['ProductDesc']) ?></td>
                                    <td><?= htmlspecialchars($record['CategoryName']) ?></td>
                                    <td><?= htmlspecialchars($record['OnhandQty']) ?></td>
                                    <td><?= htmlspecialchars($record['RetailPrice']) ?></td>
                                    <td><?= htmlspecialchars($record['MinPromoQty']) ?></td>
                                    <td><?= htmlspecialchars($record['PromoPrice']) ?></td>
                                    <td>
                                      <div class="d-flex mb-2">
                                        <a href="onhand_update.php?onhand_id=<?= htmlspecialchars($record['OnhandID']) ?>" class="btn btn-warning btn-sm me-2">
                                            <i class="bi bi-pencil"></i> <!-- Update icon -->
                                        </a>
                                        <a href="onhand_delete.php?onhand_id=<?= htmlspecialchars($record['OnhandID']) ?>" class="btn btn-danger btn-sm me-2" onclick="return confirm('Are you sure you want to delete this onhand record?');">
                                            <i class="bi bi-trash"></i> <!-- Delete icon -->
                                        </a>
                                      </div>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11">No onhand records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#onhandTable').DataTable({
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
