<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<header class="admin-header">
        <div class="container">
            <div class="logo">
                <a href="dashboard.php"><?php echo SITE_NAME; ?> Admin</a>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
</body>
</html>