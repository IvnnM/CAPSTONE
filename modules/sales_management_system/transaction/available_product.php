<?php
// Fetch categories from the database
$query = "SELECT CategoryID, CategoryName FROM ProductCategoryTb";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize category filter
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$filterType = isset($_GET['filter']) ? $_GET['filter'] : null;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;

// Use PDO to fetch data based on the selected category, filter, and search
$sql = "SELECT o.OnhandID, o.OnhandQty, o.RetailPrice, o.PromoPrice, o.MinPromoQty, p.ProductImage, p.ProductName, p.ProductDesc,
        IFNULL(s.TotalSold, 0) AS TotalSold
        FROM OnhandTb o 
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
        JOIN ProductTb p ON i.ProductID = p.ProductID 
        LEFT JOIN (
            SELECT OnhandID, SUM(Quantity) AS TotalSold
            FROM CartRecordTb
            GROUP BY OnhandID
        ) s ON o.OnhandID = s.OnhandID
        WHERE 1=1"; // Start with a always-true condition to simplify subsequent filtering

// Apply category filter
if ($selectedCategory) {
    $sql .= " AND p.CategoryID = :categoryID";
}

// Apply search filter
if ($searchQuery) {
    $sql .= " AND (p.ProductName LIKE :searchQuery OR p.ProductDesc LIKE :searchQuery)";
}

// Apply additional filters based on selected type
if ($filterType === 'bestseller') {
    $sql .= " AND s.TotalSold > 0";
} elseif ($filterType === 'lowestprice') {
    $sql .= " ORDER BY o.RetailPrice ASC";
} elseif ($filterType === 'nostock') {
    $sql .= " AND o.OnhandQty = 0";
} else {
    $sql .= " ORDER BY p.ProductName";
}

$stmt = $conn->prepare($sql);

// Bind parameters
if ($selectedCategory) {
    $stmt->bindParam(':categoryID', $selectedCategory, PDO::PARAM_INT);
}

