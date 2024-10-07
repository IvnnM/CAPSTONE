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
            echo "<script>alert('Product Category Created Successfully');</script>";
            echo"<script>window.history.back();</script>";
            exit;
        } else {
            // Error alert
            echo "<script>alert('Error: Could not create product category');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product Category</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1 class="mb-4">Create Product Category</h1>

    <form method="POST" action="">
    <hr style="border-top: 1px solid white;">
    <h6>Create New Category</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="category_name">Category Name:</label>
                <input type="text" class="form-control" name="category_name" required>
            </div>
        </div>
        <button class="btn btn-success" type="submit">Create</button>
    </form>

    <br>
    <a href="category_read.php">Back to Category List</a>
</body>
</html>
