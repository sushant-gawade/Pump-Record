<?php
// =================================================================
// PHP LOGIC: DATABASE CONNECTION, DATA FETCHING, AND CALCULATIONS
// =================================================================

// Include the database connection file
require_once 'db_connect.php';

// Check if profile ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: index.php");
    exit;
}

$profile_id = intval($_GET['id']);
$profile = null;
$transactions = [];
$status_message = '';

// --- 1. Handle Delete Profile Action ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_profile'])) {
    // ON DELETE CASCADE in the database handles deleting transactions automatically
    $sql_delete = "DELETE FROM profiles WHERE id = ?";
    if ($stmt = $conn->prepare($sql_delete)) {
        $stmt->bind_param("i", $profile_id);
        if ($stmt->execute()) {
            header("location: index.php?status=deleted");
            exit;
        } else {
            $status_message = "<div class='status-error'>Error deleting profile: " . htmlspecialchars($conn->error) . "</div>";
        }
        $stmt->close();
    }
}

// --- 2. Fetch Profile Details ---
$sql_profile = "SELECT id, name, mobile FROM profiles WHERE id = ?";
if ($stmt = $conn->prepare($sql_profile)) {
    $stmt->bind_param("i", $profile_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $profile = $result->fetch_assoc();
        } else {
            // Profile not found
            header("location: index.php");
            exit;
        }
    }
    $stmt->close();
}

// --- 3. Calculate Financial Summary (Credit, Debit, Balance) ---
$sql_summary = "
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) AS total_credit,
        COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) AS total_debit
    FROM 
        transactions 
    WHERE 
        profile_id = ?
";
$total_credit = $total_debit = $balance = 0;

if ($stmt = $conn->prepare($sql_summary)) {
    $stmt->bind_param("i", $profile_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $total_credit = $row['total_credit'];
            $total_debit = $row['total_debit'];
            $balance = $total_credit - $total_debit;
            // REMOVED DEBUG MESSAGE HERE
        }
    }
    $stmt->close();
}
// REMOVED SQL PREPARE FAIL DEBUG MESSAGE HERE

// --- 4. Fetch Transaction History ---
$sql_history = "SELECT id, transaction_date, type, amount, payment_method, note FROM transactions WHERE profile_id = ? ORDER BY transaction_date DESC, created_at DESC";
if ($stmt = $conn->prepare($sql_history)) {
    $stmt->bind_param("i", $profile_id);
    if ($stmt->execute()) {
        $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

// Check for status messages from redirects
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $status_message = "<div class='status-success'>Transaction recorded successfully!</div>";
    }
    if ($_GET['status'] == 'trans_deleted') {
        $status_message = "<div class='status-success'>Transaction deleted successfully!</div>";
    }
    if ($_GET['status'] == 'trans_updated') {
        $status_message = "<div class='status-success'>Transaction updated successfully!</div>";
    }
}

