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

// Fetch categories for the dropdown
$category_query = "SELECT * FROM ProductCategoryTb";
$category_stmt = $conn->prepare($category_query);
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ensure the upload directory exists
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
}

// Handle form submission for creating a product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $product_desc = $_POST['product_desc'];
    $category_id = $_POST['category_id'];
    
    // Handle image upload
    $product_image = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['product_image']['tmp_name'];
        $name = basename($_FILES['product_image']['name']);
        $product_image = $upload_dir . $name;

        // Move the uploaded file
        if (!move_uploaded_file($tmp_name, $product_image)) {
            $_SESSION['alert'] = 'Error: Could not upload the image.';
            $_SESSION['alert_type'] = 'danger';
        }
    } else {
        $_SESSION['alert'] = 'Error: No image uploaded or an upload error occurred.';
        $_SESSION['alert_type'] = 'danger';
    }

    // Insert the new product into the database if the image upload was successful
    if ($product_image !== null) {
        $insert_query = "INSERT INTO ProductTb (ProductName, ProductDesc, CategoryID, ProductImage) VALUES (:product_name, :product_desc, :category_id, :product_image)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindParam(':product_name', $product_name);
        $insert_stmt->bindParam(':product_desc', $product_desc);
        $insert_stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':product_image', $product_image);

        if ($insert_stmt->execute()) {
            $_SESSION['alert'] = 'Product created successfully!';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Error: Could not create the product.';
            $_SESSION['alert_type'] = 'danger';
        }

        // Redirect to the product read page
        header("Location: product_read.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Create New Product</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Product</li>
                    <li class="breadcrumb-item"><a href="../product/category/category_create.php">Create Product Category</a></li>
                </ol>
            </nav><hr>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
            <h6>Input Product Details</h6>
            <div class="row mb-3">
                <!-- Product Name -->
                <div class="col-md-12 form-floating mb-3">
                    <input type="text" class="form-control" name="product_name" id="product_name" placeholder="Product Name" required>
                    <label for="product_name">Product Name</label>
                </div>
                <!-- Product Category -->
                <div class="col-md-6 form-floating mb-3">
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['CategoryID']) ?>"><?= htmlspecialchars($category['CategoryName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="category_id">Product Category</label>
                </div>
                <!-- Product Image -->
                <div class="col-md-6 form-floating mb-3">
                    <input type="file" class="form-control" name="product_image" id="product_image" accept="image/*" required>
                    <label for="product_image">Product Image</label>
                </div>
                <!-- Product Description -->
                <div class="col-md-12 form-floating mb-3">
                    <input type="text" class="form-control" name="product_desc" id="product_desc" placeholder="Product Description">
                    <label for="product_desc">Product Description</label>
                </div>
            </div>
            
            <button class="btn btn-success w-100 mb-2" type="submit">Create</button>
            <a class="btn btn-secondary w-100 mb-2" href="product_read.php">Cancel</a>
        </form>
    </div>
</body>

</html>
