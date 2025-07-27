<?php
require_once 'dbconnection.php'; 

// Ensure $conn is set and is a valid mysqli object and connected
if (!isset($conn) || !$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn ? $conn->connect_error : 'No connection object.'));
}

// Add new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['item-name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    $stmt = $conn->prepare("INSERT INTO products (name, description, price) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssd", $name, $description, $price);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Fetch products
$result = $conn->query("SELECT * FROM products");
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods and Services</title>
    <style>
        :root {
            --falu-red: #6f1d1bff;
            --lion: #bb9457ff;
            --bistre: #432818ff;
            --brown: #99582aff;
            --peach: #ffe6a7ff;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--peach);
            margin: 0;
            padding: 0;
        }
        .topnav {
            background: var(--falu-red);
            overflow: hidden;
            display: flex;
            justify-content: center;
            gap: 18px;
            padding: 14px 0;
        }
        .topnav a {
            color: var(--peach);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .topnav a:hover {
            background: var(--lion);
            color: var(--bistre);
        }
        .header {
            background: var(--lion);
            color: var(--falu-red);
            text-align: center;
            padding: 32px 0 18px 0;
            border-bottom: 2px solid var(--brown);
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            max-width: 1100px;
            margin: 40px auto;
        }
        .content {
            flex: 2;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 18px rgba(67, 40, 24, 0.10);
            padding: 28px 24px;
            border: 2px solid var(--lion);
        }
        .content h2 {
            color: var(--falu-red);
            margin-bottom: 18px;
            font-size: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            background: var(--peach);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(67, 40, 24, 0.07);
        }
        th, td {
            padding: 14px 10px;
            text-align: left;
        }
        th {
            background: var(--lion);
            color: var(--bistre);
            font-weight: 600;
            border-bottom: 2px solid var(--brown);
        }
        td {
            border-bottom: 1px solid var(--brown);
            color: var(--bistre);
        }
        tr:last-child td {
            border-bottom: none;
        }
        .sidebar {
            flex: 1;
            background: var(--lion);
            border-radius: 10px;
            box-shadow: 0 4px 18px rgba(67, 40, 24, 0.10);
            padding: 28px 20px;
            border: 2px solid var(--brown);
            min-width: 260px;
        }
        .sidebar h2 {
            color: var(--falu-red);
            margin-bottom: 16px;
            font-size: 1.2rem;
            text-align: center;
        }
        .sidebar label {
            display: block;
            margin-bottom: 6px;
            color: var(--bistre);
            font-weight: 500;
        }
        .sidebar input[type="text"],
        .sidebar textarea,
        .sidebar input[type="number"] {
            width: 100%;
            padding: 9px;
            margin-bottom: 14px;
            border-radius: 6px;
            border: 1.5px solid var(--brown);
            background: var(--peach);
            font-size: 1rem;
            color: var(--bistre);
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .sidebar input:focus,
        .sidebar textarea:focus {
            border-color: var(--falu-red);
            outline: none;
        }
        .sidebar button {
            width: 100%;
            padding: 11px;
            background: linear-gradient(90deg, var(--falu-red), var(--brown));
            color: var(--peach);
            border: none;
            border-radius: 6px;
            font-size: 1.05rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(67, 40, 24, 0.10);
            transition: background 0.2s, transform 0.1s;
        }
        .sidebar button:hover {
            background: linear-gradient(90deg, var(--brown), var(--falu-red));
            transform: translateY(-2px) scale(1.03);
        }
        .footer {
            background: var(--falu-red);
            color: var(--peach);
            text-align: center;
            padding: 18px 0;
            margin-top: 40px;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        @media (max-width: 900px) {
            .row {
                flex-direction: column;
                gap: 18px;
            }
            .sidebar, .content {
                min-width: unset;
            }
        }
    </style>
</head>
<body>
<div class="topnav">
    <a href="adristosportofolio.html">Home</a>
    <a href="#about">About</a>
    <a href="contact1.php">Contact</a>
    <a href="signin.php">Login</a>
    <a href="signup.php">Register</a>
    </div>
<table>
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Description</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td>$<?= number_format($row['price'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<div class="header">
    <h1>Our Goods and Services</h1>
</div>
<div class="row">
    <div class="content">
        <h2>Available Goods and Services</h2>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Product 1</td>
                    <td>Water-resistant Rhode Phone Case</td>
                    <td>$105.50</td>
                </tr>
                <tr>
                    <td>Product 2</td>
                    <td>Black Prada Suitcase</td>
                    <td>$155.00</td>
                </tr>
                <tr>
                    <td>Product 3</td>
                    <td>Blue Vizmo Keys with a unique tracker</td>
                    <td>$250.00</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="sidebar">
        <h2>Add New Goods or Services</h2>
        <form action="#" method="post">
            <label for="item-name">Item Name:</label>
            <input type="text" id="item-name" name="item-name" required>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" required>
            <button type="submit">Add Item</button>
        </form>
    </div>
</div>

<div class="footer">
    <p>© 2025 Your Company Name. All rights reserved.</p>
</div>

</body>
</html>