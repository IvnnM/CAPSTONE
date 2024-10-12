<footer class="container-fluid bg-dark text-light text-center mt-auto">
    <hr class="my-4" style="border-top: 1px solid white;">
    <div class="container">
        <p class="mb-2">Feel free to contact us via email or phone. We're here to assist you with any inquiries or issues you may have.</p>
        <p class="mb-0">© 2024 DKAT's Company. All rights reserved.</p>
        <div class="social-icons mt-3">
            <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
            <a href="#" class="text-light me-3"><i class="bi bi-envelope"></i></a>
            <a href="#" class="text-light"><i class="bi bi-telephone"></i></a>
        </div>
        <!-- <a class="logout-link text-light mt-3 d-block" href="/3CAPSTONE/logout.php">Logout</a> -->
         <br>
    </div>
</foot>

<script>
    function goBack() {
        window.history.back();
    }
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
      navLinks.forEach(link => {
          link.addEventListener('click', function () {
              navLinks.forEach(nav => nav.classList.remove('active')); // Remove active class from all links
              this.classList.add('active'); // Add active class to the clicked link
          });
      });
  });
</script>
<script>
$(document).ready(function() {
    // Check if the alert message exists
    var alert = $('#alert-message');
    if (alert.length) {
        // Set a timeout to fade out the alert after 5 seconds (5000 milliseconds)
        setTimeout(function() {
            alert.fadeOut(1000); // Fades out over 1 second
        }, 2000);
    }
});
</script>
