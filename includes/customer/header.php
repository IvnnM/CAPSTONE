<?php
$alert = isset($_SESSION['alert']) ? $_SESSION['alert'] : '';
$alert_type = isset($_SESSION['alert_type']) ? $_SESSION['alert_type'] : '';

// Clear alert after displaying
unset($_SESSION['alert']);
unset($_SESSION['alert_type']);
?>

<!-- Display alert if available -->
<?php if ($alert): ?>
  <div id="alert-message" class="alert alert-<?= htmlspecialchars($alert_type) ?> position-fixed top-0 start-50 translate-middle-x w-25 text-center py-2 px-3 small" role="alert" style="z-index: 1050;">
      <?= htmlspecialchars_decode($alert) ?>  <!-- Decode HTML entities so the <a> tag is rendered -->
  </div>
<?php endif; ?>
<style>
       .dropdown-toggle::after {
    display: none;
}
</style>
<!-- HEADER -->
<nav class="custom-navbar navbar navbar-expand-md navbar-dark fixed-top" aria-label="Furni navigation bar">
    <div class="container">
        <a class="navbar-brand" href="../index.php">DKAT Store<span>.</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsFurni" aria-controls="navbarsFurni" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsFurni">
            <ul class="custom-navbar-nav navbar-nav ms-auto mb-2 mb-md-0 align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="#Overview">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#Products">Shop</a>
                </li>
                <?php if (isset($_SESSION['cust_name']) && isset($_SESSION['cust_email'])): ?>
                    <a class='nav-link view-cart-link' href="#Profile">My Cart 
                        <?php 
                        // Count cart items for the current customer
                        $cart_count_query = "SELECT COUNT(*) as cart_count FROM CartTb WHERE CustEmail = :cust_email";
                        $cart_count_stmt = $conn->prepare($cart_count_query);
                        $cart_count_stmt->execute(['cust_email' => $cust_email]);
                        $cart_count = $cart_count_stmt->fetch(PDO::FETCH_ASSOC)['cart_count'];
                        
                        // Display cart count if greater than 0
                        if ($cart_count > 0): ?>
                            <span class="badge bg-danger ms-1"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <!--<li class="nav-item">-->
                    <!--    <a class="nav-link" href="#" onclick="window.location.href='../index.php';">Personnel</a>-->
                    <!--</li>-->
                <?php endif; ?>
            </ul>
            <ul class="custom-navbar-cta navbar-nav ms-3 mb-2 mb-md-0">
                <?php if (isset($_SESSION['cust_name']) && isset($_SESSION['cust_email'])): ?>
                    <!--<li class="nav-item">-->
                    <!--    <a href='#Profile' class='nav-link view-cart-link'><i class="bi bi-cart fs-1"></i></a>-->
                    <!--</li>-->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle profile-link" href="#Profile" id="navbarDropdownProfile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-1"></i> 
                            <?//= htmlspecialchars($_SESSION['cust_name']) ?> 
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownProfile">
                            <li><a class="dropdown-item" href="../modules/sales_management_system/transaction/customer/order.php">My Orders</a></li>
                            <li><a class="dropdown-item" href="../modules/inventory_management_system/user_management/customer/customer_update.php">Manage Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                     </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-nav" href="../customer_login/login_form.php">Sign In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        const currentHash = window.location.hash; // Get the current hash (e.g., #Products)
    
        // Loop through nav links and update the active class
        navLinks.forEach(link => {
            // Remove existing active class
            link.classList.remove('active');
    
            // Add active class to the link that matches the current hash
            if (link.getAttribute('href') === currentHash) {
                link.classList.add('active');
            }
        });
    });

    $(document).ready(function() {
        // Check if the alert message exists
        var alert = $('#alert-message');
        if (alert.length) {
            // Set a timeout to fade out the alert after 5 seconds (5000 milliseconds)
            setTimeout(function() {
                alert.fadeOut(1000); // Fades out over 1 second
            }, 3000);
        }
    });
</script>