<?php 
session_start();
include("../../../includes/cdn.html");
include("../../../config/database.php");

// Fetch the store's exact coordinates using PDO
$query_store = "SELECT StoreExactCoordinates FROM StoreInfoTb LIMIT 1";
$stmt_store = $conn->prepare($query_store);
$stmt_store->execute();
$store_data = $stmt_store->fetch(PDO::FETCH_ASSOC);

// Check if store data was retrieved successfully
if ($store_data && !empty($store_data['StoreExactCoordinates'])) {
    $store_coords = explode(',', $store_data['StoreExactCoordinates']);
} else {
    // Set default coordinates if the query fails or returns no results
    $store_coords = [28.2380, 83.9956]; // Example default coordinates
}

// Fetch multiple transaction coordinates and details using PDO
$query_transac = "SELECT TransacID, CustName, CustNum, CustEmail, CustNote, LocationID, DeliveryFee, TotalPrice, TransactionDate, Status, ExactCoordinates FROM TransacTb WHERE Status = 'ToShip' ORDER BY TransactionDate ASC";
$stmt_transac = $conn->prepare($query_transac);
$stmt_transac->execute();

$transactions = [];
while ($row = $stmt_transac->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($row['ExactCoordinates'])) {
        $coord = explode(',', $row['ExactCoordinates']);
        $transactions[] = [
            'coords' => $coord,
            'name' => $row['CustName'],
            'CustNum' => $row['CustNum'],
            'CustEmail' => $row['CustEmail'],
            'DeliveryFee' => $row['DeliveryFee'],
            'TotalPrice' => $row['TotalPrice'],
            'TransactionDate' => $row['TransactionDate'],
            'Status' => $row['Status'],
            'TransacID' => $row['TransacID'], // Add TransacID for fetching cart records
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Geolocation</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      display: flex;
    }
    #map {
      width: 70%; /* Adjust width for the map */
      height: 100vh;
    }
    #transaction-table {
      width: 30%; /* Adjust width for the table */
      padding: 10px;
      overflow-y: auto; /* Scrollable table */
      height: 100vh;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f2f2f2;
      cursor: pointer; /* Show cursor on hover */
    }
  </style>
</head>
<body>
  <div id="map"></div>
  <div id="transaction-table">
    <table>
      <thead>
        <tr>
          <th onclick="sortTable(0)">Customer Name</th>
          <th onclick="sortTable(1)">Delivery Fee</th>
          <th onclick="sortTable(2)">Total Price</th>
          <th onclick="sortTable(3)">Transaction Date</th>
          <th>Action</th> <!-- New column for actions -->
        </tr>
      </thead>
      <tbody id="table-body">
        <?php foreach ($transactions as $transaction): ?>
        <tr>
          <td><?php echo htmlspecialchars($transaction['name']); ?></td>
          <td><?php echo htmlspecialchars($transaction['DeliveryFee']); ?></td>
          <td><?php echo htmlspecialchars($transaction['TotalPrice']); ?></td>
          <td><?php echo htmlspecialchars($transaction['TransactionDate']); ?></td>
          <td>
            <button class="btn btn-primary" onclick="fetchCartRecords('<?php echo $transaction['TransacID']; ?>')">View Cart</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Bootstrap Modal -->
  <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cartModalLabel">Cart Records</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="cart-details">
          <!-- Cart details will be populated here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script src="routing.js"></script>

  <script>
    var storeCoords = <?php echo json_encode($store_coords); ?>;
    var transactions = <?php echo json_encode($transactions); ?>;

    // Initialize the map with user's coordinates
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {
        var userCoords = [position.coords.latitude, position.coords.longitude];
        initMap(userCoords, storeCoords, transactions);
      }, function() {
        alert("Unable to retrieve your location.");
        initMap(storeCoords, storeCoords, transactions); // Initialize map with store coordinates if geolocation fails
      });
    } else {
      alert("Geolocation is not supported by this browser.");
      initMap(storeCoords, storeCoords, transactions); // Initialize map with store coordinates if geolocation is not supported
    }

    // Function to sort table
    function sortTable(columnIndex) {
      var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
      table = document.querySelector("table");
      switching = true;
      dir = "asc"; // Set the sorting direction to ascending

      while (switching) {
        switching = false;
        rows = table.rows;

        for (i = 1; i < (rows.length - 1); i++) {
          shouldSwitch = false;
          x = rows[i].getElementsByTagName("TD")[columnIndex];
          y = rows[i + 1].getElementsByTagName("TD")[columnIndex];

          // Compare the two rows based on the selected column
          if (dir == "asc") {
            if (parseFloat(x.innerHTML) > parseFloat(y.innerHTML)) {
              shouldSwitch = true;
              break;
            }
          } else if (dir == "desc") {
            if (parseFloat(x.innerHTML) < parseFloat(y.innerHTML)) {
              shouldSwitch = true;
              break;
            }
          }
        }
        if (shouldSwitch) {
          rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
          switching = true;
          switchcount++;
        } else {
          if (switchcount === 0 && dir === "asc") {
            dir = "desc"; // Switch to descending
            switching = true;
          }
        }
      }
    }

    // Function to fetch cart records
    function fetchCartRecords(transacID) {
      fetch('fetch_cart_records.php?transacID=' + transacID)
        .then(response => {
          if (!response.ok) {
            throw new Error("Network response was not ok: " + response.statusText);
          }
          return response.json();
        })
        .then(data => {
          // Check if there is an error in the response
          if (data.error) {
            alert("Error: " + data.error);
            console.error('Error from server:', data.error);
            return;
          }

          // Display product details in the modal
          const cartDetailsDiv = document.getElementById('cart-details');
          cartDetailsDiv.innerHTML = ''; // Clear previous content
          if (data.length > 0) {
            data.forEach(item => {
              const productDetail = `
                <div>
                  <h6>${item.ProductName}</h6>
                  <p>Description: ${item.ProductDesc}</p>
                  <p>Quantity: ${item.Quantity}</p>
                  <p>Price: ${item.Price}</p>
                  <img src="${item.ProductImage}" alt="${item.ProductName}" style="width: 100px;">
                  <hr>
                </div>
              `;
              cartDetailsDiv.innerHTML += productDetail;
            });
          } else {
            cartDetailsDiv.innerHTML = '<p>No cart records found for this transaction.</p>';
          }

          // Show the modal
          var cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
          cartModal.show();
        })
        .catch(error => {
          console.error('Error fetching cart records:', error);
          alert('An error occurred while fetching cart records.');
        });
    }
  </script>
</body>
</html>
