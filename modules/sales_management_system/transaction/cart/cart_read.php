<?php

// Fetch cart items for the current customer, including OnhandQty
$query = "SELECT 
            c.CartID, 
            p.ProductName, 
            p.ProductDesc,
            c.Quantity, 
            c.AddedDate, 
            o.RetailPrice, 
            o.MinPromoQty, 
            o.PromoPrice, 
            o.OnhandQty
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Your Cart</title>
    <style>
        /* Add a red background for items with insufficient stock */
        .insufficient-qty {
            background-color: #f8d7da;
        }
        
    </style>
</head>
<body>
    <!--<div class="hero">-->
    <!--    <div class="container mb-5">-->
    <!--        <div class="row justify-content-between">-->
    <!--            <div class="col-lg-5">-->
    <!--                <div class="intro-excerpt">-->
    <!--                    <h1>Cart <span><h5><a class="text-white" href="../views/customer_view.php#Products">Continue Buying >></a></h5></span></h1>-->
                        
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="col-lg-7">-->
    <!--                <div class="hero-image">-->
    <!--                <a class="nav-link" href="#Products"><img src="../assets/images/cart.png"></a>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->
    <div class="container cart-section">
        <?php if (count($cart_items) > 0): ?>
            <div class="cart-header">
                <h2>Shopping Cart</h2>
                <p class="items-count"><?= count($cart_items) ?> items in your cart</p>
            </div>
            <div class="cart-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="product-col">Product Details</th>
                                <th class="qty-col">Quantity</th>
                                <th class="stock-col">Stock Status</th>
                                <th class="price-col">Unit Price</th>
                                <th class="total-col">Total</th>
                                <th class="actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <?php 
                                $price_to_use = $item['Quantity'] >= $item['MinPromoQty'] ? $item['PromoPrice'] : $item['RetailPrice'];
                                $stock_status = $item['Quantity'] > $item['OnhandQty'] ? 'insufficient' : 'in-stock';
                                $row_class = $item['Quantity'] > $item['OnhandQty'] ? 'insufficient-qty' : '';
                                ?>
                                <tr class="cart-item <?= $row_class ?>">
                                    <td class="product-info" data-label="Product">
                                        <div class="product-name"><?= htmlspecialchars($item['ProductName']) ?></div>
                                    </td>
                                    <td class="quantity-cell" data-label="Quantity">
                                        <span class="quantity-badge"><?= htmlspecialchars($item['Quantity']) ?></span>
                                    </td>
                                    <td class="stock-cell" data-label="Stock">
                                        <span class="stock-badge <?= $stock_status ?>">
                                            <?= $stock_status === 'in-stock' ? 'In Stock' : 'Low Stock' ?>
                                            (<?= htmlspecialchars($item['OnhandQty']) ?>)
                                        </span>
                                    </td>
                                    <td class="price-cell" data-label="Price">
                                        ₱<?= number_format($price_to_use, 2) ?>
                                    </td>
                                    <td class="total-cell" data-label="Total">
                                        ₱<?= number_format($price_to_use * $item['Quantity'], 2) ?>
                                    </td>
                                    <td class="actions-cell" data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="update-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#updateQuantityModal"
                                                    data-cart-id="<?= htmlspecialchars($item['CartID']) ?>" 
                                                    data-current-quantity="<?= htmlspecialchars($item['Quantity']) ?>"
                                                    data-product-name="<?= htmlspecialchars($item['ProductName']) ?>"
                                                    data-product-description="<?= htmlspecialchars($item['ProductDesc'] ?? 'No description available') ?>"
                                                    data-product-price="<?= number_format($price_to_use, 2) ?>"
                                                    data-available-stock="<?= htmlspecialchars($item['OnhandQty']) ?>">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
                                            <a href="../modules/sales_management_system/transaction/cart/remove_item.php?cart_id=<?= htmlspecialchars($item['CartID']) ?>" 
                                               class="remove-btn">
                                                <i class="fas fa-trash"></i> Remove
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div><hr>
                
                <div class="cart-summary">
                    <div class="summary-content">
                        <div class="summary-row">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value">₱<?= number_format($total_price, 2) ?></span>
                        </div>
                        <!--<div class="summary-row total">-->
                        <!--    <span class="summary-label">Total:</span>-->
                        <!--    <span class="summary-value">₱<?= number_format($total_price, 2) ?></span>-->
                        <!--</div>-->
                        <a href="../modules/sales_management_system/transaction/payment/checkout_view.php" 
                           class="checkout-btn">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <!--<a href="../views/customer_view.php#Products" class="continue-shopping">Continue Shopping</a>-->
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
