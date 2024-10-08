// checkUserType.js

document.addEventListener("DOMContentLoaded", function() {
  const userType = document.getElementById('userType').value;

  // Get all adminActions divs
  const adminActions = document.querySelectorAll('.adminActions');

  // Show or hide admin actions based on user type
  if (userType === 'AdminID') {
      adminActions.forEach(action => {
          action.style.display = 'block'; // Show for admin
      });
  } else {
      adminActions.forEach(action => {
          action.style.display = 'none'; // Hide for non-admins
      });
  }
});