// =================================================================
// HTML STRUCTURE AND PRESENTATION
// =================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile: <?php echo htmlspecialchars($profile['name']); ?></title>
    <style>
        /* CSS Styling - Minor adjustments to remove debug styling */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .status-success, .status-error { padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid; }
        .status-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .status-error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        .profile-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
        .profile-info h2 { margin: 0 0 5px 0; font-size: 2em; color: #007bff; }
        .profile-info p { color: #6c757d; margin: 0; font-size: 1.1em; }
        .delete-btn { background-color: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; transition: background-color 0.3s; font-weight: 600; }
        
        /* Summary Cards */
        .summary-cards { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { flex: 1; padding: 20px; border-radius: 8px; color: white; text-align: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .card-credit { background-color: #17a2b8; } 
        .card-debit { background-color: #ffc107; color: #333; } 
        .card-balance-pos { background-color: #28a745; }
        .card-balance-neg { background-color: #dc3545; }
        .card h3 { margin: 0 0 5px 0; font-size: 1em; opacity: 0.9; }
        .card p { font-size: 1.8em; font-weight: bold; margin: 0; }
        
        /* History Table */
        .history-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .history-table th, .history-table td { padding: 12px 15px; border-bottom: 1px solid #e9ecef; text-align: left; }
        .history-table th { background-color: #f8f9fa; font-weight: 600; color: #555; }
        .history-table tbody tr:hover { background-color: #f5f5f5; }
        .trans-credit { color: #28a745; font-weight: bold; }
        .trans-debit { color: #dc3545; font-weight: bold; }
        
        /* Action Buttons */
        .action-buttons { margin-bottom: 30px; }
        .payment-btn { 
            background-color: #007bff; 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold; 
            display: inline-block; 
            text-decoration: none;
            margin-right: 10px;
        }
        .edit-btn, .delete-trans-btn { padding: 5px 8px; border-radius: 4px; border: none; cursor: pointer; margin-right: 5px; font-size: 0.9em; text-decoration: none; }
        .edit-btn { background-color: #ffc107; color: #333; }
        .delete-trans-btn { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <p><a href="index.php">← Back to Dashboard</a></p>
        
        <?php echo $status_message; ?>
        <div class="profile-header">
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($profile['name']); ?></h2>
                <p>Mobile: <?php echo htmlspecialchars($profile['mobile']); ?></p>
            </div>
            <form method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete this profile and ALL associated transactions? This cannot be undone.')">
                <input type="hidden" name="profile_id" value="<?php echo $profile_id; ?>">
                <button type="submit" name="delete_profile" class="delete-btn">❌ Delete Profile</button>
            </form>
        </div>

        <div class="summary-cards">
            <div class="card card-credit">
                <h3>Total Credit (Money In / Received)</h3>
                <p>₹ <?php echo number_format($total_credit, 2); ?></p>
            </div>
            <div class="card card-debit">
                <h3>Total Debit (Money Out / Paid)</h3>
                <p>₹ <?php echo number_format($total_debit, 2); ?></p>
            </div>
            <div class="card <?php echo ($balance >= 0) ? 'card-balance-pos' : 'card-balance-neg'; ?>">
                <h3>Net Balance</h3>
                <p>₹ <?php echo number_format(abs($balance), 2); ?></p>
                <small>
                    <?php 
                        if ($balance >= 0) {
                            echo 'User Owes You (Receivable)';
                        } else {
                            echo 'You Owe User (Payable)';
                        }
                    ?>
                </small>
            </div>
        </div>

        <div class="action-buttons">
            <a href="add_transaction.php?id=<?php echo $profile_id; ?>" class="payment-btn">
                ➕ Add New Transaction
            </a>
            <a href="backup_transaction_history.php?id=<?php echo $profile_id; ?>" class="payment-btn" style="background-color: #6c757d;">⬇️ Backup History (PDF)</a>
        </div>
        
        <h3>Transaction History</h3>
        <table class="history-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 8%;">Type</th>
                    <th style="width: 15%;">Amount (₹)</th>
                    <th style="width: 15%;">Payment Method</th>
                    <th style="width: 30%;">Note</th>
                    <th style="width: 20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?php echo date('Y-m-d', strtotime($t['transaction_date'])); ?></td>
                            
                            <td class="<?php echo ($t['type'] == 'credit') ? 'trans-credit' : 'trans-debit'; ?>">
                                <?php echo ucfirst(htmlspecialchars($t['type'])); ?>
                            </td>
                            
                            <td>₹ <?php echo number_format($t['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($t['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars(substr($t['note'], 0, 50)) . (strlen($t['note']) > 50 ? '...' : ''); ?></td>
                            <td>
                                <a href="edit_transaction.php?id=<?php echo $t['id']; ?>" class="edit-btn">Edit</a>
                                <button class="delete-trans-btn" onclick="deleteTransaction(<?php echo $t['id']; ?>, <?php echo $profile_id; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6c757d; padding: 20px;">No transactions recorded for this profile.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function deleteTransaction(transactionId, profileId) {
            if (confirm('Are you sure you want to delete this specific transaction?')) {
                window.location.href = `delete_transaction.php?trans_id=${transactionId}&profile_id=${profileId}`;
            }
        }
    </script>
</body>
</html>