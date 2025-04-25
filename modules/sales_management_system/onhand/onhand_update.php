<?php
//onhand_update.php
session_start();
include("./../../../includes/cdn.html");
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    $_SESSION['alert'] = 'You must be logged in to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ./../../../login.php");
    exit;
}

// Check if an Onhand ID is provided in the URL
if (isset($_GET['onhand_id'])) {
    $onhand_id = $_GET['onhand_id'];

    // Fetch existing on-hand record for display
    $onhand_query = "SELECT o.*, p.ProductName, c.CategoryName 
                     FROM OnhandTb o 
                     JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
                     JOIN ProductTb p ON i.ProductID = p.ProductID 
                     JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID 
                     WHERE o.OnhandID = :onhand_id";
    $onhand_stmt = $conn->prepare($onhand_query);
    $onhand_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);
    $onhand_stmt->execute();
    $onhand = $onhand_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$onhand) {
        $_SESSION['alert'] = 'Onhand record not found.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: ./../../../onhand_read.php"); // Change to appropriate page
        exit;
    }
} else {
    $_SESSION['alert'] = 'Invalid onhand ID.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ./../../../onhand_read.php"); // Change to appropriate page
    exit;
}

// Handle form submission for updating on-hand record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $onhand_qty = $_POST['onhand_qty'];
    $retail_price = $_POST['retail_price'];
    $min_promo_qty = $_POST['min_promo_qty'];
    $promo_price = $_POST['promo_price'];
    $restock_threshold = $_POST['restock_threshold'];

    // Update the OnhandTb with the new values
    $update_query = "UPDATE OnhandTb 
                     SET OnhandQty = :onhand_qty, 
                         RetailPrice = :retail_price, 
                         MinPromoQty = :min_promo_qty, 
                         PromoPrice = :promo_price,
                         RestockThreshold = :restock_threshold
                     WHERE OnhandID = :onhand_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':onhand_qty', $onhand_qty, PDO::PARAM_INT);
    $update_stmt->bindParam(':retail_price', $retail_price, PDO::PARAM_STR);
    $update_stmt->bindParam(':min_promo_qty', $min_promo_qty, PDO::PARAM_INT);
    $update_stmt->bindParam(':promo_price', $promo_price, PDO::PARAM_STR);
    $update_stmt->bindParam(':restock_threshold', $restock_threshold, PDO::PARAM_INT);
    $update_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        $_SESSION['alert'] = 'Onhand record updated successfully!';
        $_SESSION['alert_type'] = 'success';
        header("Location: onhand_read.php"); // Change to appropriate page
        exit;
    } else {
        $_SESSION['alert'] = 'Error: Could not update onhand record.';
        $_SESSION['alert_type'] = 'danger';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Onhand Record</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Update Onhand Record</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Onhand Record</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="" onsubmit="confirmUpdate(event);">
            <h6>Update Onhand Information</h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="product_name" id="product_name" value="<?= htmlspecialchars($onhand['ProductName']) ?>" readonly>
                        <label for="product_name">Product Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="category_name" id="category_name" value="<?= htmlspecialchars($onhand['CategoryName']) ?>" readonly>
                        <label for="category_name">Category</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="onhand_qty" name="onhand_qty" min="0" value="<?= htmlspecialchars($onhand['OnhandQty']) ?>" required>
                        <label for="onhand_qty">Onhand Quantity</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="retail_price" id="retail_price" value="<?= htmlspecialchars($onhand['RetailPrice']) ?>" required>
                        <label for="retail_price">Retail Price</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" name="min_promo_qty" id="min_promo_qty" min="1" value="<?= htmlspecialchars($onhand['MinPromoQty']) ?>" required>
                        <label for="min_promo_qty">Minimum Promo Quantity</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="promo_price" id="promo_price" value="<?= htmlspecialchars($onhand['PromoPrice']) ?>" required>
                        <label for="promo_price">Promo Price</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" name="restock_threshold" id="restock_threshold" min="0" value="<?= htmlspecialchars($onhand['RestockThreshold']) ?>" required>
                        <label for="restock_threshold">Restock Threshold</label>
                    </div>
                </div>
            </div>

            <button class="btn btn-success w-100 mb-2" type="submit">Update</button>
            <a class="btn btn-secondary w-100" href="onhand_read.php">Cancel</a>
        </form>
    </div>
</body>
</html>
