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

// // Calculate delivery fee if location_id is set
// $delivery_fee = 0;
// if (isset($_SESSION['location_id'])) {
//     // Call the delivery fee calculation
//     include("calculate_delivery_fee.php"); 
//     $delivery_fee = isset($_SESSION['delivery_fee']) ? $_SESSION['delivery_fee'] : 0;
// }

// // Calculate grand total
// $grand_total = $total_price + $delivery_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
</head>
<body>
    <div class="container pt-3">
        <h2>Your Cart</h2>
        <?php if (count($cart_items) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
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
                                <td><?= number_format($price_to_use, 2) ?></td>
                                <td><?= number_format($price_to_use * $item['Quantity'], 2) ?></td> <!-- Total price for each item -->
                                <td>
                                    <button class="btn btn-primary btn-sm w-100 mb-1" data-bs-toggle="modal" data-bs-target="#updateQuantityModal"
                                            data-cart-id="<?= htmlspecialchars($item['CartID']) ?>" 
                                            data-current-quantity="<?= htmlspecialchars($item['Quantity']) ?>">
                                        Update
                                    </button>
                                    <a href="../modules/sales_management_system/transaction/cart/remove_item.php?cart_id=<?= htmlspecialchars($item['CartID']) ?>" class="btn btn-danger btn-sm w-100">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Price:</strong></td>
                            <td><strong><?= number_format($total_price, 2) ?></strong></td>
                            <td colspan="3" class="text-end"><a href="../modules/sales_management_system/transaction/cart/checkout_view.php" class="btn btn-success w-100">Proceed to Payment</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-warning">Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <!-- Update Quantity Modal -->
    <div class="modal fade" id="updateQuantityModal" tabindex="-1" aria-labelledby="updateQuantityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateQuantityModalLabel">Update Quantity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateQuantityForm" method="POST" action="../modules/sales_management_system/transaction/cart/update_cart.php">
                        <input type="hidden" name="cart_id" id="cart_id" value="">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" id="quantity" name="quantity" min="1" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to handle modal data population
        const updateQuantityModal = document.getElementById('updateQuantityModal');
        updateQuantityModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const cartId = button.getAttribute('data-cart-id');
            const currentQuantity = button.getAttribute('data-current-quantity');

            // Update the modal's content
            const modalTitle = updateQuantityModal.querySelector('.modal-title');
            const quantityInput = updateQuantityModal.querySelector('#quantity');
            const cartIdInput = updateQuantityModal.querySelector('#cart_id');

            modalTitle.textContent = 'Update Quantity for Cart ID: ' + cartId; // Optional: Change title
            quantityInput.value = currentQuantity; // Set the current quantity
            cartIdInput.value = cartId; // Set the cart ID for form submission
        });
    </script>

</body>
</html>
