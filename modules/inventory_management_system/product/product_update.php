<?php
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

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the existing product details
    $query = "SELECT * FROM ProductTb WHERE ProductID = :product_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();

    // Check if the product exists
    if ($stmt->rowCount() > 0) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch categories for the dropdown
        $category_query = "SELECT * FROM ProductCategoryTb";
        $category_stmt = $conn->prepare($category_query);
        $category_stmt->execute();
        $categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['alert'] = 'Product not found.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: product_read.php");
        exit;
    }
} else {
    $_SESSION['alert'] = 'Invalid product ID.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: product_read.php");
    exit;
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_product_name = $_POST['product_name'];
    $new_product_desc = $_POST['product_desc'];
    $new_category_id = $_POST['category_id'];
    $product_image = $product['ProductImage']; // Retain the existing image

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['product_image']['tmp_name'];
        $name = basename($_FILES['product_image']['name']);
        $upload_dir = 'uploads/';
        $product_image = $upload_dir . $name;

        // Move the uploaded file
        if (!move_uploaded_file($tmp_name, $product_image)) {
            $_SESSION['alert'] = 'Error: Could not upload the image.';
            $_SESSION['alert_type'] = 'danger';
            $product_image = $product['ProductImage']; // Reset to the existing image if upload fails
        }
    }

    // Update the product in the database
    $update_query = "UPDATE ProductTb SET ProductName = :product_name, ProductDesc = :product_desc, CategoryID = :category_id, ProductImage = :product_image WHERE ProductID = :product_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':product_name', $new_product_name);
    $update_stmt->bindParam(':product_desc', $new_product_desc);
    $update_stmt->bindParam(':category_id', $new_category_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':product_image', $product_image);
    $update_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        $_SESSION['alert'] = 'Product updated successfully!';
        $_SESSION['alert_type'] = 'success';
        header("Location: product_read.php");
        exit;
    } else {
        $_SESSION['alert'] = 'Error: Could not update the product.';
        $_SESSION['alert_type'] = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Update Product</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Product</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
            <h6>Update Product Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars($product['ProductName']) ?>" placeholder="Product Name" required>
                        <label for="product_name">Product Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['CategoryID']) ?>" <?= $category['CategoryID'] == $product['CategoryID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['CategoryName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="category_id">Product Category</label>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="product_desc" value="<?= htmlspecialchars($product['ProductDesc']) ?>" placeholder="Product Description">
                        <label for="product_desc">Product Description</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="product_image">Product Image</label>
                    <input type="file" class="form-control" name="product_image" accept="image/*">
                    <small>Leave blank if you do not want to change the image.</small>
                </div>
            </div>
            
            <button class="btn btn-success w-100 mb-2" type="submit">Update</button>
            <a class="btn btn-secondary w-100 mb-2" href="product_read.php">Cancel</a>
        </form>
    </div>
</body>

</html>
