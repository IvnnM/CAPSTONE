<?php
session_start();
include("./../../../includes/cdn.html"); 
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory List</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <style>
    .table td {
        vertical-align: middle;
    }
    </style>
</head>
<body>
<?php include("../../../includes/personnel/header.php"); ?>
<?php include("../../../includes/personnel/navbar.php"); ?>
    <div class="container-fluid"><hr>
        <div class="sticky-top bg-light pb-2">
            <h3>Inventory List</h3>
            <!-- Breadcrumb Navigation -->
            <!--<nav aria-label="breadcrumb">-->
            <!--    <ol class="breadcrumb">-->
            <!--        <li class="breadcrumb-item"><a href="./../../../views/personnel_view.php#Products">Home</a></li>-->
            <!--        <li class="breadcrumb-item"><a href="./../product/category/category_read.php">Product Category List</a></li>-->
            <!--        <li class="breadcrumb-item"><a href="../product/product_read.php">Product List</a></li>-->
            <!--        <li class="breadcrumb-item active" aria-current="page">Product Inventory List</li>-->
            <!--        <li class="breadcrumb-item"><a href="./../../sales_management_system/onhand/onhand_read.php">Product Onhand List</a></li>-->
            <!--    </ol>-->
            <!--</nav><hr>-->
        </div>

        <div class="table-responsive">
            <table id="inventoryTable" class="table table-light table-hover border-secondary pt-2">
                <thead class="table-info">
                    <tr>
                        <th class="col-auto">ID</th>
                        <th class="col-auto">Product ID</th>
                        <th class="col-auto">Product</th>
                        <th class="col-3">Description</th>
                        <th class="col-auto">Category</th>
                        <th class="col-auto">Stocks</th>
                        <?php if (isset($_SESSION['AdminID'])): ?>
                            <th class="col-auto">Admin Actions</th>
                        <?php elseif (isset($_SESSION['EmpID'])): ?>
                            <th class="col-auto">Employee Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($inventory_records) > 0): ?>
                        <?php foreach ($inventory_records as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['InventoryID']) ?></td>
                                <td><?= htmlspecialchars($record['ProductID']) ?></td>
                                <td><?= htmlspecialchars($record['ProductName']) ?></td>
                                <td class="description-cell"><?= htmlspecialchars($record['ProductDesc']) ?></td>
                                <td><?= htmlspecialchars($record['CategoryName']) ?></td>
                                <td><?= htmlspecialchars($record['InventoryQty']) ?></td>
                                <td class="text-center">
                                <!-- Admin Actions -->
                                <?php if (isset($_SESSION['AdminID'])): ?>
                                    <div class="d-flex justify-content-center">
                                        <a href="inventory_update.php?id=<?= htmlspecialchars($record['InventoryID']) ?>" class="btn btn-warning btn-sm me-2 w-50">Update</a>
                                        <a href="inventory_delete.php?id=<?= htmlspecialchars($record['InventoryID']) ?>" onclick="return confirm('Are you sure you want to delete this inventory?');" class="btn btn-danger btn-sm me-2 w-50">Delete</a>
                                        <a href="../../sales_management_system/onhand/onhand_create.php?inventory_id=<?= htmlspecialchars($record['InventoryID']) ?>" class="btn btn-primary btn-sm w-50">Add to Store</a>
                                    </div>
                                <!-- Employee Actions -->
                                <?php elseif (isset($_SESSION['EmpID'])): ?>
                                    <div class="d-flex justify-content-center">
                                        <a href="inventory_create.php?product_id=<?= htmlspecialchars($record['ProductID']) ?>" class="btn btn-outline-success btn-sm me-2 w-50">Replenish stock</a>
                                    </div>
                                <?php endif; ?>
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
        </div>
    </div>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#inventoryTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 5, // Default number of entries per page
                "lengthMenu": [5, 10, 25, 50, 100], // Options for number of entries
            });
        });
    </script>
</body>
</html>
