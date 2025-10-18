<?php
require_once 'includes/db_config.php';

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}

// Prepare the base query
$history_query = "SELECT * FROM stock";
$where_clause = '';
$search_date = $_GET['search_date'] ?? '';

// Handle Date Search
if (!empty($search_date)) {
    $safe_date = $conn->real_escape_string($search_date);
    $where_clause = " WHERE add_date = '$safe_date'";
}

// Finalize query
$history_query .= $where_clause . " ORDER BY add_date DESC";
$history_result = $conn->query($history_query);

// Calculate total stock ever added
$total_query = "SELECT SUM(liters_added) AS total_added FROM stock";
$total_result = $conn->query($total_query);
$total_added = $total_result->fetch_assoc()['total_added'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📜 Stock History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">⬅️ Back to Dashboard</a>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📜 Stock Addition History</h2>
        <h4 class="text-success">Total Stock Added: **<?php echo number_format($total_added, 2); ?> L**</h4>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <form class="d-flex" action="petrol_history.php" method="GET">
                <input type="date" name="search_date" class="form-control me-2" value="<?php echo htmlspecialchars($search_date); ?>">
                <button class="btn btn-outline-dark" type="submit">🔍 Search</button>
            </form>
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            <a href="report_generator.php?type=stock_history" target="_blank" class="btn btn-danger">💾 Backup All Stock History (PDF)</a>
        </div>
    </div>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Date Added</th>
                <th>Liters Added (L)</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($history_result && $history_result->num_rows > 0) {
                while($row = $history_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['add_date']) . "</td>";
                    echo "<td><strong>" . number_format($row['liters_added'], 2) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                    echo '<td>
                            <a href="add_stock.php?edit_id=' . $row['id'] . '" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="delete_logic.php?type=stock&id=' . $row['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Are you sure you want to DELETE this stock entry? This will decrease the Available Stock.\')">Delete</a>
                          </td>';
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No stock additions recorded yet or for the search date.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>