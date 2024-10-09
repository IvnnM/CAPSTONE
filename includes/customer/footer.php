<!-- Divider -->
<hr class="my-4" style="border-top: 1px solid white;">
<footer class="text-light text-center">
  <div class="container">
    <p class="mb-2">Feel free to contact us via email or phone. We're here to assist you with any inquiries or issues you may have.</p>
    <p class="mb-0">© 2024 DKAT's Company. All rights reserved.</p>
    <div class="social-icons mt-3">
      <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
      <a href="#" class="text-light me-3"><i class="bi bi-envelope"></i></a>
      <a href="#" class="text-light"><i class="bi bi-telephone"></i></a>
    </div>
    <a class="logout-link" href="/3CAPSTONE/logout.php">Logout</a>
    <br><br>
  </div>
</footer>

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