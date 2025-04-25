<footer class="footer-section container-fluid text-light text-center mt-auto">
    <div class="container relative">
        <hr class="bg-dark">
        <div class="row g-6 mb-5 d-flex text-start">
            <div class="col-4">
                <div class="mb-4 footer-logo-wrap">
                    <h2 href="#" class="footer-logo">DKAT's Company<span>.</span></h2>
                </div>
                <p class="mb-4 text-dark">Feel free to contact us via email or phone. We're here to assist you with any inquiries or issues you may have.</p>
                <ul class="list-unstyled custom-social">
                    <li>
                        <a class="fa fa-brands fa-facebook-f" href="https://www.facebook.com/profile.php?id=100092328014910" aria-label="Visit our Facebook page" target="_blank" rel="noopener noreferrer"></a>
                    </li>
                    <li>
                        <a class="fa fa-envelope" href="https://mail.google.com/mail/?view=cm&fs=1&to=dkatchlorineandswimmingpst15@gmail.com" aria-label="Send us an email" target="_blank" rel="noopener noreferrer"></a>
                    </li>
                    <li>
                        <a class="fa fa-phone" href="tel:09936180270" aria-label="Call us" onclick="copyToClipboard('09936180270')"></a>
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
                </ul>
            </div>

           <div class="col-8">
                <div class="row links-wrap">
                    <div class="col-6 col-sm-6 col-md-3">
                        <ul class="list-unstyled">
                            <li><a href="../includes/customer/content.html#about-us">About us</a></li>
                            <li><a href="../includes/customer/content.html#contact-us">Contact us</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-sm-6 col-md-3">
                        <ul class="list-unstyled">
                            <li><a href="../includes/customer/content.html#services">Services</a></li>
                            <li><a href="../includes/customer/content.html#blog">Blog</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-sm-6 col-md-3">
                        <ul class="list-unstyled">
                            <li><a href="#" id="terms-link" onclick="openTermsModal()">Terms and Conditions</a></li>
                            <li><a href="#" id="privacy-link" onclick="openPrivacyModal()">Privacy Policy</a></li>
                       </ul>
                    </div>

                    <div class="col-6 col-sm-6 col-md-3">
                        <ul class="list-unstyled">
                            <li><a href="../includes/customer/content.html#developers">The Developers</a></li>
                            <!--<li><a href="../includes/customer/content.html#developers">Ivan Medrano</a></li>-->
                            <!--<li><a href="../includes/customer/content.html#developers">Maureen Lozares</a></li>-->
                            <!--<li><a href="../includes/customer/content.html#developers">Chris John Bautista</a></li>-->
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-top copyright">
            <div class="row pt-4">
                <div class="col-lg-6">
                    <p class="mb-2 text-center text-dark text-lg-start">
                    Copyright &copy;<script>document.write(new Date().getFullYear());</script> DKAT. All rights reserved.
                    </p>
                </div>

                <div class="col-lg-6 text-center text-lg-end">
                    <ul class="list-unstyled d-inline-flex ms-auto">
                        <li class="me-4"><a href="#" onclick="openTermsModal()">Terms &amp; Conditions</a></li>
                        <li><a href="#" onclick="openPrivacyModal()">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
<style>
    .terms-container {
            font-size: 0.9rem;
            color: #333;
        }
        
        .terms-container a {
            text-decoration: underline;
            color: #007bff;
            cursor: pointer;
        }
        
        .terms-container a:hover {
            color: #0056b3;
        }
        
        .terms-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin: 0;
            accent-color: #007bff; /* Sets the checkbox color in modern browsers */
        }
        
        .d-flex {
            display: flex;
        }
        
        .align-items-center {
            align-items: center;
        }
        .me-2 {
            margin-right: 8px;
        }
        .terms-container a {
            margin-right: 10px;
        }
</style>
    <!-- Terms & Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Acceptance of Terms</h6>
                    <p>By accessing or using the DKAT Chlorine and Swimming Pool Supply Trading System ("System"), you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use the System.</p>
    
                    <h6>2. User Accounts</h6>
                    <p>- Users are required to register an account to access certain features of the System.</p>
                    <p>- Users must provide accurate, complete, and updated information during registration.</p>
                    <p>- Users are responsible for maintaining the confidentiality of their account credentials.</p>
    
                    <h6>3. Permitted Use</h6>
                    <p>The System is intended for authorized users for managing inventory, sales, and logistics processes. Any misuse of the System, including unauthorized access or actions, is strictly prohibited.</p>
    
                    <h6>4. System Access and Availability</h6>
                    <p>The System is provided on an "as-is" and "as-available" basis. We reserve the right to modify, suspend, or discontinue the System at any time without notice.</p>
    
                    <h6>5. Limitation of Liability</h6>
                    <p>- DKAT and its developers are not liable for any loss or damages resulting from the use of the System.</p>
                    <p>- Users are solely responsible for ensuring the accuracy of data entered into the System.</p>
    
                    <h6>6. Intellectual Property</h6>
                    <p>The System and its content are the intellectual property of DKAT Chlorine and Swimming Pool Supply Trading. Users may not copy, distribute, or reproduce any part of the System without prior authorization.</p>
    
                    <h6>7. Updates and Modifications</h6>
                    <p>These Terms and Conditions may be updated periodically. Continued use of the System constitutes acceptance of the updated terms.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Privacy Policy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">Data Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Collection of Data</h6>
                    <p>- The System collects personal information, including names, email addresses, and contact details, during user registration.</p>
                    <p>- Additional data, such as inventory and transaction records, are collected to facilitate system operations.</p>
                    
                    <h6>2. Use of Data</h6>
                    <p>- Data is used to manage sales, inventory, and logistics, as well as for analytical purposes to improve system functionality.</p>
                    <p>- Personal information is used solely for account management and communication purposes.</p>
                    
                    <h6>3. Data Security</h6>
                    <p>- The System employs encryption and access controls to protect personal and transactional data.</p>
                    <p>- Regular security audits are conducted to safeguard against unauthorized access or breaches.</p>
                    
                    <h6>4. Data Sharing</h6>
                    <p>- Personal data will not be shared with third parties without user consent, except as required by law.</p>
                    
                    <h6>5. User Rights</h6>
                    <p>- Users have the right to access, update, or delete their personal information by contacting the System administrator.</p>
                    <p>- Users may request a copy of their stored data or withdraw their consent for data processing.</p>
                    
                    <h6>6. Retention of Data</h6>
                    <p>- User data is retained as long as the account remains active or as required to meet legal obligations.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openTermsModal() {
            var termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
            termsModal.show();
        }

        function openPrivacyModal() {
            var privacyModal = new bootstrap.Modal(document.getElementById('privacyModal'));
            privacyModal.show();
        }

    </script>

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
    window.addEventListener('load', function() {
  document.body.classList.add('page-loaded');
});

</script>