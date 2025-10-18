<?php
// view_profile.php - FINAL VERSION with Payment Date FIX, Month Navigation on Attendance, 
// AND Payment History filtered by Month with Totals

include 'config.php'; // Ensure your database connection settings are in config.php

// --- 1. Get Labour ID and Handle Month Selection ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: Invalid labour ID.");
}
$labour_id = (int)$_GET['id'];
$message = ""; 
$labour = null;

// Determine the month currently being viewed
$current_month_str = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Calculate the start and end dates for the current view month
$start_of_month = strtotime('first day of ' . $current_month_str);
$current_month_start = date('Y-m-01', $start_of_month);
$current_month_end = date('Y-m-t', $start_of_month); // 't' gives the number of days in the month

// Calculate the dates for the Previous and Next month links
$prev_month_link = date('Y-m', strtotime('-1 month', $start_of_month));
$next_month_link = date('Y-m', strtotime('+1 month', $start_of_month));
$current_month_display = date('F Y', $start_of_month);


// Fetch profile details (Essential)
$stmt_profile = $conn->prepare("SELECT name, mobile_number FROM labours WHERE labour_id = ?");
$stmt_profile->bind_param("i", $labour_id);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();

if ($result_profile->num_rows === 0) {
    $conn->close();
    die("Error: Labour profile not found.");
}
$labour = $result_profile->fetch_assoc();
$stmt_profile->close();


// --- 2. Handle Profile Deletion ---
if (isset($_POST['delete_profile'])) {
    $conn->begin_transaction();
    
    try {
        $stmt_att = $conn->prepare("DELETE FROM attendance WHERE labour_id = ?");
        $stmt_att->bind_param("i", $labour_id);
        $stmt_att->execute();
        $stmt_att->close();
        
        $stmt_pay = $conn->prepare("DELETE FROM payments WHERE labour_id = ?");
        $stmt_pay->bind_param("i", $labour_id);
        $stmt_pay->execute();
        $stmt_pay->close();

        $stmt_labour = $conn->prepare("DELETE FROM labours WHERE labour_id = ?");
        $stmt_labour->bind_param("i", $labour_id);
        $stmt_labour->execute();
        $stmt_labour->close();

        $conn->commit();
        header("Location: index.php?msg=deleted&name=" . urlencode($labour['name']));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='error'>❌ Error deleting profile: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}


// --- 3. Handle Status Messages from Redirections (Edit Success) ---
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'paid_updated') {
        $message = "<div class='success'>📝 Payment record updated successfully!</div>";
    } elseif ($_GET['msg'] == 'att_updated') {
        $message = "<div class='success'>📝 Attendance record updated successfully!</div>";
    }
}


