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
    $customerName = trim($_POST['customerName']);
    $email = trim($_POST['customerEmail']);
    $address = trim($_POST['Address']);
    $phone = trim($_POST['customerPhone']);

    if (!empty($customerName) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($address) && preg_match('/^[0-9]{10}$/', $phone)) {
        // Prevent duplicate emails
        $checkQuery = "SELECT COUNT(*) FROM Customers WHERE Email = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            $message = "❌ Error: Email already exists!";
        } else {
            $sql = "INSERT INTO Customers (Name, Email, Address, Phone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $customerName, $email, $address, $phone);

            if ($stmt->execute()) {
                $message = "✅ Customer added successfully!";
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "❌ Invalid input. Ensure all fields are correctly filled, and email format is valid.";
    }
}

// Retrieve customer list
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'Customer_ID DESC';
$customers = $conn->query("SELECT * FROM Customers WHERE Name LIKE '%$searchQuery%' ORDER BY $sortOrder LIMIT 10");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customer Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script src="scripts.js" defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #2b5876, #4e4376);
            color: white;
        }

        /* Navbar Styling */
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

        .navbar-nav .nav-item {
            margin-right: 15px;
        }

        .nav-link {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ffffff !important;
            transition: color 0.3s ease-in-out;
        }

        .nav-link:hover {
            color: #f39c12 !important;
            transform: scale(1.05);
        }

        .navbar-toggler {
            background-color: #ffffff;
            border-radius: 5px;
        }

        /* Submit & Next Buttons */
        .btn-submit, .btn-next {
            display: block;
            width: 100%;
            padding: 12px;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
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
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.4);
            filter: brightness(1.1);
        }

        /* Customer List Styling */
        .customer-list {
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .customer-list h3 {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
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
        <h2>Customer Management</h2>
        <form method="POST">
            <input type="text" class="form-control mb-3" name="customerName" placeholder="Customer Name" required>
            <input type="email" class="form-control mb-3" name="customerEmail" placeholder="Email Address" required>
            <input type="text" class="form-control mb-3" name="Address" placeholder="Full Address" required>
            <input type="text" class="form-control mb-3" name="customerPhone" placeholder="Phone Number" required>
            <button type="submit" class="btn-submit">Submit</button>
            <a href="page.php" class="btn btn-next">Home</a>
        </form>

        <!-- Enhanced Customer List -->
        <div class="customer-list">
            <h3>Customer List</h3>
            <ul class="list-group">
                <?php while ($row = $customers->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($row['Customer_ID']) ?> | <?= htmlspecialchars($row['Name']) ?> | <?= htmlspecialchars($row['Email']) ?> | <?= htmlspecialchars($row['Phone']) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</body>
</html>