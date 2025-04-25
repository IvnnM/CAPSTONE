<style>
/* Ensure the navbar links stay on one line and do not wrap */
.navbar-nav {
    display: flex;
    flex-direction: row; 
    flex-wrap: nowrap; 
    justify-content: center;
    width: 100%; 
    white-space: nowrap;
}

.navbar .nav-link {
    color: #ffffff !important; /* White text color */
    font-weight: 500; /* Slightly bold */
    text-transform: uppercase; /* Makes the text uppercase */
    padding: 0.5rem 1rem; /* Adds spacing around the text */
    transition: color 0.3s ease, border-bottom 0.3s ease; /* Smooth transition for color and border-bottom */
}

.navbar .nav-link:hover,
.navbar .nav-link:focus {
    color: #ffcc00; /* Bright yellow color on hover/focus */
    text-decoration: none; /* Removes underline */
    border-radius: 5px; /* Rounded corners on hover */
}

.navbar .nav-link.active {
    color: #ffcc00; /* Bright yellow color for active link */
    font-weight: 600; /* Slightly bolder font */
    border-bottom: 2px solid #17e6e6; /* Highlight active link with a bottom border */
    transition: color 0.3s ease, border-bottom 0.3s ease; /* Smooth transition */
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .navbar-nav {
        justify-content: flex-start; /* Align left on smaller screens */
        overflow-x: auto; /* Allow horizontal scrolling if necessary */
    }
}

</style>

<nav class="navbar navbar-expand-lg navbar-light sticky-top mb-2" style="background-color: #003366">
    <div class="container-fluid ps-lg-4 pe-lg-4 pt-4 ">
        
        <button class="btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-label="Open Sidebar">
            <i class="bi bi-list text-light" style="font-size: 1.5rem;"></i> <!-- Icon for the button -->
        </button>

        <!-- Navigation links -->
        <ul class="navbar-nav">
            <?php if (isset($_SESSION['AdminID']) || isset($_SESSION['EmpID'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#Overview" onclick="window.location.href='/views/personnel_view.php#Overview'; return false;">Dashboard</a>
                </li>
            
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
        function updateActiveLink() {
            const currentURL = window.location.hash || "#Overview"; // Default to #Overview if no hash
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentURL) {
                    link.classList.add('active'); // Add 'active' class
                } else {
                    link.classList.remove('active'); // Remove 'active' class
                }
            });
        }
    
        // Initial update based on current URL
        updateActiveLink();
    
        // Update active link on hash change
        window.addEventListener('hashchange', updateActiveLink);
    
        // Update active link on link click
        navLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default anchor behavior (optional, based on needs)
                window.location.hash = this.getAttribute('href'); // Manually set the hash
                updateActiveLink(); // Update active class immediately
            });
        });
    });


</script>
