<?php
session_start();
include("../includes/cdn.html");
include("../config/database.php");

// Get session variables
$customerId = $_SESSION['customer_id'] ?? null;
$cust_name = $_SESSION['cust_name'] ?? 'Guest';
$cust_email = $_SESSION['cust_email'] ?? null;

// Ensure cust_num is available
if (!isset($_SESSION['cust_num'])) {
    try {
        if ($customerId) {
            // Fetch the customer's mobile number
            $stmt = $conn->prepare("SELECT ContactNumber FROM customerTb WHERE CustomerID = :customer_id");
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['cust_num'] = $customer ? $customer['ContactNumber'] : "No number available";
        } else {
            // Set default values for guest users
            $_SESSION['cust_num'] = "No number available";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        $_SESSION['cust_num'] = "Error fetching number";
    }
}

// Use cust_num in your page
$cust_num = $_SESSION['cust_num'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/images/logo.png">
    <title>DKAT Store</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <link rel="preload" href="../assets/images/detergent.WebP" as="image">
    <link rel="preload" href="../assets/images/cart.WebP" as="image">
    <style>
        .hero h1 {
          font-weight: 700;
          color: var(--text-light);
          margin-bottom: 30px;
        }
        
        @media (min-width: 1400px) {
          .hero h1 {
            font-size: 54px;
          }
        }
    </style>
</head>
<body>
<?php include("../includes/customer/header.php"); ?>
<br><br><br>
    <div class="page fade-element" id="Overview"> 
        <div id="Home" class="hero">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-5">
                        <div class="intro-excerpt">
                            <h1>DKAT <br><span style="color: #17e6e6">Chlorine and Swimming Pool Supply Trading</span></h1>
                            <p class="mb-4 text-light">We're here to help with your swimming pool.We supply Pool chemicals and other Pool Products.</p>
                            <p>
                            <?php if (isset($_SESSION['cust_email'])): ?>
                                <a href="#Products" class="btn btn-secondary me-2">Shop Now</a>
                            <?php else: ?>
                                <a href="../customer_login/login_form.php" class="btn-shop-now me-2">Sign in
                                </a>
                            <?php endif; ?>
                                <a href="#Explore" class="btn btn-white-outline scroll-link">Explore</a>
                            </p>

                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="hero-image">
                            <img src="../assets/images/detergent.WebP" loading="lazy" alt="DKAT">
                        </div>
                    </div>
                </div>
            </div>
        </div>

		<div id="Explore" class="product-section">
			<div class="container">
				<div class="row">
					<!-- Start Column 1 -->
					<div class="col-md-12 col-lg-3 mb-5 mb-lg-0">
						<h2 class="mb-4 section-title">Pool Chemicals with excellent result.</h2>
						<p class="mb-4 text-dark">Using high-quality pool chemicals ensures clean, safe water. Our products effectively sanitize and balance pH levels, keeping your pool crystal clear and enhancing swimmer comfort. Trust us for reliable results every time.</p>
						<p><a href="#Service" class="btn-shop-now scroll-link">Why choose us?</a></p>
					</div> 
					<!-- End Column 1 -->

					<!-- Start Column 2 -->
					<div class="col-12 col-md-4 col-lg-3 mb-5 mb-md-0">
						<a class="product-item" href="#Products">
							<img src="../assets/images/dry-acid-ph.jpeg" loading="lazy" class="img-fluid product-thumbnail" alt="Dry Acid PH">
							<h3 class="product-title">Dry Acid PH</h3>
							<strong class="product-price">Buy now</strong>

							<span class="icon-cross">
								<img src="../assets/images/cross.svg" class="img-fluid">
							</span>
						</a>
					</div> 
					<!-- End Column 2 -->

					<!-- Start Column 3 -->
					<div class="col-12 col-md-4 col-lg-3 mb-5 mb-md-0">
						<a class="product-item" href="#Products">
							<img src="../assets/images/Fujichlon.jpg" loading="lazy" class="img-fluid product-thumbnail" alt="Fujichlon">
							<h3 class="product-title">Fuji Chlon 70%</h3>
							<strong class="product-price">Buy now</strong>

							<span class="icon-cross">
								<img src="../assets/images/cross.svg" loading="lazy" class="img-fluid">
							</span>
						</a>
					</div>
					<!-- End Column 3 -->

					<!-- Start Column 4 -->
					<div class="col-12 col-md-4 col-lg-3 mb-5 mb-md-0">
						<a class="product-item" href="#Products">
							<img src="../assets/images/dicalite.jpg" loading="lazy" class="img-fluid product-thumbnail" alt="Dicalite">
							<h3 class="product-title">Dicalate de Powder</h3>
							<strong class="product-price">Buy now</strong>

							<span class="icon-cross">
								<img src="../assets/images/cross.svg" loading="lazy" class="img-fluid" alt="Dots">
							</span>
						</a>
					</div>
					<!-- End Column 4 -->

				</div>
			</div>
		</div>
		
        <div id="Service" class="why-choose-section">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-6">
                        <h2 class="section-title">Why Choose Us</h2>
                        <p>We are dedicated to providing the best pool solutions, ensuring quality, convenience, and customer satisfaction.</p>

                        <div class="row my-5">
                            <div class="col-6 col-md-6">
                                <div class="feature">
                                    <!--<div class="icon">-->
                                    <!--    <img src="../assets/images/truck.svg" loading="lazy" alt="Image" class="img-fluid" alt="Dicalite">-->
                                    <!--</div>-->
                                    <h3>Fast &amp; Affordable Shipping</h3>
                                    <p>Quick delivery at great prices.</p>
                                </div>
                            </div>

                            <div class="col-6 col-md-6">
                                <div class="feature">
                                    <!--<div class="icon">-->
                                    <!--    <img src="../assets/images/bag.svg" loading="lazy" alt="Image" class="img-fluid">-->
                                    <!--</div>-->
                                    <h3>Easy to Shop</h3>
                                    <p>Simple and hassle-free shopping experience.</p>
                                </div>
                            </div>

                            <div class="col-6 col-md-6">
                                <div class="feature">
                                    <!--<div class="icon">-->
                                    <!--    <img src="../assets/images/support.svg" alt="Image" class="img-fluid">-->
                                    <!--</div>-->
                                    <h3>Secure and Reliable</h3>
                                    <p>Your safety is our priority. Our website is protected with advanced security measures to ensure a safe shopping experience.</p>
                                </div>
                            </div>

                            <div class="col-6 col-md-6">
                                <div class="feature">
                                    <!--<div class="icon">-->
                                    <!--    <img src="../assets/images/return.svg" alt="Image" class="img-fluid">-->
                                    <!--</div>-->
                                    <h3>Premium Quality Products</h3>
                                    <p>Keep your swimming pool crystal clear with our high-grade cleaning chemicals, designed to deliver outstanding results every time.</p>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="imgs-grid">
                            <div class="grid">
                                <div class="grid-item item-1"><img src="../assets/images/Product1.png" loading="lazy" alt="Product 1"></div>
                                <div class="grid-item item-2"><img src="../assets/images/Product2.png" loading="lazy" alt="Product 3"></div>
                                <div class="grid-item item-3"><img src="../assets/images/Product3.png" loading="lazy" alt="Product 2"></div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        
        <div id="Carousel" class="image-section">
            <div class="container">
                <div class="row justify-content-center" style="height:500px">
                    <div class="col-12">
                        <div id="whyChooseUsCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="../assets/images/service1.jpg" loading="lazy" class="d-block w-100 img-fluid" alt="Service 1">
                                </div>
                                <div class="carousel-item">
                                    <img src="../assets/images/service2.jpg" loading="lazy" class="d-block w-100 img-fluid" alt="Service 2">
                                </div>
                                <div class="carousel-item">
                                    <img src="../assets/images/service3.jpg" loading="lazy" class="d-block w-100 img-fluid" alt="Service 3">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#whyChooseUsCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#whyChooseUsCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include("../includes/customer/footer.php"); ?>
    </div>
    
    <!-- Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Your Shopping Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php include('../modules/sales_management_system/transaction/cart/cart_read.php'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page fade-element" id="Products" >  
        <div class="row m-0">
            <div class="col-12 p-0">
                <?php include("../modules/sales_management_system/transaction/available_product.php"); ?>
            </div>
        </div>

        <!-- Update Quantity Modal (Moved outside of cart modal) -->
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
            document.addEventListener('DOMContentLoaded', function() {
                const updateButtons = document.querySelectorAll('.update-btn');
                const updateQuantityModal = document.getElementById('updateQuantityModal');
                
                // Create a Bootstrap Modal instance
                const quantityModal = new bootstrap.Modal(updateQuantityModal);
        
                // Add event listener to each update button
                updateButtons.forEach(button => {
                    button.addEventListener('click', function(event) {
                        const cartId = this.getAttribute('data-cart-id');
                        const currentQuantity = this.getAttribute('data-current-quantity');
                        const productName = this.getAttribute('data-product-name');
                        const productDescription = this.getAttribute('data-product-description');
                        const productImage = this.getAttribute('data-product-image');
                        const productPrice = this.getAttribute('data-product-price');
                        const availableStock = this.getAttribute('data-available-stock');
        
                        // Update the modal's content with product details
                        updateQuantityModal.querySelector('.modal-title').textContent = productName;
                        
                        // Populate modal body with product details
                        const modalBody = updateQuantityModal.querySelector('.modal-body');
                        modalBody.innerHTML = `
                            <div class="product-details">
                                <div class="product-info">
                                    <h5>${productName}</h5>
                                    <p class="text-muted">${productDescription}</p>
                                    <div class="product-meta">
                                        <p><strong>Price:</strong> ₱${productPrice}</p>
                                        <p><strong>Available Stock:</strong> ${availableStock} units</p>
                                    </div>
                                </div>
                            </div>
                            <form id="updateQuantityForm" method="POST" action="../modules/sales_management_system/transaction/cart/update_cart.php">
                                <input type="hidden" name="cart_id" id="cart_id" value="${cartId}">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" min="1" max="${availableStock}" class="form-control" value="${currentQuantity}" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Quantity</button>
                            </form>
                        `;
        
                        // Show the modal
                        quantityModal.show();
                    });
                });
        
                // Add event listener to properly close the modal
                updateQuantityModal.addEventListener('hidden.bs.modal', function () {
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                });
            });
        </script>
    </div>

    <div class="page fade-element" id="Profile">
        <div class="hero" style="min-height: 400px;"> <!-- Added min-height to prevent layout shift -->
            <div class="container mb-5">
                <div class="row justify-content-between">
                    <div class="col-lg-5">
                        <div class="intro-excerpt">
                            <h1>Welcome, <?= htmlspecialchars($cust_name) ?>!</h1>
                            <h5 class="text-info">Your orders are just a click away.</h5>
                            <p class="text-white">Your contact number: <?= htmlspecialchars($_SESSION['cust_num']) ?></p>
                            <a class="btn btn-secondary" href="../modules/sales_management_system/transaction/customer/order.php">View Orders</a>
                        </div>
                    </div>
                    <!-- <div class="col-lg-7"> -->
                    <!--     <div class="hero-image"> -->
                    <!--         <img src="../assets/images/cart.png" alt="Cart"> -->
                    <!--     </div> -->
                    <!-- </div> -->
                </div>
            </div>
        </div>
    </div>



    <?php //include("../includes/customer/footer.php"); ?>

    <script>
        $(document).ready(function() {
            // Fetch and populate province and city data
            $.ajax({
                url: "../includes/get_location_data.php",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    var provinces = data.provinces;
                    var cities = data.cities;
                    var provinceDropdown = $("#province");
                    var cityDropdown = $("#city");

                    // Populate province dropdown
                    provinces.forEach(function(province) {
                        provinceDropdown.append(
                            $("<option>").val(province.Province).text(province.Province)
                        );
                    });

                    // Event listener for province change
                    provinceDropdown.change(function() {
                        var selectedProvince = $(this).val();
                        cityDropdown.empty();
                        cityDropdown.append("<option value=''>Select City</option>");

                        // Filter and populate city dropdown based on selected province
                        cities.forEach(function(city) {
                            if (city.Province === selectedProvince) {
                                cityDropdown.append(
                                    $("<option>").val(city.LocationID).text(city.City)
                                );
                            }
                        });
                    });
                },
                error: function() {
                    alert("Error: Could not retrieve location data.");
                }
            });

            // When city is selected, set location_id
            $("#city").change(function() {
                var locationID = $(this).val();
                $("#location_id").val(locationID);
            });
        });
    </script>
    <script>
        // Select all elements with the 'scroll-link' class
        const scrollLinks = document.querySelectorAll('.scroll-link');

        // Add a click event listener to each link
        scrollLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default anchor behavior
                
                const targetId = this.getAttribute('href').substring(1); // Get the target section ID (without the #)
                const targetElement = document.getElementById(targetId); // Find the target element by ID

                if (targetElement) {
                    // Scroll to the target element
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });

                    // Update the URL to keep the #Overview (if needed)
                    history.pushState(null, null, '#Overview');
                }
            });
        });
    </script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const viewCartLinks = document.querySelectorAll('.view-cart-link');
        const cartModal = document.getElementById('cartModal');
    
        viewCartLinks.forEach(link => {
          link.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default link behavior
            var modal = new bootstrap.Modal(cartModal);
            modal.show(); // Manually show the cart modal
          });
        });
      });
    </script>
    <script src="../assets/js/navbar.js" defer></script>
</body>

</html>
