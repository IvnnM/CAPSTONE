
<footer class="footer-section container-fluid text-light text-center mt-auto">
    <div class="container relative">
        <!-- <div class="sofa-img">
            <img src="images/sofa.png" alt="Image" class="img-fluid">
        </div> -->

        <!-- <div class="row">
            <div class="col-lg-8">
                <div class="subscription-form">
                    <h3 class="d-flex align-items-center">
                        <span class="me-1"><img src="images/envelope-outline.svg" alt="Image" class="img-fluid"></span>
                        <span>Subscribe to Newsletter</span>
                    </h3>
                    <form action="#" class="row g-3">
                        <div class="col-auto">
                            <input type="text" class="form-control" placeholder="Enter your name" required>
                        </div>
                        <div class="col-auto">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" type="submit">
                                <span class="fa fa-paper-plane"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->
        <hr class="bg-dark">
        <div class="row g-5 mb-5 d-flex text-start">
            <div class="col-lg-4">
                <div class="mb-4 footer-logo-wrap">
                    <a href="#" class="footer-logo">DKAT's Company<span>.</span></a>
                </div>
                <p class="mb-4 text-dark">Feel free to contact us via email or phone. We're here to assist you with any inquiries or issues you may have.</p>
                <ul class="list-unstyled custom-social">
                    <li><a class="fa fa-brands fa-facebook-f" href="https://www.facebook.com/profile.php?id=100092328014910" target="_blank" rel="noopener noreferrer"></a></li>
                    <li>
                    <a class="fa fa-envelope" href="https://mail.google.com/mail/?view=cm&fs=1&to=dkatchlorineandswimmingpst15@gmail.com" target="_blank" rel="noopener noreferrer"></a>
                    </li>
                    <li>
                        <a class="fa fa-phone" href="tel:09936180270" onclick="copyToClipboard('09936180270')"></a>
                    </li>
                    <script>
                        function copyToClipboard(text) {
                            navigator.clipboard.writeText(text).then(() => {
                                alert('Phone number copied to clipboard: ' + text);
                            }, (err) => {
                                console.error('Could not copy text: ', err);
                            });
                        }
                    </script>

                    <!-- <li><a class="fa fa-brands fa-twitter" href="#"></a></li>
                    <li><a class="fa fa-brands fa-instagram" href="#"></a></li> -->
                </ul>
            </div>

            <div class="col-lg-8">
                <div class="row links-wrap">
                    <div class="col-6 col-sm-6 col-md-3">
                        <ul class="list-unstyled">
                            <li><a href="#">About us</a></li>
                            <li><a href="#">Services</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Contact us</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-sm-6 col-md-3">
                        <ul class="list-unstyled">
                            <li><a href="#">Support</a></li>
                            <li><a href="#">Knowledge base</a></li>
                            <li><a href="#">Live chat</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-sm-6 col-md-3">
                        <ul class="list-unstyled">
                            <li><a href="#">Jobs</a></li>
                            <li><a href="#">Our team</a></li>
                            <li><a href="#">Leadership</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-sm-6 col-md-3">
                        <ul class="list-unstyled">
                            <li><a href="#">Ivan Medrano</a></li>
                            <li><a href="#">Maureen Lozares</a></li>
                            <li><a href="#">Chris John Bautista</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-top copyright">
            <div class="row pt-4">
                <div class="col-lg-6">
                    <p class="mb-2 text-center text-dark text-lg-start">
                    Copyright &copy;<script>document.write(new Date().getFullYear());</script> DKAT's Company. All rights reserved.
                    </p>
                </div>

                <div class="col-lg-6 text-center text-lg-end">
                    <ul class="list-unstyled d-inline-flex ms-auto">
                        <li class="me-4"><a href="#">Terms &amp; Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
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
