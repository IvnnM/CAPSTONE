<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top mb-2">
  <div class="container-fluid ps-lg-4 pe-lg-4">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../assets/images/logo.png" alt="Logo" width="40" height="40" class="d-inline-block align-text-top">
    </a>

    <!-- Toggle button for mobile view -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navigation links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['cust_name']) && isset($_SESSION['cust_num']) && isset($_SESSION['cust_email'])): ?>
          <li class="nav-item">
            <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/path-to-overview' ? 'active' : ''); ?>" href="#Overview">Products</a> <!-- Active class for current page -->
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Orders">Orders</a>
          </li>
          <!-- <li class="nav-item">
            <a class="nav-link" href="#About">About</a>
          </li> -->
          <li class="nav-item ms-3">
            <a class="btn btn-outline-secondary" href="/3CAPSTONE/logout.php">Sign out</a>
          </li>
        <?php else: ?>
          <!-- No user logged in: Show Log In button -->
          <li class="nav-item ms-3">
            <a class="btn btn-outline-secondary" href="/3CAPSTONE/logout.php">Sign out</a>
            <a class="btn btn-secondary bg-dark" href="#Overview">Sign up</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
