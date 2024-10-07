<?php
// Fetch categories from the database
$query = "SELECT CategoryID, CategoryName FROM ProductCategoryTb";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize category filter
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

// Use PDO to fetch data based on the selected category
$sql = "SELECT o.OnhandID, o.OnhandQty, o.RetailPrice, o.PromoPrice, p.ProductImage, p.ProductName 
        FROM OnhandTb o 
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
        JOIN ProductTb p ON i.ProductID = p.ProductID";

if ($selectedCategory) {
    $sql .= " WHERE p.CategoryID = :categoryID"; // Assuming CategoryID is in ProductTb
}

$stmt = $conn->prepare($sql);

if ($selectedCategory) {
    $stmt->bindParam(':categoryID', $selectedCategory, PDO::PARAM_INT);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Products List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-5">
        <!-- Category Dropdown -->
        <div class="dropdown mb-3">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Select Category
            </button>
            <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                <li><a class="dropdown-item" href="?">All Products</a></li> <!-- Link to show all products -->
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
            // Define the base URL for images if necessary
            $base_url = '/2CAPSTONE/modules/inventory_management_system/product/';
            foreach ($products as $row): 
                // Construct the image path based on the stored ProductImage value
                $image_path = $base_url . htmlspecialchars($row['ProductImage']);
            ?>
                <div class="col-lg-3 mt-3 mb-3">
                    <div class="card border-info p-2">
                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($row['ProductName']) ?>" class="card-img-top" style="width:100%; height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['ProductName']) ?></h5>
                            <p class="card-text">
                                Retail Price: <?= htmlspecialchars($row['RetailPrice']) ?> <br>
                                Promo Price: <?= htmlspecialchars($row['PromoPrice']) ?> <br>
                                Available Quantity: <?= htmlspecialchars($row['OnhandQty']) ?>
                            </p>
                            
                            <?php if ($row['OnhandQty'] > 0): ?>
                                <a href="../modules/sales_management_system/transaction/transac_create_retail.php?onhand_id=<?= htmlspecialchars($row['OnhandID']) ?>" class="btn btn-primary">Buy in Retail</a>
                                <a href="../modules/sales_management_system/transaction/transac_create_promo.php?onhand_id=<?= htmlspecialchars($row['OnhandID']) ?>" class="btn btn-warning">Buy in Promo</a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Out Of Stock</button>
                                <button class="btn btn-warning" disabled>Out Of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
