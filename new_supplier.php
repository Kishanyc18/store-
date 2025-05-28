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
    $supplierName = trim($_POST['supplierName']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    if (!empty($supplierName) && preg_match('/^[0-9]{10}$/', $contact) && !empty($address)) {
        // Prevent duplicate entries
        $checkQuery = "SELECT COUNT(*) FROM Suppliers WHERE Name = ? AND Contact = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ss", $supplierName, $contact);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            $message = "❌ Error: Supplier already exists!";
        } else {
            $sql = "INSERT INTO Suppliers (Name, Contact, Address) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $supplierName, $contact, $address);

            if ($stmt->execute()) {
                $message = "✅ Supplier added successfully!";
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "❌ Invalid input. Ensure all fields are correctly filled, and contact format is valid.";
    }
}

// Retrieve supplier list
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'Supplier_ID DESC';
$suppliers = $conn->query("SELECT * FROM Suppliers WHERE Name LIKE '%$searchQuery%' ORDER BY $sortOrder LIMIT 10");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Supplier Management</title>
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

        .supplier-list {
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
        <h2>Supplier Management</h2>
        <form method="POST">
            <input type="text" class="form-control mb-3" name="supplierName" placeholder="Supplier Name" required>
            <input type="text" class="form-control mb-3" name="contact" placeholder="Contact Info" required>
            <input type="text" class="form-control mb-3" name="address" placeholder="Full Address" required>
            <button type="submit" class="btn-submit">Submit</button>
            <a href="page.php" class="btn btn-next">Home</a>
        </form>

        <div class="supplier-list">
            <h3>Supplier List</h3>
            <ul class="list-group">
                <?php while ($row = $suppliers->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($row['Supplier_ID']) ?> | <?= htmlspecialchars($row['Name']) ?> | <?= htmlspecialchars($row['Contact']) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</body>
</html>