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
            echo "<script>alert('Error: Could not upload the image.');</script>";
            $product_image = null; // Reset in case of failure
        }
    } else {
        echo "<script>alert('Error: No image uploaded or an upload error occurred.');</script>";
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
            echo "<script>alert('Product created successfully!');</script>";
            echo"<script>window.history.back();</script>";
            exit;
        } else {
            echo "<script>alert('Error: Could not create the product.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1 class="mb-4">Product Form</h1>
    

    <form method="POST" action="" enctype="multipart/form-data">
        <hr style="border-top: 1px solid white;">
        <h6>Create New Product</h6>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="product_name">Product Name:</label>
                <input type="text" class="form-control" name="product_name" required>
            </div>
            <div class="col-md-6">
                <label for="category_id">Product Category:</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['CategoryID']) ?>"><?= htmlspecialchars($category['CategoryName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="product_image">Product Image:</label>
                <input type="file" class="form-control" name="product_image" accept="image/*" required>
            </div>
            <div class="col-md-12">
                <label for="product_desc">Product Description:</label>
                <input type="text" class="form-control" name="product_desc">
            </div>
        </div>
        
        <button class="btn btn-success" type="submit">Create</button>
    </form>

    <br>
    <a href="product_read.php">Back to Product List</a>
</body>
</html>
