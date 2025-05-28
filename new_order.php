<?php
// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Message for user feedback
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerID = intval($_POST['customerID']);
    $productID = intval($_POST['productID']);
    $quantity = intval($_POST['orderQuantity']);

    if ($customerID > 0 && $productID > 0 && $quantity > 0) {
        // Check stock availability
        $stockQuery = "SELECT Quantity FROM Products WHERE Product_ID = ?";
        $stockStmt = $conn->prepare($stockQuery);
        $stockStmt->bind_param("i", $productID);
        $stockStmt->execute();
        $stockStmt->bind_result($availableStock);
        $stockStmt->fetch();
        $stockStmt->close();

        if ($availableStock >= $quantity) {
            // Place order with default status 'Pending'
            $sql = "INSERT INTO Orders (Customer_ID, Product_ID, Quantity, Status) VALUES (?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $customerID, $productID, $quantity);

            if ($stmt->execute()) {
                // Update stock
                $updateStock = "UPDATE Products SET Quantity = Quantity - ? WHERE Product_ID = ?";
                $updateStmt = $conn->prepare($updateStock);
                $updateStmt->bind_param("ii", $quantity, $productID);
                $updateStmt->execute();
                $updateStmt->close();

                $message = "✅ Order placed successfully!";
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "❌ Not enough stock available!";
        }
    } else {
        $message = "❌ Invalid input. Ensure all fields are correctly filled.";
    }
}

// Retrieve order list
$orders = $conn->query("SELECT Orders.Order_ID, Customers.Name AS Customer_Name, Products.Name AS Product_Name, Orders.Quantity, Orders.Status, Products.Quantity AS Stock_Level 
                        FROM Orders 
                        JOIN Customers ON Orders.Customer_ID = Customers.Customer_ID 
                        JOIN Products ON Orders.Product_ID = Products.Product_ID 
                        ORDER BY Order_ID DESC");

// Fetch sales data for visualization (Pie Chart)
$salesData = $conn->query("SELECT Products.Name AS Product_Name, SUM(Orders.Quantity) AS Total_Sold 
                           FROM Orders 
                           JOIN Products ON Orders.Product_ID = Products.Product_ID 
                           GROUP BY Orders.Product_ID");

$sales = [];
while ($row = $salesData->fetch_assoc()) {
    $sales[$row['Product_Name']] = $row['Total_Sold'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Management with Visualization</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="scripts.js" defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #2b5876, #4e4376);
            color: white;
        }

        .btn-submit, .btn-next {
            width: 100%;
            padding: 12px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 8px;
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }

        .btn-submit {
            background: linear-gradient(to right, #28a745, #218838);
            color: white;
        }

        .btn-next {
            background: linear-gradient(to right, #f39c12, #e67e22);
            color: white;
            margin-top: 15px;
        }

        .order-list {
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .list-group-item {
            padding: 15px;
            font-size: 1.2rem;
            border-radius: 8px;
            transition: transform 0.3s ease-in-out;
        }

        .list-group-item.low-stock {
            background: #e74c3c !important;
            color: white;
        }

        .chart-container {
            width: 80%;
            margin: auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Order Management with Sales Visualization</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="number" class="form-control mb-3" name="customerID" placeholder="Customer ID" required>
            <input type="number" class="form-control mb-3" name="productID" placeholder="Product ID" required>
            <input type="number" class="form-control mb-3" name="orderQuantity" placeholder="Quantity" required>
            <button type="submit" class="btn-submit">Place Order</button>
            <a href="page.php" class="btn btn-next">Home</a>
        </form>

        <div class="order-list">
            <h3>Order List</h3>
            <ul class="list-group">
                <?php while ($row = $orders->fetch_assoc()): ?>
                    <li class="list-group-item <?= ($row['Stock_Level'] < 10) ? 'low-stock' : '' ?>">
                        <?= "Order #" . htmlspecialchars($row['Order_ID']) ?> | <?= htmlspecialchars($row['Customer_Name']) ?> | <?= htmlspecialchars($row['Product_Name']) ?> | Qty: <?= htmlspecialchars($row['Quantity']) ?> | Status: <?= htmlspecialchars($row['Status']) ?> | Stock Left: <?= htmlspecialchars($row['Stock_Level']) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <!-- Pie Chart Container -->
        <div class="chart-container">
            <h3>Sales Distribution</h3>
            <canvas id="salesPieChart"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const salesData = <?= json_encode($sales); ?>;
            const ctx = document.getElementById('salesPieChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: Object.keys(salesData),
                    datasets: [{
                        label: 'Sales Per Product',
                        data: Object.values(salesData),
                        backgroundColor: ['#f39c12', '#3498db', '#e74c3c', '#9b59b6', '#2ecc71'],
                    }]
                },
                options: {
                    responsive: true
                }
            });
        });
    </script>
</body>
</html>