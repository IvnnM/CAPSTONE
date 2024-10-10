<?php
session_start();
include('../../../../config/database.php'); // Adjust path as necessary

$cust_email = $_SESSION['cust_email'];

// Check if cart_id is provided
if (!isset($_GET['cart_id'])) {
    echo "<p class='text-danger'>No cart item specified.</p>";
    exit();
}

$cart_id = $_GET['cart_id'];

// Fetch the current quantity for the cart item
$query = "SELECT c.Quantity, p.ProductName 
          FROM CartTb c 
          JOIN OnhandTb o ON c.OnhandID = o.OnhandID 
          JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
          JOIN ProductTb p ON i.ProductID = p.ProductID 
          WHERE c.CartID = :cart_id AND c.CustEmail = :cust_email";
$stmt = $conn->prepare($query);
$stmt->execute(['cart_id' => $cart_id, 'cust_email' => $cust_email]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "<p class='text-danger'>Cart item not found.</p>";
    exit();
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_quantity = (int)$_POST['quantity'];

    // Validate new quantity
    if ($new_quantity < 1) {
        echo "<p class='text-danger'>Quantity must be at least 1.</p>";
    } else {
        // Update the cart item in the database
        $update_query = "UPDATE CartTb SET Quantity = :quantity WHERE CartID = :cart_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute(['quantity' => $new_quantity, 'cart_id' => $cart_id]);

        // Redirect back to cart_read.php after updating
        header("Location: ../../../../views/customer_view.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Quantity</title>
    <link rel="stylesheet" href="../../../../includes/cdn.php"> <!-- Adjust as necessary -->
</head>
<body>
    <div class="container">
        <h2>Update Quantity for <?= htmlspecialchars($item['ProductName']) ?></h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="<?= htmlspecialchars($item['Quantity']) ?>" min="1" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="../../../../views/customer_view.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
