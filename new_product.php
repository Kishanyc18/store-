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
    $productName = trim($_POST['productName']);
    $category = trim($_POST['productCategory']);
    $price = floatval($_POST['productPrice']);
    $quantity = intval($_POST['productQuantity']);
    $supplierID = intval($_POST['supplierID']);

    if (!empty($productName) && !empty($category) && $price > 0 && $quantity >= 0 && $supplierID > 0) {
        // Prevent duplicate products
        $checkQuery = "SELECT COUNT(*) FROM Products WHERE Name = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $productName);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            $message = "❌ Error: Product already exists!";
        } else {
            $sql = "INSERT INTO Products (Name, Category, Price, Quantity, Supplier_ID) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdii", $productName, $category, $price, $quantity, $supplierID);

            if ($stmt->execute()) {
                $message = "✅ Product added successfully!";
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "❌ Invalid input. Ensure all fields are correctly filled.";
    }
}

// Retrieve supplier list dynamically
$suppliers = $conn->query("SELECT Supplier_ID, Name FROM Suppliers");

// Retrieve product list
$products = $conn->query("SELECT Products.*, Suppliers.Name AS Supplier_Name FROM Products 
                          JOIN Suppliers ON Products.Supplier_ID = Suppliers.Supplier_ID 
                          ORDER BY Product_ID DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Product Management</title>
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

        .btn-submit, .btn-home {
            width: 100%;
            padding: 12px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 8px;
            text-align: center;
            display: block;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
        }

        .btn-submit {
            background: linear-gradient(to right, #28a745, #218838);
            color: white;
        }

        .btn-home {
            background: linear-gradient(to right, #f39c12, #e67e22);
            color: white;
            margin-top: 15px;
        }

        .btn-submit:hover, .btn-home:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .product-list {
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
        }

        .list-group-item {
            padding: 15px;
            font-size: 1.2rem;
            border-radius: 8px;
            color: white;
            transition: transform 0.3s ease-in-out;
        }

        .list-group-item:nth-child(odd) {
            background: #3498db;
        }

        .list-group-item:nth-child(even) {
            background: #2ecc71;
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

    <div class="container mt-4">
        <h2>Product Management</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" class="form-control mb-3" name="productName" placeholder="Product Name" required>
            <select class="form-control mb-3" name="productCategory" required>
                <option value="" disabled selected>Select a Category</option>
                <option value="Electronics">Electronics</option>
                <option value="Clothing">Clothing</option>
                <option value="Home Appliances">Home Appliances</option>
                <option value="Books">Books</option>
                <option value="Furniture">Furniture</option>
                <option value="Toys">Toys</option>
            </select>
            <input type="number" class="form-control mb-3" name="productPrice" placeholder="Price" required>
            <input type="number" class="form-control mb-3" name="productQuantity" placeholder="Quantity" required>
            <select class="form-control mb-3" name="supplierID" required>
                <option value="" disabled selected>Select Supplier</option>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($supplier['Supplier_ID']) ?>"><?= htmlspecialchars($supplier['Name']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn-submit">Submit</button>
            <a href="page.php" class="btn-home">Home</a>
        </form>
         <div class="product-list mt-4">
            <h3>Product List</h3>
            <ul class="list-group">
                <?php while ($row = $products->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($row['Name']) ?> | ₹<?= htmlspecialchars($row['Price']) ?> | Qty: <?= htmlspecialchars($row['Quantity']) ?> | Supplier: <?= htmlspecialchars($row['Supplier_Name']) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

    </div>
</body>
</html>