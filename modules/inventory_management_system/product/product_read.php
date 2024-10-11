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

// Fetch products from the database based on search value
$product_query = "
    SELECT p.*, c.CategoryName 
    FROM ProductTb p 
    JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID
";

if (!empty($search_value)) {
    $product_query .= " WHERE p.ProductName LIKE :search_value OR p.ProductDesc LIKE :search_value";
}

$product_stmt = $conn->prepare($product_query);

if (!empty($search_value)) {
    $search_param = '%' . $search_value . '%'; // Wildcard search for partial matches
    $product_stmt->bindParam(':search_value', $search_param);
}

$product_stmt->execute();
$products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
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
    <div class="container-fluid">
        <div class="sticky-top bg-light pb-2">
            <h3>Product List</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="./../../../views/admin_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item"><a href="./../product/category/category_read.php">Product Category List</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product List</li>
                    <li class="breadcrumb-item"><a href="./../inventory/inventory_read.php">Product Inventory List</a></li>
                    <li class="breadcrumb-item"><a href="../../../sales_management_system/onhand/onhand_read.php">Product Onhand List</a></li>
                </ol>
            </nav><hr>
            <div class="d-flex justify-content-end">
                <?php if (isset($_SESSION['AdminID'])): ?>
                    <button type="button" class="btn btn-success" onclick="window.location.href='product_create.php';">Create New Product</button>
                <?php elseif (isset($_SESSION['EmpID'])): ?>
                    
                <?php endif; ?> 
            </div>
        </div>

        <div class="table-responsive">
            <table id="productTable" class="table table-light table-hover border-secondary pt-2">
                <thead class="table-info">
                    <tr>
                        <th class="col-auto">Product ID</th>
                        <th class="col-auto">Product Name</th>
                        <th class="col-3">Product Description</th>
                        <th class="col-auto">Category</th>
                        <th class="col-auto">Product Image</th>
                        <?php if (isset($_SESSION['AdminID'])): ?>
                            <th class="col-auto">Admin Actions</th>
                        <?php elseif (isset($_SESSION['EmpID'])): ?>
                            <th class="col-auto">Employee Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['ProductID']) ?></td>
                                <td><?= htmlspecialchars($product['ProductName']) ?></td>
                                <td class="description-cell"><?= htmlspecialchars($product['ProductDesc']) ?></td>
                                <td><?= htmlspecialchars($product['CategoryName']) ?></td>
                                <td>
                                    <?php if ($product['ProductImage']): ?>
                                        <img src="<?= htmlspecialchars($product['ProductImage']) ?>" alt="<?= htmlspecialchars($product['ProductName']) ?>" width="100">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- Admin-only actions -->
                                    <?php if (isset($_SESSION['AdminID'])): ?>
                                        <div class="d-flex justify-content-center">
                                            <a href="product_update.php?id=<?= htmlspecialchars($product['ProductID']) ?>" class="btn btn-warning btn-sm me-2 w-50">Update</a>
                                            <a href="product_delete.php?id=<?= htmlspecialchars($product['ProductID']) ?>" onclick="return confirm('Are you sure you want to delete this product?');" class="btn btn-danger btn-sm w-50">Delete</a>
                                        </div>
                                    <!-- Employee-only actions -->
                                    <?php elseif (isset($_SESSION['EmpID'])): ?>
                                        <div class="d-flex justify-content-center">
                                            <a href="../inventory/inventory_create.php?product_id=<?= htmlspecialchars($product['ProductID']) ?>" class="btn btn-primary btn-sm w-50">Add to Inventory</a>
                                        </div>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#productTable').DataTable({
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
