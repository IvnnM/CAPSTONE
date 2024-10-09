<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top mb-2">
  <div class="container-fluid ps-lg-4 pe-lg-4">
    <!-- Logo and Title -->
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../assets/images/logo.png" alt="Logo" width="40" height="40" class="d-inline-block align-text-top">
      <!-- <strong class="ms-2 custom-title">DKAT Website</strong> -->
    </a>

    <!-- Toggle button for mobile view -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navigation links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['AdminID'])): ?>
          <!-- Admin: Show all links -->
          <li class="nav-item">
            <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/path-to-overview' ? 'active' : ''); ?>" href="#Overview">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Products">Products</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Transaction">Transactions</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Employee">Employees</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Store">Store</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Profile">Profile</a>
          </li>
          <li class="nav-item ms-3">
            <a class="btn btn-outline-secondary" href="/3CAPSTONE/logout.php">Sign out</a>
          </li>
        <?php elseif (isset($_SESSION['EmpID'])): ?>
          <!-- Employee: Hide Employees link -->
          <li class="nav-item">
            <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/path-to-overview' ? 'active' : ''); ?>" href="#Overview">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Products">Products</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Transaction">Transactions</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Store">Store</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#Profile">Profile</a>
          </li>
          <li class="nav-item ms-3">
            <a class="btn btn-outline-secondary" href="/3CAPSTONE/logout.php">Sign out</a>
          </li>
        <?php else: ?>
          <!-- No user logged in: Show only Log In button -->
          <li class="nav-item">
            <a class="btn btn-secondary bg-dark" href="login.php">Log In</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
