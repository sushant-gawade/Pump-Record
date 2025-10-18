<?php
require_once 'includes/db_config.php';

// Fetch available stock
$stock_query = "SELECT current_stock FROM config WHERE id = 1";
$stock_result = $conn->query($stock_query);
$current_stock = $stock_result ? ($stock_result->fetch_assoc()['current_stock'] ?? 0) : 0;

// Fetch latest nozzle readings for display and calculation
$list_query = "
    SELECT
        reading_date,
        (n1_close - n1_open) AS n1_dispensed,
        (n2_close - n2_open) AS n2_dispensed,
        ((n1_close - n1_open) + (n2_close - n2_open)) AS total_dispensed,
        id
    FROM nozzle_readings
    ORDER BY reading_date DESC
    LIMIT 10
";
$list_result = $conn->query($list_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⛽ Diesel Pump Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4 text-center">⛽ Diesel Management Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-info text-white text-center">
                <div class="card-body">
                    <h5 class="card-title">Available Diesel Stock</h5>
                    <p class="card-text display-4"><?php echo number_format($current_stock, 2); ?> L</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 d-grid">
            <a href="add_stock.php" class="btn btn-success btn-lg my-2">➕ Add Diesel Stock</a>
        </div>

        <div class="col-md-4 d-grid">
            <a href="diesel_history.php" class="btn btn-warning btn-lg my-2">📜 Diesel History</a>
        </div>
    </div>

    <hr>

    <div class="row mb-5 align-items-center">
        <div class="col-md-4 d-grid order-md-1 order-3 mt-3 mt-md-0">
            <a href="report_generator.php?type=nozzle_list" target="_blank" class="btn btn-secondary btn-sm">💾 Backup Nozzle List (PDF)</a>
        </div>

        <div class="col-md-4 d-grid order-md-2 order-2">
            <a href="add_readings.php" class="btn btn-primary btn-lg">⛽ Nozzle Readings</a>
        </div>

        <div class="col-md-4 order-md-3 order-1">
            <form class="d-flex" action="index.php" method="GET">
                <input type="date" name="search_date" class="form-control me-2" required>
                <button class="btn btn-outline-dark" type="submit">🔍 Search</button>
            </form>
        </div>
    </div>

    <hr>

    <h2 class="mb-3">Daily Nozzle Reading & Dispensed Diesel</h2>
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Nozzle 1 Disp. (L)</th>
                <th>Nozzle 2 Disp. (L)</th>
                <th>Total Disp. (L)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($list_result && $list_result->num_rows > 0) {
                while($row = $list_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['reading_date']) . "</td>";
                    echo "<td>" . number_format($row['n1_dispensed'], 2) . "</td>";
                    echo "<td>" . number_format($row['n2_dispensed'], 2) . "</td>";
                    echo "<td><strong>" . number_format($row['total_dispensed'], 2) . "</strong></td>";
                    echo '<td>
                            <a href="add_readings.php?edit_id=' . $row['id'] . '" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="delete_logic.php?type=nozzle&id=' . $row['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Are you sure you want to delete this reading? This will affect stock calculations.\')">Delete</a>
                          </td>';
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No nozzle readings recorded yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
if (isset($conn)) {
    $conn->close();
} 
?>