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
    SELECT i.InventoryID, p.ProductID, p.ProductName, p.ProductDesc, i.InventoryQty, c.CategoryName, i.ReorderLevel, i.MaxStockLevel
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

// Check if the user wants to update levels
if (isset($_POST['update_levels'])) {
    // Include the inventory update levels script
    include("./inventory_update_levels.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory List</title>
    
    <link rel="stylesheet" href="./../../../assets/css/form.css">
    <style>
    </style>
</head>
<body>
    <div class="container">
        <h3>Inventory List</h3>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../views/admin_view.php#Products">Home</a></li>
                <li class="breadcrumb-item"><a href="../product/product_read.php">Add New Inventory</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inventory List</li>
                <li class="breadcrumb-item"><a href="../../sales_management_system/onhand/onhand_read.php">Go to Onhand List</a></li>
            </ol>
        </nav>

        <form method="POST" action="">
            <button type="submit" name="update_levels" class="btn btn-warning mt-2">Update Reorder and Max Stock Levels</button>
        </form>
        <h4 class="mt-4">Inventory Records</h4>
        <div class="container">
            <div class="table-responsive">
                <table id="inventoryTable" class="display table table-bordered table-striped table-hover fixed-table">
                    <thead>
                        <tr>
                            <th>Inventory ID</th>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Product Description</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Reorder Level</th>
                            <th>Max Stock Level</th>
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
                                    <td><?= htmlspecialchars($record['ReorderLevel']) ?></td>
                                    <td><?= htmlspecialchars($record['MaxStockLevel']) ?></td>
                                    <td>
                                        <div class="text-center">
                                            <button class="btn btn-secondary" type="button" id="actionMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="actionMenu">
                                                <li>
                                                    <a class="dropdown-item" href="inventory_update.php?id=<?= htmlspecialchars($record['InventoryID']) ?>">
                                                        <i class="bi bi-pencil"></i> Update
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="inventory_delete.php?id=<?= htmlspecialchars($record['InventoryID']) ?>" onclick="return confirm('Are you sure you want to delete this inventory?');">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="inventory_create.php?product_id=<?= htmlspecialchars($record['ProductID']) ?>">
                                                        <i class="bi bi-plus-circle"></i> Add Stocks
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="../../sales_management_system/onhand/onhand_create.php?inventory_id=<?= htmlspecialchars($record['InventoryID']) ?>">
                                                        <i class="bi bi-box"></i> Add to Onhand
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">No inventory records found.</td>
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
            $('#inventoryTable').DataTable({
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
