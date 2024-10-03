<?php
session_start();
include("../../../../includes/cdn.php"); 
include("../../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Determine the search value, either from URL or form submission
$search_value = '';
if (isset($_GET['search_value']) && !empty($_GET['search_value'])) {
    $search_value = trim($_GET['search_value']);
}

// Prepare the query to fetch categories with an optional search filter
$category_query = "SELECT * FROM ProductCategoryTb";

if (!empty($search_value)) {
    $category_query .= " WHERE CategoryName LIKE :search_value";
}

$stmt = $conn->prepare($category_query);

if (!empty($search_value)) {
    $search_param = '%' . $search_value . '%'; // Wildcard search for partial matches
    $stmt->bindParam(':search_value', $search_param);
}

$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Category List</title>
    <link rel="stylesheet" href="path-to-bootstrap.css"> <!-- Add bootstrap link if needed -->
    <style>
        .container {
            margin-top: 30px;
        }
        .table {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Product Categories</h3>
        <form method="GET" action="">
            <div class="form-group">
                <label for="search_value">Search by Category Name:</label>
                <input type="text" name="search_value" id="search_value" class="form-control" 
                       value="<?= htmlspecialchars($search_value) ?>">
            </div>
            <button type="submit" class="btn btn-primary mt-2">Search</button>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Category ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($stmt->rowCount() > 0): ?>
                    <?php while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['CategoryID']) ?></td>
                            <td><?= htmlspecialchars($category['CategoryName']) ?></td>
                            <td>
                                <a href="category_update.php?id=<?= htmlspecialchars($category['CategoryID']) ?>" class="btn btn-warning btn-sm">Update</a> | 
                                <a href="category_delete.php?id=<?= htmlspecialchars($category['CategoryID']) ?>" onclick="return confirm('Are you sure you want to delete this category?');" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No categories found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a href="category_create.php" class="btn btn-success">Add New Category</a>
    </div>
</body>
</html>
