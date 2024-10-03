<?php
session_start();
include("../../../../includes/cdn.php"); 
include("../../../../config/database.php");

// Check if the admin is logged in and has an admin ID in the session
if (!isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Determine the search value, either from URL or form submission
$search_value = '';
if (isset($_GET['search_value']) && !empty($_GET['search_value'])) {
    $search_value = trim($_GET['search_value']);
}

// Initialize the employees array
$employees = [];

// Fetch the employees only if there is a search value
if (!empty($search_value)) {
    $sql = "SELECT * FROM EmpTb WHERE EmpName LIKE :search_value OR EmpEmail LIKE :search_value";
    $stmt = $conn->prepare($sql);
    $search_param = '%' . $search_value . '%'; // Wildcard search for partial matches
    $stmt->bindParam(':search_value', $search_param);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fetch all employees if no search value is provided
    $sql = "SELECT * FROM EmpTb";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee List</title>
    <link rel="stylesheet" href="path-to-bootstrap.css"> <!-- Add bootstrap link if needed -->
    <style>
        /* Add some basic styling */
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
        <h3>Employee List</h3>
        <form method="GET" action="">
            <div class="form-group">
                <label for="search_value">Search by Employee Name or Email:</label>
                <input type="text" name="search_value" id="search_value" class="form-control" 
                       value="<?= htmlspecialchars($search_value) ?>">
            </div>
            <button type="submit" class="btn btn-primary mt-2">Search</button>
        </form>

        <?php if (!empty($employees)): ?>
            <h4 class="mt-4">Employee Records</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Location ID</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['EmpID']) ?></td>
                            <td><?= htmlspecialchars($employee['EmpName']) ?></td>
                            <td><?= htmlspecialchars($employee['LocationID']) ?></td>
                            <td><?= htmlspecialchars($employee['EmpEmail']) ?></td>
                            <td>
                                <a href="employee_delete.php?id=<?= htmlspecialchars($employee['EmpID']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="mt-4">No employees found.</p>
        <?php endif; ?>
        <br>
        <a href="employee_create.php" class="btn btn-success">Add New Employee</a>
    </div>
</body>
</html>
