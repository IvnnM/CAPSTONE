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
</head>
<body>
    <div class="container">
        <h3>Product List</h3>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../views/admin_view.php#Products">Home</a></li>
                <li class="breadcrumb-item"><a href="product_create.php">Add New Product</a></li>
                <li class="breadcrumb-item active" aria-current="page">Product List</li>
                <li class="breadcrumb-item"><a href="../inventory/inventory_read.php">Go to Inventory List</a></li>
                <li class="breadcrumb-item"><a href="../../sales_management_system/onhand/onhand_read.php">Go to Onhand List</a></li>

            </ol>
        </nav>
        <h4 class="mt-4">Product Records</h4>
       
        <div class="container">
            <div class="table-responsive">
                <table id="productTable" class="display table table-bordered table-striped table-hover fixed-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Product Description</th>
                            <th>Category</th>
                            <th>Product Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['ProductID']) ?></td>
                                    <td><?= htmlspecialchars($product['ProductName']) ?></td>
                                    <td><?= htmlspecialchars($product['ProductDesc']) ?></td>
                                    <td><?= htmlspecialchars($product['CategoryName']) ?></td>
                                    <td>
                                        <?php if ($product['ProductImage']): ?>
                                            <img src="<?= htmlspecialchars($product['ProductImage']) ?>" alt="<?= htmlspecialchars($product['ProductName']) ?>" width="100">
                                        <?php else: ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <a href="product_update.php?id=<?= htmlspecialchars($product['ProductID']) ?>">Update</a> | 
                                            <a href="product_delete.php?id=<?= htmlspecialchars($product['ProductID']) ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a> | 
                                            <a href="../inventory/inventory_create.php?product_id=<?= htmlspecialchars($product['ProductID']) ?>">Add Product to Inventory</a>
                                        </div>
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
