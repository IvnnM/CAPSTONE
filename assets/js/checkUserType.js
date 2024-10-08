// checkUserType.js

document.addEventListener("DOMContentLoaded", function() {
  const userType = document.getElementById('userType').value;

  // Get all adminActions divs
  const adminActions = document.querySelectorAll('.adminActions');

  // Show or hide admin actions based on user type
  if (userType === 'AdminID') {
      adminActions.forEach(action => {
          action.classList.add('d-flex', 'justify-content-center'); // Add the classes
          action.style.display = 'block'; // Show for admin
      });
  } else {
      adminActions.forEach(action => {
          action.classList.remove('d-flex', 'justify-content-center'); // Remove the classes
          action.style.display = 'none'; // Hide for non-admins
      });
  }
});
