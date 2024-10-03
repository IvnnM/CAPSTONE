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
        echo "<script>alert('Product not found.'); window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid product ID.'); window.history.back();</script>";
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
            echo "<script>alert('Error: Could not upload the image.');</script>";
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
        echo "<script>alert('Product updated successfully!');</script>";
        echo "<script>window.history.back();</script>";
    } else {
        echo "<script>alert('Error: Could not update the product.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Product</title>
</head>
<body>
    <h3>Update Product</h3>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="product_name">Product Name:</label>
        <input type="text" name="product_name" value="<?= htmlspecialchars($product['ProductName']) ?>" required><br>

        <label for="product_desc">Product Description:</label>
        <input type="text" name="product_desc" value="<?= htmlspecialchars($product['ProductDesc']) ?>"><br>

        <label for="category_id">Product Category:</label>
        <select name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['CategoryID']) ?>" <?= $category['CategoryID'] == $product['CategoryID'] ? 'selected' : '' ?>><?= htmlspecialchars($category['CategoryName']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="product_image">Product Image:</label>
        <input type="file" name="product_image" accept="image/*"><br>
        <small>Leave blank if you do not want to change the image.</small><br>

        <button type="submit">Update</button>
    </form>
    <br>
    <a href="product_read.php">Back to Product List</a>
</body>
</html>
