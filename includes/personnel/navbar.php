<style>
/* CSS Variables for Color Scheme */
:root {
    --primary-color: #003366;
    --secondary-color: #f4f4f4;
    --text-color: #333;
    --white: #ffffff;
    --danger-color: #dc3545;
    --hover-background: #e9ecef;
}

/* Base Navigation Bar Styles */
.navbar-bottom {
    width: 100%;
    background-color: var(--primary-color);
    z-index: 1050;
}

/* Navigation Icons */
.nav-icon {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: var(--white);
    opacity: 0.7;
    transition: all 0.3s ease;
    cursor: pointer;
}

.nav-icon:hover,
.nav-icon.active {
    opacity: 1;
    transform: scale(1.1);
}

.nav-icon i {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.nav-icon span {
    font-size: 0.75rem;
}

/* Quick Access Modal Base Styles */
.quick-access-modal .modal-dialog {
    max-width: 90%;
    width: 400px;
    margin: 1.75rem auto;
    position: absolute;
    bottom: 80px;
    right: 20px;
}

.quick-access-modal .modal-content {
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: none;
    background-color: var(--white);
}

/* Modal Header Styles */
.quick-access-modal .modal-header {
    background-color: var(--primary-color);
    color: var(--white);
    padding: 15px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

.quick-access-modal .modal-header .modal-title {
    font-weight: 600;
}

.quick-access-modal .modal-body {
    padding: 0;
}

/* Modal List Group Styles */
.quick-access-modal .list-group {
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
    overflow: hidden;
}

.quick-access-modal .list-group-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border-color: var(--hover-background);
    transition: all 0.3s ease;
}

.quick-access-modal .list-group-item:hover {
    background-color: var(--secondary-color);
    transform: translateX(5px);
}

.quick-access-modal .list-group-item i {
    margin-right: 12px;
    color: var(--primary-color);
    font-size: 1.2rem;
}

.quick-access-modal .list-group-item.text-danger:hover {
    background-color: #fff0f0;
}

</style>
   <?php
    // Get the current page's filename
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Define the pages where this code should NOT run
    $excluded_pages = ['index.php'];
    
    // Only run the code if we're NOT on the excluded pages
    if (!in_array($current_page, $excluded_pages)) {
    
        // Check if there are any pending transactions
        $pending_query = "SELECT COUNT(*) as pending_count FROM TransacTb WHERE Status = 'Pending'";
        $pending_stmt = $conn->prepare($pending_query);
        $pending_stmt->execute();
        $pending_count = $pending_stmt->fetch(PDO::FETCH_ASSOC)['pending_count'];
    
        // Check if there are any transactions with 'ToShip' status
        $toship_query = "SELECT COUNT(*) as toship_count FROM TransacTb WHERE Status = 'ToShip'";
        $toship_stmt = $conn->prepare($toship_query);
        $toship_stmt->execute();
        $toship_count = $toship_stmt->fetch(PDO::FETCH_ASSOC)['toship_count'];
    
        // Determine if we need to show the red circle
        $has_alert = ($pending_count > 0 || $toship_count > 0);
    }
    ?>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-bottom sticky-top">
        <div class="container-fluid">
            <div class="row w-100 text-center">
                <div class="col nav-icon active" data-nav="home" data-href="#Overview" data-full-href="/views/personnel_view.php#Overview">
                    <i class="bi bi-house"></i>
                    <span>Home</span>
                </div>
                <div class="col nav-icon" data-nav="product" data-modal="productModal">
                    <i class="bi bi-box-seam"></i>
                    <span>Product</span>
                </div>
                <div class="col nav-icon" data-nav="gis" data-modal="gisModal">
                    <i class="bi bi-geo-alt"></i>
                    <span>GIS</span>
                </div>
                <div class="col nav-icon" data-nav="shop" data-modal="shopModal">
                    <i class="bi bi-shop"></i>
                    <span>Shop</span>
                </div>
                <div class="col nav-icon" data-nav="account" data-modal="accountModal">
                    <i class="bi bi-person"></i>
                    <span>Account</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Modals -->
    <div class="modal fade quick-access-modal" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-box-seam me-2"></i>Product Options
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a href="/modules/inventory_management_system/product/category/category_read.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-tags"></i>Product Category
                        </a>
                        <a href="/modules/inventory_management_system/product/product_read.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-box-seam"></i>Manage Product
                        </a>
                        <a href="/modules/inventory_management_system/inventory/inventory_read.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-archive"></i>Manage Inventory
                        </a>
                        <a href="/modules/sales_management_system/onhand/onhand_read.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-truck"></i>Manage Onhand Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade quick-access-modal" id="gisModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-geo-alt me-2"></i>GIS Options
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a href="/predictive/index.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-map"></i>Site Selection
                        </a>
                        <a href="/modules/geographic_information_system/route_optimization/route.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-pin-map"></i>Route Optimization
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade quick-access-modal" id="shopModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-shop me-2"></i>Shop Options
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        
                        <a href="/modules/sales_management_system/transaction/personnel/transac_read_pending.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-list"></i> To Review Order
                            <?php if ($pending_count > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $pending_count ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <a href="/modules/sales_management_system/transaction/personnel/transac_read_approved.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-truck"></i> To Ship Order
                            <?php if ($toship_count > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $toship_count ?></span>
                            <?php endif; ?>
                        </a>

                        <a href="/modules/sales_management_system/transaction/personnel/transac_read_delivered.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-check-circle"></i>Complete Transactions
                        </a>
                        <a href="/modules/sales_management_system/store/store_read.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-shop"></i>Manage Store
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade quick-access-modal" id="accountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person me-2"></i>Account Options
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a class="list-group-item list-group-item-action" 
                           onclick="location.href='<?php echo isset($_SESSION['AdminID']) ? 
                               '/modules/inventory_management_system/user_management/admin/admin_update.php' : 
                               '/modules/inventory_management_system/user_management/employee/employee_update.php'; ?>';" style="cursor: pointer;">
                            <i class="bi bi-person-circle"></i>My Profile
                        </a>

                        <?php if (isset($_SESSION['AdminID'])): ?>
                        <a href="/modules/inventory_management_system/user_management/employee/employee_read.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-people"></i>Manage Employee Accounts
                        </a>
                        <?php endif; ?>
                        <a href="/logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="bi bi-box-arrow-right"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    
    <!-- Updated JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navIcons = document.querySelectorAll('.nav-icon');

            // Function to update active state based on current hash
            function updateActiveNavIcon() {
                const currentHash = window.location.hash || '#Overview'; // Default to #Overview
                
                navIcons.forEach(icon => {
                    const href = icon.getAttribute('data-href');
                    
                    if (href === currentHash) {
                        // Remove active class from all icons
                        navIcons.forEach(item => item.classList.remove('active'));
                        // Add active class to matching icon
                        icon.classList.add('active');
                    }
                });
            }

            // Initial update of active state
            updateActiveNavIcon();

            // Update active state on hash change
            window.addEventListener('hashchange', updateActiveNavIcon);

            // Add click event listeners for navigation
            navIcons.forEach(icon => {
                icon.addEventListener('click', (event) => {
                    // Check if the icon has a modal
                    const modalId = icon.getAttribute('data-modal');
                    const href = icon.getAttribute('data-href');
                    const fullHref = icon.getAttribute('data-full-href');

                    if (modalId) {
                        // If modal exists, show the modal
                        const modal = new bootstrap.Modal(document.getElementById(modalId));
                        modal.show();
                        event.preventDefault();
                    } else if (href && fullHref) {
                        // Mimic the original onclick behavior
                        window.location.href = fullHref;
                        return false;
                    } else {
                        // Add your default navigation logic here
                        const navTarget = icon.getAttribute('data-nav');
                        console.log(`Navigating to: ${navTarget}`);
                    }
                });
            });
        });
    </script>


    
