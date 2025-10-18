<?php
require_once 'includes/db_config.php';

$message = '';
$edit_id = $_GET['edit_id'] ?? null;
$form_title = "⛽ Daily Nozzle Readings";

// Default form values (used for new entry or if fetching fails)
$reading_data = [
    'reading_date' => date('Y-m-d'), 
    'n1_open' => '', 'n1_close' => '', 
    'n2_open' => '', 'n2_close' => '', 
    'notes' => ''
];

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed. Please check includes/db_config.php.");
}

// =================================================================
// 1. Handle GET Request for Editing
// =================================================================
if ($edit_id) {
    $edit_id = intval($edit_id);
    $form_title = "✏️ Edit Nozzle Reading (ID: $edit_id)";
    
    $fetch_sql = "SELECT reading_date, n1_open, n1_close, n2_open, n2_close, notes FROM nozzle_readings WHERE id = ?";
    $stmt_fetch = $conn->prepare($fetch_sql);
    $stmt_fetch->bind_param("i", $edit_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    
    if ($result->num_rows == 1) {
        $reading_data = $result->fetch_assoc();
    } else {
        $message = "<div class='alert alert-danger'>❌ Error: Reading entry not found.</div>";
        $edit_id = null; // Revert to Add mode if ID is invalid
    }
    $stmt_fetch->close();
}


// =================================================================
// 2. Handle POST Request (Add or Update)
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null; // ID will be present for update
    $date = $_POST['reading_date'];
    $n1_open = floatval($_POST['n1_open']);
    $n1_close = floatval($_POST['n1_close']);
    $n2_open = floatval($_POST['n2_open']);
    $n2_close = floatval($_POST['n2_close']);
    $notes = $conn->real_escape_string($_POST['notes']);

    $new_dispensed = ($n1_close - $n1_open) + ($n2_close - $n2_open);

    if ($new_dispensed < 0) {
        $message = "<div class='alert alert-danger'>❌ Error: Closing reading must be greater than or equal to opening reading.</div>";
    } else {
        $conn->begin_transaction();
        $is_update = false;
        $prev_dispensed = 0;
        
        try {
            if ($id) {
                // --- UPDATE LOGIC (Editing Existing Reading) ---
                $is_update = true;

                // A. Get the original dispensed value
                $get_prev_sql = "SELECT (n1_close - n1_open) + (n2_close - n2_open) AS dispensed FROM nozzle_readings WHERE id = ?";
                $stmt_prev = $conn->prepare($get_prev_sql);
                $stmt_prev->bind_param("i", $id);
                $stmt_prev->execute();
                $result_prev = $stmt_prev->get_result();
                $prev_dispensed = $result_prev->fetch_assoc()['dispensed'] ?? 0;
                $stmt_prev->close();
                
                // B. Update the readings record
                $sql_readings = "
                    UPDATE nozzle_readings SET 
                        reading_date = ?, n1_open = ?, n1_close = ?, n2_open = ?, n2_close = ?, notes = ? 
                    WHERE id = ?
                ";
                $stmt_readings = $conn->prepare($sql_readings);
                $stmt_readings->bind_param("sddddsi", $date, $n1_open, $n1_close, $n2_open, $n2_close, $notes, $id);
                $stmt_readings->execute();

            } else {
                // --- INSERT LOGIC (Adding New Reading or Updating Duplicate Date) ---
                
                // Check if date already exists (to handle the ON DUPLICATE case which doesn't use ID)
                $check_sql = "SELECT (n1_close - n1_open) + (n2_close - n2_open) AS dispensed FROM nozzle_readings WHERE reading_date = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("s", $date);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                     $is_update = true;
                     $prev_dispensed = $check_result->fetch_assoc()['dispensed'];
                }
                $check_stmt->close();
                
                // Use INSERT ... ON DUPLICATE KEY UPDATE
                $sql_readings = "
                    INSERT INTO nozzle_readings (reading_date, n1_open, n1_close, n2_open, n2_close, notes)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        n1_open = VALUES(n1_open), n1_close = VALUES(n1_close),
                        n2_open = VALUES(n2_open), n2_close = VALUES(n2_close),
                        notes = VALUES(notes)
                ";
                $stmt_readings = $conn->prepare($sql_readings);
                $stmt_readings->bind_param("sdddds", $date, $n1_open, $n1_close, $n2_open, $n2_close, $notes);
                $stmt_readings->execute();
            }

            // --- COMMON STOCK ADJUSTMENT LOGIC ---
            
            // Adjustment = (Old dispensed amount ADDED BACK) - (New dispensed amount SUBTRACTED)
            // If it's a new entry, prev_dispensed is 0, so adjustment = 0 - new_dispensed (subtraction)
            $stock_adjustment = $prev_dispensed - $new_dispensed;
            
            $sql_update_stock = "UPDATE config SET current_stock = current_stock + ?";
            $stmt_update = $conn->prepare($sql_update_stock);
            $stmt_update->bind_param("d", $stock_adjustment);
            $stmt_update->execute();

            $conn->commit();
            
            $status = $is_update ? 'Updated' : 'Saved';
            $message = "<div class='alert alert-success'>✅ Readings $status. **" . number_format($new_dispensed, 2) . "L** dispensed today. Available stock adjusted.</div>";

            // If successful, redirect to the main index page to view the list
            header("location: index.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>❌ Error processing readings: " . $e->getMessage() . "</div>";
            // If POST failed, reload form with submitted data
            $reading_data = ['reading_date' => $date, 'n1_open' => $n1_open, 'n1_close' => $n1_close, 'n2_open' => $n2_open, 'n2_close' => $n2_close, 'notes' => $notes];
            $edit_id = $id; 
            $form_title = $edit_id ? "✏️ Edit Nozzle Reading (ID: $edit_id)" : "⛽ Daily Nozzle Readings";
        }

        if (isset($stmt_readings)) $stmt_readings->close();
        if (isset($stmt_update)) $stmt_update->close();
    }
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
    <a href="index.php" class="btn btn-secondary mb-3">⬅️ Back to Dashboard</a>
    <h2 class="mb-4"><?php echo $form_title; ?></h2>
    <?php echo $message; ?>

    <form action="add_readings.php" method="POST">
        <?php if ($edit_id): ?>
            <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="reading_date" class="form-label">Date (Manual)</label>
            <input type="date" class="form-control" id="reading_date" name="reading_date" 
                   value="<?php echo htmlspecialchars($reading_data['reading_date']); ?>" 
                   <?php echo $edit_id ? 'readonly' : ''; ?> required>
            <?php if ($edit_id): ?>
                <div class="form-text">Date cannot be changed during edit. Please delete and re-add if needed.</div>
            <?php endif; ?>
        </div>

        <div class="card p-3 mb-3 bg-light">
            <h4>Nozzle 1</h4>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="n1_open" class="form-label">Day Opening Reading</label>
                    <input type="number" step="0.01" class="form-control" id="n1_open" name="n1_open" 
                           value="<?php echo htmlspecialchars($reading_data['n1_open']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="n1_close" class="form-label">Day End Reading</label>
                    <input type="number" step="0.01" class="form-control" id="n1_close" name="n1_close" 
                           value="<?php echo htmlspecialchars($reading_data['n1_close']); ?>" required>
                </div>
            </div>
        </div>

        <div class="card p-3 mb-3 bg-light">
            <h4>Nozzle 2</h4>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="n2_open" class="form-label">Day Opening Reading</label>
                    <input type="number" step="0.01" class="form-control" id="n2_open" name="n2_open" 
                           value="<?php echo htmlspecialchars($reading_data['n2_open']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="n2_close" class="form-label">Day End Reading</label>
                    <input type="number" step="0.01" class="form-control" id="n2_close" name="n2_close" 
                           value="<?php echo htmlspecialchars($reading_data['n2_close']); ?>" required>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes (Shift details, etc.)</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($reading_data['notes']); ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-<?php echo $edit_id ? 'warning' : 'primary'; ?> btn-lg">
            <?php echo $edit_id ? 'Update Readings' : 'Submit Readings'; ?>
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