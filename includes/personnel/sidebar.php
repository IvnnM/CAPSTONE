<!-- Offcanvas Sidebar Styles -->
<style>
    /* Offcanvas sidebar styles */
    .offcanvas {
        background-color: #023a4a; /* Dark background */
    }

    /* Link styles */
    .offcanvas a {
        color: white;
        text-decoration: none;
        transition: background-color 0.3s, color 0.3s, transform 0.3s;
        font-size: 1.1rem;
        padding: 15px 20px;
        display: block;
    }

    /* Hover effect for links */
    .offcanvas a:hover {
        background-color: transparent;
        color: #ffffff;
        transform: scale(1.05);
    }

    /* Active nav item styles */
    .offcanvas a.active {
        background-color: #2d8d79;
        color: #ffffff;
        font-weight: bold;
    }

    /* Sign Out effect for links */
    #signout:hover {
        background-color: #dc3545; /* Slightly lighter hover effect */
        color: #ffffff; /* Change text color on hover */
        transform: scale(1.05); /* Slightly increase size on hover */
    }

    #signout:active {
        background-color: #dc3545; /* Highlight background for active item */
        color: #ffffff; /* Change text color for active item */
        font-weight: bold;
    }

    .profile-section {
        display: flex; /* Flex layout for alignment */
        align-items: center; /* Center items vertically */
        margin-bottom: 20px; /* Space below profile */
        padding: 15px; /* Add padding for better spacing */
        border-radius: 8px; /* Rounded corners */
        background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent background */
        transition: background-color 0.3s; /* Smooth transition */
    }

    .profile-section:hover {
        background-color: rgba(255, 255, 255, 0.2); /* Lighter background on hover */
    }

    .profile-icon {
        font-size: 50px; /* Set icon size */
        color: #ffffff; /* Icon color */
        margin-right: 15px; /* Space between icon and text */
    }

    .profile-info {
        color: white; /* White text */
    }

    .profile-info .username {
        font-weight: bold; /* Bold username */
        font-size: 1.2rem; /* Larger font size */
    }

    .profile-info .role {
        font-size: 0.9rem; /* Smaller font size for role */
        color: rgba(255, 255, 255, 0.7); /* Lighter color for role */
    }

    /* Hide scrollbar for Webkit browsers */
    .offcanvas::-webkit-scrollbar {
        display: none; /* Remove scrollbar */
    }
</style>

<!-- Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
    <div class="offcanvas-header">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Profile Section -->
        <div class="profile-section" 
             onclick="location.href='<?php echo isset($_SESSION['AdminID']) ? 
             '../modules/inventory_management_system/user_management/admin/admin_update.php' : 
             '../modules/inventory_management_system/user_management/employee/employee_update.php'; ?>';" 
             style="cursor: pointer;">
            <i class="bi bi-person-circle profile-icon"></i>
            <div class="profile-info">
                <div class="username"><?php echo htmlspecialchars($_SESSION['AdminName']); ?>!</div>
                <div class="role">
                    <?php
                        // Determine the role based on session variables
                        if (isset($_SESSION['AdminID'])) {
                            echo "Admin"; // User is an Admin
                        } elseif (isset($_SESSION['EmpID'])) {
                            echo "<strong>Employee ID:</strong> " . htmlspecialchars($_SESSION['EmpID']); // User is an Employee
                        } else {
                            echo "Guest"; // Default role if no ID is set
                        }
                    ?>
                </div>
            </div>
        </div>
        
        <div>
            <a href="/predictive/index.php" class="btn btn-outline-light text-start w-100 mb-2 p-3">Site Selection Report</a>
            <a href="/modules/geographic_information_system/route_optimization/route.php" class="btn btn-outline-light text-start w-100 mb-2 p-3">Route Optimization</a>
            <a href="#Products" class="btn btn-outline-light text-start w-100 mb-2 p-3">Products</a>
            <a href="#Transaction" class="btn btn-outline-light text-start w-100 mb-2 p-3">Transactions</a>
            <?php if (isset($_SESSION['AdminID'])): ?>
                <a href="#Employee" class="btn btn-outline-light text-start w-100 mb-2 p-3">Employees</a>
            <?php endif; ?>
            <a href="#Store" class="btn btn-outline-light text-start w-100 mb-2 p-3">Store</a>
            <a href="/logout.php" class="btn btn-outline-danger text-start w-100 mb-2 p-3" id="signout">Sign Out</a>
        </div>
    </div>
</div>

<script>
    // Optional: Ensure transitions are smooth
    document.addEventListener('DOMContentLoaded', function () {
        const offcanvas = document.getElementById('offcanvasSidebar');
        offcanvas.addEventListener('show.bs.offcanvas', function () {
            offcanvas.classList.add('show'); // Add class when opening
        });
        offcanvas.addEventListener('hide.bs.offcanvas', function () {
            offcanvas.classList.remove('show'); // Remove class when closing
        });

        // Function to highlight the active button
        function highlightActiveButton() {
            const buttons = document.querySelectorAll('.offcanvas a');
            buttons.forEach(button => {
                button.classList.remove('active'); // Remove active class from all buttons
            });
            const currentHash = window.location.hash;
            const activeButton = document.querySelector(`.offcanvas a[href="${currentHash}"]`);
            if (activeButton) {
                activeButton.classList.add('active'); // Add active class to the current button
            }
        }

        // Highlight active button on page load
        highlightActiveButton();

        // Highlight active button on hash change
        window.addEventListener('hashchange', highlightActiveButton);

        // Add click event to each button for navigation
        const buttons = document.querySelectorAll('.offcanvas a');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                highlightActiveButton();
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasSidebar'));
                if (offcanvas) {
                    offcanvas.hide(); // Close the sidebar when a link is clicked
                }
            });
        });
    });
</script>