if ($searchQuery) {
    $searchParam = "%{$searchQuery}%";
    $stmt->bindParam(':searchQuery', $searchParam, PDO::PARAM_STR);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Products List</title>
    
    <style>
        /* Remove underline from links */
        a {
            text-decoration: none !important;
        }
        .category-button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .quantity-input {
            width: 80px;
            text-align: center;
            margin: 0 10px;
        }
        
        .quantity-btn {
            padding: 5px 15px;
            font-size: 18px;
            cursor: pointer;
        }
        
        .modal-product-image {
            max-width: 200px;
            height: auto;
            margin: 0 auto;
            display: block;
        }
        
        .modal-product-details {
            text-align: center;
            margin: 15px 0;
        }
        
        .quantity-control {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .search-input {
            flex-grow: 1;
            margin-right: 10px;
        }
        .clear-search {
            cursor: pointer;
            color: #dc3545;
            display: none;
        }
        .no-results {
            text-align: center;
            color: #6c757d;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container mb-5">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <div class="intro-excerpt">
                        <h1 class="mb-4">Shop now</h1>
                        <div class="d-flex flex-column text-light">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box me-3">
                                    <i class="bi bi-cart-plus fs-1"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Add to Cart</h5>
                                    <p class="mb-0 text-info">Click the cart to view and manage your items.</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box me-3">
                                    <i class="bi bi-list-ul fs-1"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Browse Products</h5>
                                    <p class="mb-0 text-info">Scroll down to explore our full selection.</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="icon-box me-3">
                                    <i class="bi bi-credit-card-2-front fs-1"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Checkout</h5>
                                    <p class="mb-0 text-info">Complete your order when you're ready.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="hero-image">
                        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#cartModal">
                            <img src="../assets/images/cart.WebP" loading="lazy" alt="Shopping Cart" class="img-fluid">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-5">

         <!-- Search Bar -->   
        <div class="search-container">
            <form id="searchForm" method="get" class="d-flex w-100">
                <input type="search" 
                       name="search" 
                       id="productSearch" 
                       class="form-control search-input" 
                       placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>">
                <button type="submit" class="btn btn-primary me-2"  aria-label="Search Products">
                    <i class="fas fa-search"></i>
                </button>
                <?php if ($searchQuery): ?>
                    <a href="?#Products" class="btn btn-danger">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Category Buttons -->        
        <div class="d-flex flex-wrap p-1">
            <div class="dropdown">
                <button class="btn btn-secondary category-button dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php 
                    // Display currently selected category or default text
                    $selectedCategory = 'Select Category';
                    if (isset($_GET['category'])) {
                        foreach ($categories as $category) {
                            if ($_GET['category'] == $category['CategoryID']) {
                                $selectedCategory = htmlspecialchars($category['CategoryName']);
                                break;
                            }
                        }
                    }
                    echo $selectedCategory;
                    ?>
                    <i class="fas fa-shopping-cart"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                    <li><a class="dropdown-item" href="?#Products">All Products</a></li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a class="dropdown-item" href="?category=<?php echo htmlspecialchars($category['CategoryID']); ?>#Products">
                                <?php echo htmlspecialchars($category['CategoryName']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            
            <a href="?filter=bestseller#Products" class="btn btn-warning category-button">Best Seller
            <i class="fas fa-check-circle"></i>
            </a>
            <a href="?filter=lowestprice#Products" class="btn btn-success category-button">Lowest Price
            <i class="fas fa-tag fa-sm"></i>
            </a>
            <a href="?filter=nostock#Products" class="btn btn-danger category-button">Out of Stock
            <i class="fas fa-times-circle"></i>
            </a>
        </div>
        
        
        <!-- Products List -->
        <div class="row product-section">
            <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="no-results">
                        <h3>No products found</h3>
                        <?php 
                        if ($searchQuery) {
                            echo "<p>Your search for '" . htmlspecialchars($searchQuery) . "' did not match any products.</p>";
                        }
                        ?>
                        <a href="?#Products" class="btn btn-primary mt-3">Clear Search</a>
                    </div>
                </div>
            <?php else: ?>
                <?php
                $base_url = '/modules/inventory_management_system/product/';
                foreach ($products as $row):
                    $image_path = $base_url . htmlspecialchars($row['ProductImage']);
                ?>
                <!-- Product Card -->
                <div class="col-12 col-md-4 col-lg-3 mb-5">
                    <div class="product-card">
                        <div class="product-image-container">
                            <?php 
                            $image_full_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
                            if (file_exists($image_full_path) && is_file($image_full_path)): 
                            ?>
                                <img src="<?= $image_path ?>" 
                                    alt="<?= htmlspecialchars($row['ProductName']) ?>" 
                                    class="product-image"
                                    style="max-width: 100%; height: auto; object-fit: contain;"
                                    loading="lazy"
                                    onerror="this.onerror=null; this.src='../assets/images/placeholder.png';">
                            <?php else: ?>
                                <img src="../assets/images/placeholder.png" 
                                    alt="No image available" 
                                    class="product-image no-image"
                                    style="max-width: 100%; height: auto; object-fit: contain;">
                                    
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-content">
                            <h3 class="product-title"><?= htmlspecialchars($row['ProductName']) ?></h3>
                            
                            <div class="price-section">
                                <?php if ($row['PromoPrice'] && $row['PromoPrice'] < $row['RetailPrice']): ?>
                                    <span class="promo-price">
                                        ₱<?= number_format($row['PromoPrice'], 2) ?>
                                    </span>
                                    <span class="original-price">₱<?= number_format($row['RetailPrice'], 2) ?></span>
                                    <?php if ($row['OnhandQty'] > 0): ?>
                                    <a href="../modules/sales_management_system/transaction/cart/add_to_cart.php?onhand_id=<?= htmlspecialchars($row['OnhandID']) ?>&quantity=<?= htmlspecialchars($row['MinPromoQty']) ?>" 
                                    class="min-order">
                                        <i class="fas fa-tag fa-sm"></i> Minimum order: <?= htmlspecialchars($row['MinPromoQty']) ?> units
                                    </a>
                                    <?php else: ?>
                                    <div class="min-order">
                                        <i class="fas fa-tag fa-sm"></i> Minimum order: <?= htmlspecialchars($row['MinPromoQty']) ?> units
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="promo-price">₱<?= number_format($row['RetailPrice'], 2) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="product-description"><?= htmlspecialchars($row['ProductDesc']) ?></p>
                            <a href="#" class="view-details" data-bs-toggle="modal" data-bs-target="#descModal<?= $row['OnhandID'] ?>">
                                <i class="fas fa-info-circle"></i> View full description
                            </a>
    
                            <div class="stock-info">
                                <?php if ($row['OnhandQty'] > 0): ?>
                                    <i class="fas fa-check-circle in-stock"></i>
                                    <span class="in-stock"><?= htmlspecialchars($row['OnhandQty']) ?> units available</span>
                                <?php else: ?>
                                    <i class="fas fa-times-circle out-of-stock"></i>
                                    <span class="out-of-stock">Currently out of stock</span>
                                <?php endif; ?>
                            </div>
    
                            <?php if ($row['OnhandQty'] > 0): ?>
                                <?php if (isset($_SESSION['cust_email'])): ?>
                                    <button type="button" class="cart-button available" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#quantityModal<?= $row['OnhandID'] ?>"
                                            data-onhand-qty="<?= htmlspecialchars($row['OnhandQty']) ?>"
                                            data-min-promo-qty="<?= htmlspecialchars($row['MinPromoQty']) ?>">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                <?php else: ?>
                                    <p class="signup-message">Please sign up to add items to cart</p>
                                    <a href="#Products" class="cart-button unavailable">
                                        <i class="fas fa-lock"></i> Sign Up to Buy
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="cart-button unavailable" disabled>
                                    <i class="fas fa-ban"></i> Out Of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
    
                <!-- Quantity Selection Modal -->
                <div class="modal fade" id="quantityModal<?= $row['OnhandID'] ?>" 
                     tabindex="-1" 
                     aria-hidden="true"
                     data-retail-price="<?= htmlspecialchars($row['RetailPrice']) ?>"
                     data-promo-price="<?= htmlspecialchars($row['PromoPrice']) ?>"
                     data-min-promo-qty="<?= htmlspecialchars($row['MinPromoQty']) ?>">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add to Cart - <?= htmlspecialchars($row['ProductName']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Product Image -->
                                <?php 
                                $image_full_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
                                if (file_exists($image_full_path) && is_file($image_full_path)): 
                                ?>
                                    <img src="<?= $image_path ?>" 
                                        alt="<?= htmlspecialchars($row['ProductName']) ?>" 
                                        class="modal-product-image"
                                        onerror="this.onerror=null; this.src='../assets/images/placeholder.png';">
                                <?php else: ?>
                                    <img src="../assets/images/placeholder.png" 
                                        alt="No image available" 
                                        class="modal-product-image">
                                <?php endif; ?>
                                
                                <!-- Product Details -->
                                <div class="modal-product-details">
                                    <h4><?= htmlspecialchars($row['ProductName']) ?></h4>
                                    <p>Available Stock: <?= htmlspecialchars($row['OnhandQty']) ?> units</p>
                                    
                                    <!-- Dynamic Price Display -->
                                    <div class="price-display"></div>
                                    
                                    <!-- Promotional Message -->
                                    <div class="promo-message text-primary mt-2"></div>
                                </div>
                
                                <!-- Quantity Controls -->
                                <div class="quantity-control">
                                    <button class="btn btn-secondary quantity-btn decrease-qty">-</button>
                                    <input type="number" class="form-control quantity-input" 
                                           value="1" 
                                           min="1" 
                                           max="<?= htmlspecialchars($row['OnhandQty']) ?>"
                                           data-onhand-id="<?= htmlspecialchars($row['OnhandID']) ?>">
                                    <button class="btn btn-secondary quantity-btn increase-qty">+</button>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary add-to-cart-confirm" 
                                        data-onhand-id="<?= htmlspecialchars($row['OnhandID']) ?>">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Product Description Modal -->
                <div class="modal fade" id="descModal<?= $row['OnhandID'] ?>" tabindex="-1" aria-labelledby="descModalLabel<?= $row['OnhandID'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content product-modal">
                            <div class="modal-header">
                                <h5 class="modal-title" id="descModalLabel<?= $row['OnhandID'] ?>">
                                    <?= htmlspecialchars($row['ProductName']) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?= htmlspecialchars($row['ProductDesc']) ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="modal-close-btn" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('productSearch');
            const searchForm = document.getElementById('searchForm');

            // Optional: Add real-time search suggestions (client-side enhancement)
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim();
                
                // Optional: Implement client-side filtering if needed
                // For now, we'll rely on server-side search
                if (searchTerm.length > 2) {
                    // You could add a debounce mechanism here for performance
                    // searchForm.submit();
                }
            });

            // Prevent empty search submissions
            searchForm.addEventListener('submit', function(e) {
                const searchTerm = searchInput.value.trim();
                if (searchTerm === '') {
                    e.preventDefault();
                }
            });
        });
            document.addEventListener('DOMContentLoaded', function() {
            // Handle quantity controls
            document.querySelectorAll('.quantity-control').forEach(control => {
                const input = control.querySelector('.quantity-input');
                const decreaseBtn = control.querySelector('.decrease-qty');
                const increaseBtn = control.querySelector('.increase-qty');
                const modal = control.closest('.modal');
                const priceDisplay = modal.querySelector('.price-display');
                
                // Get price information from data attributes
                const retailPrice = parseFloat(modal.getAttribute('data-retail-price'));
                const promoPrice = parseFloat(modal.getAttribute('data-promo-price'));
                const minPromoQty = parseInt(modal.getAttribute('data-min-promo-qty'));
                
                // Function to update price display based on quantity
                const updatePrice = (quantity) => {
                    const currentPrice = quantity >= minPromoQty ? promoPrice : retailPrice;
                    const formattedPrice = currentPrice.toLocaleString('en-PH', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                    
                    // Update price display with appropriate message
                    if (quantity >= minPromoQty && promoPrice < retailPrice) {
                        priceDisplay.innerHTML = `
                            <span class="text-success">Promo Price: ${formattedPrice}</span><br>
                            <small class="text-muted"><del>Regular Price: ₱${retailPrice.toFixed(2)}</del></small>
                        `;
                    } else {
                        priceDisplay.innerHTML = `Regular Price: ${formattedPrice}`;
                    }
                    
                    // Show or hide promo message
                    const promoMessage = modal.querySelector('.promo-message');
                    if (promoPrice < retailPrice) {
                        if (quantity < minPromoQty) {
                            promoMessage.innerHTML = `Add ${minPromoQty - quantity} more unit(s) to avail promo price of ₱${promoPrice.toFixed(2)}`;
                            promoMessage.style.display = 'block';
                        } else {
                            promoMessage.style.display = 'none';
                        }
                    }
                };
                
                // Initialize price display
                updatePrice(parseInt(input.value));
                
                // Decrease button click handler
                decreaseBtn.addEventListener('click', () => {
                    const currentValue = parseInt(input.value);
                    if (currentValue > 1) {
                        input.value = currentValue - 1;
                        updatePrice(currentValue - 1);
                    }
                });
                
                // Increase button click handler
                increaseBtn.addEventListener('click', () => {
                    const currentValue = parseInt(input.value);
                    const maxValue = parseInt(input.getAttribute('max'));
                    if (currentValue < maxValue) {
                        input.value = currentValue + 1;
                        updatePrice(currentValue + 1);
                    }
                });
                
                // Direct input change handler
                input.addEventListener('change', () => {
                    let value = parseInt(input.value);
                    const max = parseInt(input.getAttribute('max'));
                    
                    if (isNaN(value) || value < 1) {
                        value = 1;
                    } else if (value > max) {
                        value = max;
                    }
                    
                    input.value = value;
                    updatePrice(value);
                });
            });
            
            // Handle Add to Cart button clicks
            document.querySelectorAll('.add-to-cart-confirm').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    const quantityInput = modal.querySelector('.quantity-input');
                    const onhandId = this.getAttribute('data-onhand-id');
                    const quantity = quantityInput.value;
                    
                    // Redirect to add_to_cart.php with the selected quantity
                    window.location.href = `../modules/sales_management_system/transaction/cart/add_to_cart.php?onhand_id=${onhandId}&quantity=${quantity}`;
                });
            });
        });
        
        document.addEventListener('DOMContentLoaded', function() {
    // Get all category buttons
    const categoryButtons = document.querySelectorAll('.category-button');
    
    // Function to handle button selection
    function handleButtonSelection(event) {
        // Remove 'active' class from all buttons
        categoryButtons.forEach(button => {
            button.classList.remove('active');
        });
        
        // Add 'active' class to the clicked button
        event.currentTarget.classList.add('active');
        
        // Optional: Store the selected button in localStorage
        localStorage.setItem('selectedCategoryButton', event.currentTarget.getAttribute('href'));
    }
    
    // Add click event listener to each button
    categoryButtons.forEach(button => {
        button.addEventListener('click', handleButtonSelection);
    });
    
    // Optional: Restore selected button on page load
    const storedSelectedButton = localStorage.getItem('selectedCategoryButton');
    if (storedSelectedButton) {
        const buttonToSelect = document.querySelector(`.category-button[href="${storedSelectedButton}"]`);
        if (buttonToSelect) {
            buttonToSelect.classList.add('active');
        }
    }
});
    </script>
</body>
</html>
