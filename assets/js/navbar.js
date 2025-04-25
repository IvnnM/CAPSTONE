$(document).ready(function() {
    // Fade in the body
    $('body').addClass('show');

    // Function to show a page based on the provided ID
    function showPage(target) {
        // Hide all pages except the target
        $('.page').not('#' + target).removeClass('show');
        setTimeout(function() {
            $('.page').not('#' + target).hide(); // Hide non-target pages
        }); // Match CSS transition duration

        // Show the targeted page immediately
        $('#' + target).show();

        // Trigger the transition after a slight delay
        setTimeout(function() {
            $('#' + target).addClass('show');
        });

        // Update the URL hash (without reloading the page)
        history.replaceState(null, null, '#' + target);

        // Scroll to the top of the targeted page
        $('html, body').animate({ scrollTop: 0 }, 300); // Optional: smooth scroll to top
    }

    // Check the current URL hash on page load
    var currentHash = window.location.hash.substring(1);
    if (currentHash) {
        showPage(currentHash); // Show the corresponding page
    } else {
        showPage('Overview'); // Default to 'Overview'
    }

    // Event listener for navbar links
    $('.nav-link').click(function(e) {
        var target = $(this).attr('href').substring(1);

        // If target is a page (not a submenu), show the page
        if ($(this).parents('.sub-menu').length === 0) {
            e.preventDefault(); // Prevent default anchor behavior
            showPage(target); // Show the target page
        }
    });

    // Event listener for submenu links
    $('.sub-menu .nav-link').click(function(e) {
        // Allow normal navigation for submenu links
        // Just remove e.preventDefault() to let it navigate normally
    });

    // Handle back/forward navigation (when URL hash changes)
    $(window).on('hashchange', function() {
        var newHash = window.location.hash.substring(1);
        if (newHash) {
            showPage(newHash);
        }
    });

    // Prevent back/forward cache issues
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Page was restored from bfcache
            var currentHash = window.location.hash.substring(1);
            if (currentHash) {
                showPage(currentHash);
            } else {
                showPage('Overview');
            }
        }
    });

    // Example usage: Fade in/out another element
    $('.fade-button').click(function() {
        const target = $(this).data('target');
        showElement(target);
    });
});