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
<html>
<head>
    <title>Product List</title>
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
        <h3>Product List</h3>
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
                                <a href="product_update.php?id=<?= htmlspecialchars($product['ProductID']) ?>" class="btn btn-warning btn-sm">Update</a> | 
                                <a href="product_delete.php?id=<?= htmlspecialchars($product['ProductID']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a> | 
                                <a href="../inventory/inventory_create.php?product_id=<?= htmlspecialchars($product['ProductID']) ?>" class="btn btn-info btn-sm">Add to Inventory</a>
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
        <br>
        <a href="product_create.php" class="btn btn-success">Add New Product</a>
        <br><br>
        <a href="../inventory/inventory_read.php" class="btn btn-secondary">Go to Inventory List</a>
    </div>
</body>
</html>
