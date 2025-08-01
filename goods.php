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
    <link rel="stylesheet" href="styles/goods.css">
</head>
<body>
<div class="topnav">
    <a href="1portofolio.php">Home</a>
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