<!-- header.php -->
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

<header class="text-light pt-2" style="background-color: #003366">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <!-- Page Title -->
        <h1 class="m-0">Welcome, <?php echo htmlspecialchars($_SESSION['AdminName']); ?>!</h1>
        <span class="navbar-text me-3">
            <?php echo date("l, F j, Y");?>
        </span>
    </div>
</header>
<script>
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