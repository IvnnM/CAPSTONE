<?php
// Fetch categories from the database
$query = "SELECT CategoryID, CategoryName FROM ProductCategoryTb";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize category filter
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

// Use PDO to fetch data based on the selected category
$sql = "SELECT o.OnhandID, o.OnhandQty, o.RetailPrice, o.PromoPrice, p.ProductImage, p.ProductName, p.ProductDesc 
        FROM OnhandTb o 
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
        JOIN ProductTb p ON i.ProductID = p.ProductID";

if ($selectedCategory) {
    $sql .= " WHERE p.CategoryID = :categoryID";
}

$stmt = $conn->prepare($sql);

if ($selectedCategory) {
    $stmt->bindParam(':categoryID', $selectedCategory, PDO::PARAM_INT);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Products List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <!-- Category Dropdown -->
        <div class="dropdown p-1">
            <button class="btn btn-outline-dark dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Select Category
            </button>
            <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                <li><a class="dropdown-item" href="?">All Products</a></li>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a class="dropdown-item" href="?category=<?php echo htmlspecialchars($category['CategoryID']); ?>">
                            <?php echo htmlspecialchars($category['CategoryName']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Products List -->
        <div class="row">
            <?php
            $base_url = '/3CAPSTONE/modules/inventory_management_system/product/';
            foreach ($products as $row):
                $image_path = $base_url . htmlspecialchars($row['ProductImage']);
            ?>
                <div class="col-lg-3 mb-1 mt-1">
                    <div class="card border-info h-100 p-1">
                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($row['ProductName']) ?>" class="card-img-top" style="width:100%; height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['ProductName']) ?></h5>
                            <p class="card-text text-truncate"><?= htmlspecialchars($row['ProductDesc']) ?></p>
                            <button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#descModal<?= $row['OnhandID'] ?>">
                                Read more
                            </button>
                            <p class="card-text">
                                Retail Price: <?= htmlspecialchars($row['RetailPrice']) ?> <br>
                                Promo Price: <?= htmlspecialchars($row['PromoPrice']) ?> <br>
                                Available Quantity: <?= htmlspecialchars($row['OnhandQty']) ?>
                            </p>

                            <?php if ($row['OnhandQty'] > 0): ?>
                                <?php if (isset($_SESSION['cust_email'])): ?>
                                    <!-- Add to Cart Button visible only if the customer session is set -->
                                    <a href="../modules/sales_management_system/transaction/cart/add_to_cart.php?onhand_id=<?= htmlspecialchars($row['OnhandID']) ?>" class="btn btn-success">Add to Cart</a>
                                <?php else: ?>
                                    <!-- Message or disabled button if the customer session is not set -->
                                    <p class="text-danger">Please submit your information to add items to the cart.</p>
                                    <a href="#" class="btn btn-secondary" disabled>Add to Cart</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Out of stock button (disabled) -->
                                <button class="btn btn-secondary" disabled>Out Of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal for full product description -->
                <div class="modal fade" id="descModal<?= $row['OnhandID'] ?>" tabindex="-1" aria-labelledby="descModalLabel<?= $row['OnhandID'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="descModalLabel<?= $row['OnhandID'] ?>">Product Description</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?= htmlspecialchars($row['ProductDesc']) ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
