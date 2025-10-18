<?php
require_once 'includes/db_config.php';

$message = '';
$edit_id = $_GET['edit_id'] ?? null;
$stock_data = ['add_date' => date('Y-m-d'), 'liters_added' => '', 'notes' => ''];
$form_title = "➕ Add New Petrol Stock";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed. Please check includes/db_config.php.");
}

// =================================================================
// 1. Handle GET Request for Editing
// =================================================================
if ($edit_id) {
    $edit_id = intval($edit_id);
    $form_title = "✏️ Edit Stock Entry (ID: $edit_id)";
    
    $fetch_sql = "SELECT add_date, liters_added, notes FROM stock WHERE id = ?";
    $stmt_fetch = $conn->prepare($fetch_sql);
    $stmt_fetch->bind_param("i", $edit_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    
    if ($result->num_rows == 1) {
        $stock_data = $result->fetch_assoc();
    } else {
        $message = "<div class='alert alert-danger'>❌ Error: Stock entry not found.</div>";
        $edit_id = null; // Revert to Add mode if ID is invalid
    }
    $stmt_fetch->close();
}

// =================================================================
// 2. Handle POST Request (Add or Update)
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null;
    $date = $_POST['stock_date'];
    $new_liters = floatval($_POST['liters_added']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $prev_liters = 0; // Only used for UPDATE logic
    
    if ($new_liters <= 0) {
        $message = "<div class='alert alert-warning'>Please enter a valid amount of liters.</div>";
    } else {
        // Start transaction for data integrity
        $conn->begin_transaction();
        $success = false;
        
        try {
            if ($id) {
                // --- UPDATE LOGIC (Editing Existing Stock) ---
                
                // A. Get the original liters value
                $get_prev_sql = "SELECT liters_added FROM stock WHERE id = ?";
                $stmt_prev = $conn->prepare($get_prev_sql);
                $stmt_prev->bind_param("i", $id);
                $stmt_prev->execute();
                $result_prev = $stmt_prev->get_result();
                $prev_liters = $result_prev->fetch_assoc()['liters_added'] ?? 0;
                $stmt_prev->close();
                
                // B. Calculate the adjustment: difference between new value and old value
                $liters_difference = $new_liters - $prev_liters; 

                // C. Update the record in the stock table
                $sql_stock = "UPDATE stock SET add_date = ?, liters_added = ?, notes = ? WHERE id = ?";
                $stmt_stock = $conn->prepare($sql_stock);
                $stmt_stock->bind_param("sdsi", $date, $new_liters, $notes, $id);
                $stmt_stock->execute();

                // D. Update the total current stock (by adding the difference)
                // If liters_difference is positive, stock increases. If negative, stock decreases.
                $sql_config = "UPDATE config SET current_stock = current_stock + ?";
                $stmt_config = $conn->prepare($sql_config);
                $stmt_config->bind_param("d", $liters_difference);
                $stmt_config->execute();
                
                $message_text = "Stock entry updated successfully! Available stock adjusted by " . number_format($liters_difference, 2) . "L.";
                
            } else {
                // --- INSERT LOGIC (Adding New Stock) ---
                
                // A. Insert into stock history table
                $sql_stock = "INSERT INTO stock (add_date, liters_added, notes) VALUES (?, ?, ?)";
                $stmt_stock = $conn->prepare($sql_stock);
                $stmt_stock->bind_param("sds", $date, $new_liters, $notes);
                $stmt_stock->execute();
                
                // B. Update the total current stock
                $sql_config = "UPDATE config SET current_stock = current_stock + ? WHERE id = 1";
                $stmt_config = $conn->prepare($sql_config);
                $stmt_config->bind_param("d", $new_liters);
                $stmt_config->execute();
                
                $message_text = "Stock of " . number_format($new_liters, 2) . "L added successfully!";
            }

            // Commit transaction if all queries succeeded
            $conn->commit();
            $message = "<div class='alert alert-success'>✅ $message_text</div>";
            $success = true;
            
            // If successful update, redirect to history page
            if ($id) {
                 header("location: petrol_history.php");
                 exit;
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>❌ Error processing stock: " . $e->getMessage() . "</div>";
            // If insert failed, reload form with submitted data
            $stock_data = ['add_date' => $date, 'liters_added' => $new_liters, 'notes' => $notes];
            $edit_id = $id; 
        }

        if (isset($stmt_stock)) $stmt_stock->close();
        if (isset($stmt_config)) $stmt_config->close();
    }
}
// If POST failed or we are in Edit mode, ensure current data is displayed
if ($_SERVER["REQUEST_METHOD"] == "POST" && $edit_id) {
    // If edit failed, ensure the form title and ID are preserved
    $form_title = "✏️ Edit Stock Entry (ID: $edit_id)";
    $stock_data = ['add_date' => $_POST['stock_date'], 'liters_added' => $_POST['liters_added'], 'notes' => $_POST['notes']];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $form_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container mt-5">
    <a href="petrol_history.php" class="btn btn-secondary mb-3">⬅️ Back to Stock History</a>
    <h2 class="mb-4"><?php echo $form_title; ?></h2>
    <?php echo $message; ?>

    <form action="add_stock.php" method="POST">
        <?php if ($edit_id): ?>
            <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="stock_date" class="form-label">Date (Manual)</label>
            <input type="date" class="form-control" id="stock_date" name="stock_date" 
                   value="<?php echo htmlspecialchars($stock_data['add_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="liters_added" class="form-label">Stock Added (Liters)</label>
            <input type="number" step="0.01" class="form-control" id="liters_added" name="liters_added" 
                   placeholder="e.g., 5000.00" 
                   value="<?php echo htmlspecialchars($stock_data['liters_added']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes (Tanker ID, Supplier, etc.)</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($stock_data['notes']); ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-<?php echo $edit_id ? 'warning' : 'success'; ?> btn-lg">
            <?php echo $edit_id ? 'Update Stock Entry' : 'Add Stock'; ?>
        </button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
if (isset($conn)) {
    $conn->close();
}
?>