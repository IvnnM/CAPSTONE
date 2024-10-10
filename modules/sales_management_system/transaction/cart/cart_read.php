<?php

// Fetch cart items for the current customer
$query = "SELECT c.CartID, p.ProductName, c.Quantity, c.AddedDate, o.RetailPrice
          FROM CartTb c
          JOIN OnhandTb o ON c.OnhandID = o.OnhandID
          JOIN InventoryTb i ON o.InventoryID = i.InventoryID
          JOIN ProductTb p ON i.ProductID = p.ProductID
          WHERE c.CustEmail = :cust_email";
$stmt = $conn->prepare($query);
$stmt->execute(['cust_email' => $cust_email]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total price
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['RetailPrice'] * $item['Quantity'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../../../../includes/cdn.php"> <!-- Adjust as necessary -->
</head>
<body>
    <div class="container">
        <h2>Your Cart</h2>
        <?php if (count($cart_items) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Added Date</th>
                        <th>Price</th>
                        <th>Total Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['ProductName']) ?></td>
                            <td><?= htmlspecialchars($item['Quantity']) ?></td>
                            <td><?= htmlspecialchars($item['AddedDate']) ?></td>
                            <td><?= number_format($item['RetailPrice'], 2) ?></td>
                            <td><?= number_format($item['RetailPrice'] * $item['Quantity'], 2) ?></td> <!-- Total price for each item -->
                            <td>
                                <a href="../modules/sales_management_system/transaction/cart/remove_item.php?cart_id=<?= htmlspecialchars($item['CartID']) ?>" class="btn btn-danger">Remove</a>
                                <a href="../modules/sales_management_system/transaction/cart/update_cart.php?cart_id=<?= htmlspecialchars($item['CartID']) ?>" class="btn btn-primary">Update</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total Price:</strong></td>
                        <td><strong><?= number_format($total_price, 2) ?></strong></td>
                        <td>
                            <a href="../modules/sales_management_system/transaction/cart/checkout.php" class="btn btn-success">Checkout</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-warning">Your cart is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>