// --- 4. Function 1: Handle Payment Submission ---
if (isset($_POST['add_payment'])) {
    $amount = filter_var($_POST['paid_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $note = $conn->real_escape_string($_POST['note']);
    $payment_date = $_POST['payment_date'];

    if ($amount > 0 && !empty($payment_date)) {
        $stmt_payment = $conn->prepare("INSERT INTO payments (labour_id, paid_amount, note, payment_date) VALUES (?, ?, ?, ?)");
        
        // FIX: Using "idss" (Integer, Double, String, String) for date storage.
        $stmt_payment->bind_param("idss", $labour_id, $amount, $note, $payment_date); 

        if ($stmt_payment->execute()) {
            $message = "<div class='success'>💰 Payment of ₹" . number_format($amount, 2) . " logged successfully!</div>";
            // Redirect to ensure message persistence and reflect the new payment in the correct month view
            header("Location: view_profile.php?id=$labour_id&month=" . date('Y-m', strtotime($payment_date)) . "&msg=paid_added");
            exit();
        } else {
            $message = "<div class='error'>Error logging payment: " . htmlspecialchars($stmt_payment->error) . "</div>";
        }
        $stmt_payment->close();
    } else {
        $message = "<div class='error'>Please enter a valid amount and date.</div>";
    }
}
if (isset($_GET['msg']) && $_GET['msg'] == 'paid_added') {
     $message = "<div class='success'>💰 Payment logged successfully!</div>";
}


// --- 5. Function 2: Handle Attendance Submission ---
if (isset($_POST['mark_attendance'])) {
    $status = $_POST['status']; 
    $att_date = $_POST['att_date'];

    if (!empty($status) && !empty($att_date)) {
        $stmt_attendance = $conn->prepare("REPLACE INTO attendance (labour_id, attendance_date, status) VALUES (?, ?, ?)");
        $stmt_attendance->bind_param("iss", $labour_id, $att_date, $status);

        if ($stmt_attendance->execute()) {
            // Redirect to the correct month after marking attendance
            header("Location: view_profile.php?id=$labour_id&month=" . date('Y-m', strtotime($att_date)) . "&msg=att_updated");
            exit();
        } else {
            $message = "<div class='error'>Error marking attendance: " . htmlspecialchars($stmt_attendance->error) . "</div>";
        }
        $stmt_attendance->close();
    } else {
        $message = "<div class='error'>Please select a status and date for attendance.</div>";
    }
}
if (isset($_GET['msg']) && $_GET['msg'] == 'att_updated') {
     $message = "<div class='success'>📅 Attendance updated successfully!</div>";
}


// --- 6. Fetch Histories (For Display) ---

// FETCH Payment History for the selected month (NEW FILTER)
$sql_payments = "SELECT payment_id, payment_date, paid_amount, note FROM payments WHERE labour_id = ? AND payment_date BETWEEN ? AND ? ORDER BY payment_date DESC";
$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->bind_param("iss", $labour_id, $current_month_start, $current_month_end);
$stmt_payments->execute();
$payments_result = $stmt_payments->get_result();

$total_paid_in_month = 0;
$payments_list = [];
while ($row = $payments_result->fetch_assoc()) {
    $payments_list[] = $row;
    $total_paid_in_month += $row['paid_amount'];
}

// Fetch Attendance History for the selected month
$sql_attendance = "SELECT attendance_id, attendance_date, status FROM attendance WHERE labour_id = ? AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date ASC";
$stmt_attendance_hist = $conn->prepare($sql_attendance);
$stmt_attendance_hist->bind_param("iss", $labour_id, $current_month_start, $current_month_end);
$stmt_attendance_hist->execute();
$attendance_result = $stmt_attendance_hist->get_result();

$present_days = 0;
$absent_days = 0;
$attendance_dates = [];
while ($row = $attendance_result->fetch_assoc()) {
    $attendance_dates[] = $row;
    if ($row['status'] == 'P') {
        $present_days++;
    } else {
        $absent_days++;
    }
}
// Use the calculated total days for the viewed month
$total_days_in_month = date('t', $start_of_month);


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile: <?php echo htmlspecialchars($labour['name']); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Base Styles */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 5px; }
        .profile-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .details-box { background-color: #e9ecef; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .form-section, .history-section { background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 25px; }
        .form-group { margin-bottom: 10px; }
        
        /* Input & Button Styles */
        .form-group input[type="date"], .form-group input[type="number"], .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .submit-btn { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .delete-btn { background-color: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .edit-btn { padding: 5px 10px; background-color: #ffc107; color: #333; text-decoration: none; border-radius: 4px; font-size: 0.9em; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        
        /* Message Styles */
        .success { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        
        /* Layout & Navigation */
        .month-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .nav-btn { background-color: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 1em; }
        .nav-btn:hover { background-color: #0056b3; }
        .current-month-title { font-size: 1.2em; font-weight: bold; }
        .total-box { padding: 10px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; text-align: right; font-weight: bold; margin-top: 10px; }
        
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php">← Back to Dashboard</a>
        
        <div class="profile-header">
            <h1><?php echo htmlspecialchars($labour['name']); ?></h1>
            <form method="POST" onsubmit="return confirm('Are you absolutely sure you want to DELETE <?php echo htmlspecialchars($labour['name']); ?>? This action is permanent and cannot be undone.');">
                <input type="hidden" name="delete_profile" value="1">
                <button type="submit" class="delete-btn">❌ Delete Profile</button>
            </form>
        </div>

        <?php echo $message; ?>

        <div class="details-box">
            <p><strong>Mobile Number:</strong> <?php echo htmlspecialchars($labour['mobile_number']); ?></p>
        </div>

        <div style="display: flex; gap: 30px;">
            <div style="flex: 2;">
                
                <div class="form-section">
                    <h2>💰 Record Payment (Expense)</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="payment_date">Date Paid:</label>
                            <input type="date" name="payment_date" id="payment_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="paid_amount">Amount Paid (₹):</label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" placeholder="e.g., 500.00" required>
                        </div>
                        <div class="form-group">
                            <label for="note">Note (Reason/Details):</label>
                            <textarea name="note" id="note" rows="2" placeholder="Ex: Advance for food, Daily wage payment"></textarea>
                        </div>
                        <button type="submit" name="add_payment" class="submit-btn">Add Payment</button>
                    </form>
                </div>
                
                <div class="history-section">
                    <div class="month-nav">
                        <a href="view_profile.php?id=<?php echo $labour_id; ?>&month=<?php echo $prev_month_link; ?>" class="nav-btn">
                            &lt; 
                        </a>
                        
                        <span class="current-month-title">
                            Payment History: <?php echo $current_month_display; ?>
                        </span>
                        
                        <a href="view_profile.php?id=<?php echo $labour_id; ?>&month=<?php echo $next_month_link; ?>" class="nav-btn">
                             &gt;
                        </a>
                    </div>

                    <div class="total-box">
                        Total Paid this month: ₹<?php echo number_format($total_paid_in_month, 2); ?>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount (₹)</th>
                                <th>Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($payments_list) > 0): ?>
                                <?php foreach ($payments_list as $payment): ?>
                                <tr>
                                    <?php
                                        $db_date = $payment['payment_date'];
                                        $formatted_date = (empty($db_date) || $db_date == '0000-00-00') ? 'Date Missing' : date('d-M-Y', strtotime($db_date));
                                    ?>
                                    <td><?php echo $formatted_date; ?></td>
                                    <td><?php echo number_format($payment['paid_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($payment['note']); ?></td>
                                    <td><a href="edit_payment.php?pid=<?php echo $payment['payment_id']; ?>" class="edit-btn">Edit</a></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No payment history found for <?php echo $current_month_display; ?>.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="flex: 1;">
                
                <div class="form-section">
                    <h2>📅 Mark Attendance</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="att_date">Date:</label>
                            <input type="date" name="att_date" id="att_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Status:</label><br>
                            <input type="radio" id="present" name="status" value="P" required>
                            <label for="present">Present</label> &nbsp;
                            <input type="radio" id="absent" name="status" value="A">
                            <label for="absent">Absent</label>
                        </div>
                        <button type="submit" name="mark_attendance" class="submit-btn">Mark/Update Attendance</button>
                    </form>
                </div>

                <div class="history-section">
                    <div class="month-nav">
                        <a href="view_profile.php?id=<?php echo $labour_id; ?>&month=<?php echo $prev_month_link; ?>" class="nav-btn">
                            &lt; 
                        </a>
                        
                        <span class="current-month-title">
                            Attendance: <?php echo $current_month_display; ?>
                        </span>
                        
                        <a href="view_profile.php?id=<?php echo $labour_id; ?>&month=<?php echo $next_month_link; ?>" class="nav-btn">
                             &gt;
                        </a>
                    </div>
                
                    <h3>Attendance Summary</h3>
                    <p>Present Days: **<?php echo $present_days; ?>**</p>
                    <p>Absent Days: **<?php echo $absent_days; ?>**</p>
                    <p>Total Days in Month: **<?php echo $total_days_in_month; ?>**</p>

                    <canvas id="monthlyAttendanceChart"></canvas>
                </div>

                <div class="history-section">
                    <h3>Dates Record (List)</h3>
                    <ul style="list-style: none; padding: 0;">
                        <?php 
                        if (!empty($attendance_dates)):
                            foreach ($attendance_dates as $att) {
                                $status_text = ($att['status'] == 'P') ? 'Present' : 'Absent';
                                $status_color = ($att['status'] == 'P') ? '#28a745' : '#dc3545';
                                echo "<li style='margin-bottom: 5px;'>" . date('d-M', strtotime($att['attendance_date'])) . ": <strong style='color: {$status_color};'>{$status_text}</strong> ( <a href='edit_attendance.php?aid={$att['attendance_id']}' class='edit-btn' style='font-size:0.8em; background-color: #f8f9fa; border: 1px solid #ddd;'>Edit</a> )</li>";
                            }
                        else:
                            echo "<li>No attendance recorded in {$current_month_display}.</li>";
                        endif;
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        const attendanceData = <?php echo json_encode($attendance_dates); ?>;
        const daysInMonth = <?php echo $total_days_in_month; ?>;
        const labels = [];
        let dailyP = new Array(daysInMonth).fill(0);
        let dailyA = new Array(daysInMonth).fill(0);

        for(let i = 1; i <= daysInMonth; i++) {
            labels.push(i);
        }

        attendanceData.forEach(record => {
            const date = new Date(record.attendance_date);
            const dayIndex = date.getDate() - 1; 

            if (dayIndex >= 0 && dayIndex < daysInMonth) {
                if (record.status === 'P') {
                    dailyP[dayIndex] = 1; 
                } else if (record.status === 'A') {
                    dailyA[dayIndex] = 1; 
                }
            }
        });
        
        const ctx = document.getElementById('monthlyAttendanceChart').getContext('2d');
        // Destroy existing chart instance to prevent duplicates/errors when navigating months
        if (window.myMonthlyChart) {
            window.myMonthlyChart.destroy();
        }

        window.myMonthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Present (P)',
                        data: dailyP,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        stack: 'Attendance'
                    },
                    {
                        label: 'Absent (A)',
                        data: dailyA,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        stack: 'Attendance'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                        title: { display: true, text: 'Day of the Month' }
                    },
                    y: {
                        stacked: true,
                        title: { display: true, text: 'Status (1=P, 1=A)' },
                        min: 0,
                        max: 1 
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Daily Attendance Status for ' + '<?php echo $current_month_display; ?>' }
                }
            }
        });
    </script>
</body>
</html>