<?php
$pages = [
    "page.php" => ["Home", "fa-home", "#3498db"], // Blue
    "new_customer.php" => ["Customers", "fa-user", "#e67e22"], // Orange
    "new_supplier.php" => ["Suppliers", "fa-truck", "#2ecc71"], // Green
    "new_product.php" => ["Products", "fa-box", "#9b59b6"], // Purple
    "new_order.php" => ["Orders", "fa-shopping-cart", "#e74c3c"], // Red
    "new_stock.php" => ["Stock Update", "fa-warehouse", "#f39c12"] // Yellow
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>StoreDB Inventory System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="scripts.js" defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #2b5876, #4e4376); /* Royal Blue & Purple */
            color: white;
        }

        nav {
            background-color: #000000;
        }

        .navbar-brand, .nav-link {
            color: white !important;
            font-weight: bold;
        }

        .nav-link:hover {
            color: #28a745 !important;
            transition: 0.3s ease-in-out;
        }

        .hero-section {
            height: 80vh;
            background: url('hero-bg.png') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            color: white;
        }

        .overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.6);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-content h1 {
            font-size: 4rem;
            font-weight: bold;
            animation: fadeInDown 1s ease-in-out;
        }

        .hero-content p {
            font-size: 1.3rem;
            animation: fadeInUp 1.2s ease-in-out;
        }

        @keyframes fadeInDown {
            0% { opacity: 0; transform: translateY(-50px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(50px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Navigation Boxes Section */
        .navigation-boxes {
            padding: 50px 0;
            text-align: center;
        }

        .nav-box {
            display: block;
            padding: 20px;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out, background 0.3s ease-in-out;
            text-align: center;
            margin: 15px auto;
            max-width: 250px;
        }

        .nav-box:hover {
            transform: scale(1.05);
        }

        .nav-box i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><i class="fas fa-store"></i> StoreDB</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php foreach ($pages as $link => $details): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars($link) ?>">
                                    <i class="fas <?= htmlspecialchars($details[1]) ?>"></i> <?= htmlspecialchars($details[0]) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero-section">
            <div class="overlay"></div>
            <div class="hero-content">
                <h1>Welcome to StoreDB</h1>
                <p>Manage your store efficiently with our inventory system</p>
            </div>
        </section>

        <!-- Navigation Boxes Below the Background -->
        <section class="navigation-boxes">
            <div class="container">
                <div class="row justify-content-center">
                    <?php foreach ($pages as $link => $details): ?>
                        <div class="col-md-4">
                            <a href="<?= htmlspecialchars($link) ?>" class="nav-box" style="background-color: <?= htmlspecialchars($details[2]) ?>;">
                                <i class="fas <?= htmlspecialchars($details[1]) ?>"></i>
                                <p><?= htmlspecialchars($details[0]) ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>