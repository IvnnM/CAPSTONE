$(document).ready(function() {
  // Function to show a page based on the provided ID
  function showPage(target) {
      $('.page').hide(); // Hide all pages
      $('#' + target).show(); // Show the targeted page

      // Update the URL hash (without reloading the page)
      if (history.pushState) {
          history.pushState(null, null, '#' + target);
      } else {
          window.location.hash = '#' + target; // For older browsers
      }
  }

  // Check the current URL hash on page load
  var currentHash = window.location.hash.substring(1); // Remove the "#" symbol
  if (currentHash) {
      // If there's a hash, show the corresponding page
      showPage(currentHash);
  } else {
      // Default to 'Overview' if no hash is present
      showPage('Overview');
  }

  // Event listener for navbar links
  $('.nav-link').click(function(e) {
      e.preventDefault(); // Prevent default anchor behavior
      var target = $(this).attr('href').substring(1); // Get the target from href without '#'
      showPage(target); // Show the target page
  });

  // Handle back/forward navigation (when URL hash changes)
  $(window).on('hashchange', function() {
      var newHash = window.location.hash.substring(1); // Get the new hash without '#'
      if (newHash) {
          showPage(newHash); // Show the corresponding page when hash changes
      }
  });
});
