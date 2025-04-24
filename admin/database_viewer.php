<?php
require_once '../includes/config.php';

// Verify admin access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Function to get all tables from database
function getAllTables($conn) {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    return $tables;
}

// Function to get table data with pagination
function getTableData($conn, $table, $page = 1, $perPage = 10) {
    $offset = ($page - 1) * $perPage;
    
    // Get total rows
    $totalResult = $conn->query("SELECT COUNT(*) FROM $table");
    $totalRows = $totalResult->fetch_row()[0];
    $totalPages = ceil($totalRows / $perPage);
    
    // Get column names
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM $table");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Get data
    $data = [];
    $result = $conn->query("SELECT * FROM $table LIMIT $perPage OFFSET $offset");
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return [
        'columns' => $columns,
        'data' => $data,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'totalRows' => $totalRows
    ];
}

// Get current table (default to first table)
$tables = getAllTables($conn);
$currentTable = $_GET['table'] ?? $tables[0] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get table data if table exists
$tableData = [];
if (in_array($currentTable, $tables)) {
    $tableData = getTableData($conn, $currentTable, $page);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .database-container {
            margin: 20px;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .table-selector {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        .pagination {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .pagination a.active {
            background: #3498db;
            color: white;
        }
        .table-info {
            margin-bottom: 15px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <h1>Database Viewer</h1>
        
        <div class="database-container">
            <!-- Table selector dropdown -->
            <select class="table-selector" onchange="window.location.href='?table='+this.value">
                <?php foreach ($tables as $table): ?>
                    <option value="<?= htmlspecialchars($table) ?>" 
                        <?= $table === $currentTable ? 'selected' : '' ?>>
                        <?= htmlspecialchars($table) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if (!empty($tableData)): ?>
                <div class="table-info">
                    Showing <?= count($tableData['data']) ?> of <?= $tableData['totalRows'] ?> rows
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php foreach ($tableData['columns'] as $column): ?>
                                    <th><?= htmlspecialchars($column) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableData['data'] as $row): ?>
                                <tr>
                                    <?php foreach ($tableData['columns'] as $column): ?>
                                        <td>
                                            <?php 
                                            $value = $row[$column] ?? null;
                                            if (is_string($value) && strlen($value) > 50) {
                                                echo htmlspecialchars(substr($value, 0, 50)) . '...';
                                            } else {
                                                echo htmlspecialchars($value);
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($tableData['totalPages'] > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?table=<?= $currentTable ?>&page=<?= $page - 1 ?>">Previous</a>
                        <?php endif; ?>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($tableData['totalPages'], $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++): ?>
                            <a href="?table=<?= $currentTable ?>&page=<?= $i ?>" 
                               class="<?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $tableData['totalPages']): ?>
                            <a href="?table=<?= $currentTable ?>&page=<?= $page + 1 ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No tables found in the database or no data available in the selected table.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/admin_footer.php'; ?>
</body>
</html>