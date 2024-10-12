<link rel="stylesheet" href="../assets/css/style.css">
<footer class="container-fluid text-light text-center mt-auto">
    <hr class="my-4" style="border-top: 1px solid white;">
    <div class="container">
      <p class="mb-2">Feel free to contact us via email or phone. We're here to assist you with any inquiries or issues you may have.</p>
      <p class="mb-0">© 2024 DKAT's Company. All rights reserved.</p>
      <div class="social-icons mt-3">
        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
        <a href="#" class="text-light me-3"><i class="bi bi-envelope"></i></a>
        <a href="#" class="text-light"><i class="bi bi-telephone"></i></a>
      </div>
      <!-- <a class="logout-link" href="/3CAPSTONE/logout.php">Logout</a> -->
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

      // Function to set the active link based on the current URL hash
      function setActiveLink() {
          const currentHash = window.location.hash; // Get the current hash from the URL
          navLinks.forEach(link => {
              // Remove active class from all links
              link.classList.remove('active');

              // Check if the link's href matches the current hash
              if (link.getAttribute('href') === currentHash) {
                  link.classList.add('active'); // Add active class to the matching link
              }
          });
      }

      // Call the function to set the active link on page load
      setActiveLink();

      // Add click event listeners to all nav links
      navLinks.forEach(link => {
          link.addEventListener('click', function (event) {
              // Prevent the default behavior of the link
              event.preventDefault();

              // Update the URL hash without reloading the page
              const targetHash = this.getAttribute('href');
              window.location.hash = targetHash;

              // Set the active link
              setActiveLink();
          });
      });

      // Update active link on hash change (e.g., if links change the URL hash without reloading)
      window.addEventListener('hashchange', setActiveLink);
  });
</script>

