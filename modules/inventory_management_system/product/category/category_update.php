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

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $category_id = $_GET['id'];

    // Fetch the existing category details
    $query = "SELECT * FROM ProductCategoryTb WHERE CategoryID = :category_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();

    // Check if the category exists
    if ($stmt->rowCount() > 0) {
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "<script>alert('Category not found.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid category ID.');</script>";
    echo "<script>window.history.back();</script>";
    exit;
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_category_name = $_POST['category_name'];

    // Update the category in the database
    $update_query = "UPDATE ProductCategoryTb SET CategoryName = :category_name WHERE CategoryID = :category_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':category_name', $new_category_name);
    $update_stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        // Success alert
        echo "<script>alert('Category updated successfully!');</script>";
        echo "<script>window.history.back();</script>";
    } else {
        // Error alert
        echo "<script>alert('Error: Could not update the category.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product Category</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h3>Update Product Category</h3>
    <form method="POST" action="">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['CategoryName']); ?>" required><br>
        <button type="submit">Update</button>
    </form>
    <br>
    <a href="category_read.php">Back to Category List</a>
</body>
</html>
