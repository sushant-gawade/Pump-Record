<?php
// =================================================================
// PHP LOGIC: DATABASE CONNECTION AND FINANCIAL CALCULATIONS
// =================================================================

// Include the database connection file
require_once 'db_connect.php';

// Initialize variables
$profiles = [];
$overall_collected = 0; // Sum of all positive balances (Money owed TO YOU)
$overall_pending = 0;   // Sum of all negative balances (Money owed BY YOU / Advance)
$error_message = "";

// SQL Query to fetch ALL profiles and calculate their summary Credit/Debit/Balance
// COALESCE is used to ensure that profiles with no transactions return 0 instead of NULL.
$sql = "
    SELECT 
        p.id, 
        p.name, 
        p.mobile,
        COALESCE(SUM(CASE WHEN t.type = 'credit' THEN t.amount ELSE 0 END), 0) AS total_credit,
        COALESCE(SUM(CASE WHEN t.type = 'debit' THEN t.amount ELSE 0 END), 0) AS total_debit
    FROM 
        profiles p
    LEFT JOIN 
        transactions t ON p.id = t.profile_id
    GROUP BY 
        p.id, p.name, p.mobile
    ORDER BY 
        p.name ASC
";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        // Calculate the net balance for the profile
        $row['balance'] = $row['total_credit'] - $row['total_debit'];
        
        // Calculate the overall system totals
        if ($row['balance'] > 0) {
            // Positive balance (User owes you) -> Receivable/Collected
            $overall_collected += $row['balance'];
        } elseif ($row['balance'] < 0) {
            // Negative balance (You owe user / Advance) -> Payable/Pending
            $overall_pending += abs($row['balance']);
        }
        
        $profiles[] = $row;
    }
    $result->free();
} else {
    $error_message = "Error fetching profiles: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Dashboard - Account Management System</title>
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; color: #333; }
        .dashboard { max-width: 1200px; margin: 0 auto; }
        h1 { color: #007bff; margin-bottom: 25px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        
        /* Summary Cards (Flexbox) */
        .summary-cards { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { flex: 1; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); color: white; transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); }
        .collected { background-color: #28a745; } /* Green */
        .pending { background-color: #dc3545; } /* Red */
        .card h2 { margin-top: 0; font-size: 1.1em; opacity: 0.9; }
        .card p { font-size: 2.5em; font-weight: bold; margin: 5px 0 0 0; }
        
        /* Header & Actions */
        .action-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        #search-bar { padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 300px; font-size: 16px; transition: border-color 0.3s; }
        #search-bar:focus { border-color: #007bff; outline: none; }
        
        .action-group a { text-decoration: none; }
        .create-btn, .backup-btn { 
            padding: 10px 15px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold; 
            display: inline-block; 
            transition: background-color 0.3s;
            margin-left: 10px;
        }
        .create-btn { background-color: #007bff; color: white; }
        .create-btn:hover { background-color: #0056b3; }
        .backup-btn { background-color: #6c757d; color: white; }
        .backup-btn:hover { background-color: #5a6268; }

        /* Profile List Table */
        .profile-list-container { background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); }
        .profile-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 15px; }
        .profile-table th, .profile-table td { padding: 12px 15px; border-bottom: 1px solid #e9ecef; text-align: left; }
        .profile-table th { background-color: #f8f9fa; font-weight: 600; color: #555; border-top: 1px solid #e9ecef; }
        .profile-table tbody tr:hover { background-color: #f5f5f5; }
        
        /* Balance Styling */
        .balance-pos { color: #28a745; font-weight: 600; } 
        .balance-neg { color: #dc3545; font-weight: 600; } 
        .view-btn { background-color: #17a2b8; color: white; padding: 6px 10px; border-radius: 5px; text-decoration: none; font-size: 0.9em; transition: background-color 0.3s; }
        .view-btn:hover { background-color: #138496; }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>📊 Account Management Dashboard</h1>

        <?php if (!empty($error_message)): ?>
            <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
                <strong>Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="summary-cards">
            <div class="card collected">
                <h2>💰 Overall Collected (Receivable)</h2>
                <p>₹ <?php echo number_format($overall_collected, 2); ?></p>
            </div>
            <div class="card pending">
                <h2>⏳ Overall Pending (Payable / Advance)</h2>
                <p>₹ <?php echo number_format($overall_pending, 2); ?></p>
            </div>
        </div>

        <div class="action-header">
            <input type="text" id="search-bar" placeholder="🔍 Search profile by name or mobile...">
            <div class="action-group">
                <a href="backup_all_profiles.php" class="backup-btn">⬇️ Backup Data (PDF)</a> 
                <a href="create_profile.php" class="create-btn">➕ Create Profile</a>
            </div>
        </div>
        
        <div class="profile-list-container">
            <h3>Profile Ledger List</h3>
            
            <table class="profile-table" id="profile-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Profile Name (Mobile)</th>
                        <th style="width: 20%;">Credit (In)</th>
                        <th style="width: 20%;">Debit (Out)</th>
                        <th style="width: 15%;">Balance</th>
                        <th style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($profiles)): ?>
                        <?php foreach ($profiles as $profile): ?>
                            <tr data-name="<?php echo strtolower(htmlspecialchars($profile['name'] . ' ' . $profile['mobile'])); ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($profile['name']); ?></strong><br>
                                    <small style="color:#6c757d;"><?php echo htmlspecialchars($profile['mobile']); ?></small>
                                </td>
                                <td>₹ <?php echo number_format($profile['total_credit'], 2); ?></td>
                                <td>₹ <?php echo number_format($profile['total_debit'], 2); ?></td>
                                <td class="<?php echo ($profile['balance'] >= 0) ? 'balance-pos' : 'balance-neg'; ?>">
                                    ₹ <?php echo number_format($profile['balance'], 2); ?>
                                </td>
                                <td>
                                    <a href="view_profile.php?id=<?php echo $profile['id']; ?>" class="view-btn">View Profile</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6c757d; padding: 30px;">
                                No profiles found. Click 'Create Profile' to begin tracking accounts.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        document.getElementById('search-bar').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const tableBody = document.getElementById('profile-table').getElementsByTagName('tbody')[0];
            const rows = tableBody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const rowData = rows[i].getAttribute('data-name'); // Get data-name attribute for search
                
                if (rowData && rowData.indexOf(searchText) > -1) {
                    rows[i].style.display = ""; // Show row
                } else {
                    rows[i].style.display = "none"; // Hide row
                }
            }
        });
    </script>
</body>
</html>