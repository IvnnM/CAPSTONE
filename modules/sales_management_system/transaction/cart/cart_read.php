<?php

// Fetch cart items for the current customer
$query = "SELECT c.CartID, p.ProductName, c.Quantity, c.AddedDate, o.RetailPrice, o.MinPromoQty, o.PromoPrice
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
    // Determine the applicable price
    $price_to_use = $item['Quantity'] >= $item['MinPromoQty'] ? $item['PromoPrice'] : $item['RetailPrice'];
    $total_price += $price_to_use * $item['Quantity'];
}

// Calculate delivery fee if location_id is set
$delivery_fee = 0;
if (isset($_SESSION['location_id'])) {
    // Call the delivery fee calculation
    include("calculate_delivery_fee.php"); 
    $delivery_fee = isset($_SESSION['delivery_fee']) ? $_SESSION['delivery_fee'] : 0;
}

// Calculate grand total
$grand_total = $total_price + $delivery_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container pt-1">
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
                        <?php 
                        // Determine the applicable price
                        $price_to_use = $item['Quantity'] >= $item['MinPromoQty'] ? $item['PromoPrice'] : $item['RetailPrice'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['ProductName']) ?></td>
                            <td><?= htmlspecialchars($item['Quantity']) ?></td>
                            <td><?= htmlspecialchars($item['AddedDate']) ?></td>
                            <td><?= number_format($price_to_use, 2) ?></td>
                            <td><?= number_format($price_to_use * $item['Quantity'], 2) ?></td> <!-- Total price for each item -->
                            <td>
                                <a href="../modules/sales_management_system/transaction/cart/update_cart.php?cart_id=<?= htmlspecialchars($item['CartID']) ?>" class="btn btn-primary btn-sm w-100 mb-1">Update</a>
                                <a href="../modules/sales_management_system/transaction/cart/remove_item.php?cart_id=<?= htmlspecialchars($item['CartID']) ?>" class="btn btn-danger btn-sm w-100">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total Price:</strong></td>
                        <td><strong><?= number_format($total_price, 2) ?></strong></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Delivery Fee:</strong></td>
                        <td><strong><?= number_format($delivery_fee, 2) ?></strong></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                        <td><strong><?= number_format($grand_total, 2) ?></strong></td>
                        <td>
                            <a href="../modules/sales_management_system/transaction/cart/checkout.php" class="btn btn-success w-100">Proceed to Payment</a>
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
