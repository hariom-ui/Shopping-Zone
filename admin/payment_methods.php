<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Add new payment method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $conn->query("INSERT INTO payment_methods (name, description, is_active) 
                 VALUES ('$name', '$description', $isActive)");
}

// Toggle activation
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE payment_methods SET is_active = NOT is_active WHERE id = $id");
    header("Location: payment_methods.php");
    exit();
}

$methods = $conn->query("SELECT * FROM payment_methods");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Payments MethodsS</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<?php include 'includes/admin_header.php'; ?>
    
    <main class="admin-container">

<h1>Payment Methods</h1>

<!-- Add New Method Form -->
<form method="POST" class="admin-form">
    <div class="form-group">
        <label>Name:</label>
        <input type="text" name="name" required>
    </div>
    <div class="form-group">
        <label>Description:</label>
        <textarea name="description" required></textarea>
    </div>
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_active" checked> Active
        </label>
    </div>
    <button type="submit" class="btn">Add Payment Method</button>
</form>

<!-- Methods List -->
<table class="admin-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($method = $methods->fetch_assoc()): ?>
    <tr>
        <td><?= $method['id'] ?></td>
        <td><?= htmlspecialchars($method['name']) ?></td>
        <td><?= htmlspecialchars($method['description']) ?></td>
        <td>
            <span class="status-<?= $method['is_active'] ? 'active' : 'inactive' ?>">
                <?= $method['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
        </td>
        <td>
            <a href="?toggle=<?= $method['id'] ?>" class="btn btn-sm">
                <?= $method['is_active'] ? 'Deactivate' : 'Activate' ?>
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</main>
    
    <?php include 'includes/admin_footer.php'; ?>
</body>
</html>