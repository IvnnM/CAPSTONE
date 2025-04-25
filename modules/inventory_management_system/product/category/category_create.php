<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the category name from the form
    $category_name = $_POST['category_name'];

    // Check if the category name is not empty
    if (!empty($category_name)) {
        // Prepare and execute the insert query using PDO
        $query = "INSERT INTO ProductCategoryTb (CategoryName) VALUES (:category_name)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category_name', $category_name);

        if ($stmt->execute()) {
            // Success alert
            $_SESSION['alert'] = 'Product Category Created Successfully';
            $_SESSION['alert_type'] = 'success';
        } else {
            // Error alert
            $_SESSION['alert'] = 'Error: Could not create product category';
            $_SESSION['alert_type'] = 'danger';
        }
    } else {
        // Empty category name alert
        $_SESSION['alert'] = 'Category name cannot be empty.';
        $_SESSION['alert_type'] = 'danger';
    }

    header("Location: category_read.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product Category</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Create Product Category</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Product Category</li>
                    <li class="breadcrumb-item"><a href="../product_create.php">Create Product</a></li>
                </ol>
            </nav><hr>
        </div>
        <form method="POST" action="">
            <h6>Input Category</h6>

            <div class="form-floating">
                <input type="text" class="form-control" name="category_name" placeholder="Category" required>
                <label for="category_name">Category</label>
            </div>
            <br>
 
            <button class="btn btn-success w-100 mb-2" type="submit">Create</button>
            <a class="btn btn-secondary w-100 mb-2" href="category_read.php">Cancel</a>
        </form>
    </div>
</body>
</html>
