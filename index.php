<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome | Employee Inventory & Sales Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: Arial, sans-serif;
    }
    .container {
      text-align: center;
    }
    h1 {
      margin-bottom: 40px;
    }
    .btn {
      padding: 15px 30px;
      font-size: 18px;
      margin: 10px;
    }
    .btn-personnel {
      background-color: #007bff;
      color: white;
    }
    .btn-customer {
      background-color: #28a745;
      color: white;
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Welcome to Inventory & Sales Management</h1>
    <div>
      <a href="login.php"><button class="btn btn-personnel">Personnel Access</button></a>
      <a href="./views/customer_view.php"><button class="btn btn-customer">Get Started as Customer</button></a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
