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
    $productID = intval($_POST['productID']);
    $changeType = $_POST['changeType'];
    $changeQuantity = intval($_POST['changeQuantity']);

    if ($productID > 0 && !empty($changeType) && $changeQuantity != 0) {
        if ($changeType === "Sale") {
            $stockQuery = "SELECT Quantity FROM Products WHERE Product_ID = ?";
            $stockStmt = $conn->prepare($stockQuery);
            $stockStmt->bind_param("i", $productID);
            $stockStmt->execute();
            $stockStmt->bind_result($availableStock);
            $stockStmt->fetch();
            $stockStmt->close();

            if ($availableStock >= abs($changeQuantity)) {
                $updateStock = "UPDATE Products SET Quantity = Quantity - ? WHERE Product_ID = ?";
                $updateStmt = $conn->prepare($updateStock);
                $updateStmt->bind_param("ii", $changeQuantity, $productID);
                $updateStmt->execute();
                $updateStmt->close();
                $message = "✅ Sale processed, stock updated!";
            } else {
                $message = "❌ Not enough stock available!";
            }
        } else {
            $updateStock = "UPDATE Products SET Quantity = Quantity + ? WHERE Product_ID = ?";
            $updateStmt = $conn->prepare($updateStock);
            $updateStmt->bind_param("ii", $changeQuantity, $productID);
            $updateStmt->execute();
            $updateStmt->close();
            $message = "✅ Stock updated successfully!";
        }

        $sql = "INSERT INTO Stock_Updates (Product_ID, Change_Type, Change_Quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $productID, $changeType, $changeQuantity);
        $stmt->execute();
        $stmt->close();

        header("Location: new_stock.php");
        exit();
    } else {
        $message = "❌ Invalid input. Ensure all fields are correctly filled.";
    }
}

$stockUpdates = $conn->query("SELECT Stock_Updates.Update_ID, Products.Name AS Product_Name, Stock_Updates.Change_Type, Stock_Updates.Change_Quantity, Stock_Updates.Update_Date 
                              FROM Stock_Updates 
                              JOIN Products ON Stock_Updates.Product_ID = Products.Product_ID 
                              ORDER BY Update_ID DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Stock Update Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script src="scripts.js" defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #2b5876, #4e4376);
            color: white;
        }
        
        nav {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
            color: #ffffff !important;
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

        .btn-submit:hover, .btn-next:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .stock-update-list {
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

        .list-group-item:nth-child(odd) {
            background: #3498db;
            color: white;
        }

        .list-group-item:nth-child(even) {
            background: #2ecc71;
            color: white;
        }

        .list-group-item:hover {
            transform: scale(1.02);
            background: #f39c12;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-store"></i> StoreDB</a>
        </div>
    </nav>

    <div class="container">
        <h2>Stock Update Management</h2>
        <form method="POST">
            <input type="number" class="form-control mb-3" name="productID" placeholder="Product ID" required>
            <select class="form-control mb-3" name="changeType" required>
                <option value="Restock">Restock</option>
                <option value="Sale">Sale</option>
                <option value="Return">Return</option>
            </select>
            <input type="number" class="form-control mb-3" name="changeQuantity" placeholder="Quantity Change" required>
            <button type="submit" class="btn-submit">Submit Stock Update</button>
            <a href="page.php" class="btn btn-next">Home</a>
        </form>

        <div class="stock-update-list">
            <h3>Stock Update History</h3>
            <ul class="list-group">
                <?php while ($row = $stockUpdates->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($row['Update_ID']) ?> | <?= htmlspecialchars($row['Product_Name']) ?> | <?= htmlspecialchars($row['Change_Type']) ?> | <?= htmlspecialchars($row['Change_Quantity']) ?> | <?= htmlspecialchars($row['Update_Date']) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</body>
</html>