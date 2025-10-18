<?php
// edit_attendance.php

include 'config.php';

// 1. Get Attendance ID
if (!isset($_GET['aid']) || !is_numeric($_GET['aid'])) {
    die("Error: Invalid attendance ID.");
}
$attendance_id = (int)$_GET['aid'];
$message = "";
$labour_id = 0; // Initialize labour_id

// 2. Handle Form Submission (Update)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_attendance'])) {
    $status = $_POST['status']; // 'P' or 'A'
    $att_date = $_POST['att_date'];
    $current_attendance_id = (int)$_POST['attendance_id'];
    $redirect_labour_id = (int)$_POST['labour_id'];

    if (!empty($status) && !empty($att_date)) {
        // Prepare UPDATE statement
        $stmt_update = $conn->prepare("UPDATE attendance SET status = ?, attendance_date = ? WHERE attendance_id = ?");
        $stmt_update->bind_param("ssi", $status, $att_date, $current_attendance_id);

        if ($stmt_update->execute()) {
            // Success: Redirect back to the labour's profile view
            header("Location: view_profile.php?id=" . $redirect_labour_id . "&msg=att_updated");
            exit();
        } else {
            $message = "<div class='error'>Error updating attendance: " . $stmt_update->error . "</div>";
        }
        $stmt_update->close();
    } else {
        $message = "<div class='error'>Please select a status and date.</div>";
    }
}

// 3. Fetch current attendance details
$stmt_fetch = $conn->prepare("SELECT a.status, a.attendance_date, l.name, l.labour_id 
                               FROM attendance a 
                               JOIN labours l ON a.labour_id = l.labour_id 
                               WHERE a.attendance_id = ?");
$stmt_fetch->bind_param("i", $attendance_id);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();

if ($result_fetch->num_rows === 0) {
    die("Attendance record not found.");
}
$attendance = $result_fetch->fetch_assoc();
$labour_id = $attendance['labour_id']; // Store labour ID for redirection
$stmt_fetch->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Attendance for <?php echo htmlspecialchars($attendance['name']); ?></title>
    <style>
        /* Basic CSS styles for the form */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="date"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .submit-btn { background-color: #ffc107; color: #333; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; margin-top: 10px; }
        .submit-btn:hover { background-color: #e0a800; }
        .back-link { display: block; text-align: center; margin-top: 20px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Attendance Record</h2>
        <p>Editing record for: <strong><?php echo htmlspecialchars($attendance['name']); ?></strong> on <strong><?php echo date('d-M-Y', strtotime($attendance['attendance_date'])); ?></strong></p>
        <?php echo $message; ?>

        <form method="POST">
            <input type="hidden" name="attendance_id" value="<?php echo $attendance_id; ?>">
            <input type="hidden" name="labour_id" value="<?php echo $labour_id; ?>">

            <div class="form-group">
                <label for="att_date">Date:</label>
                <input type="date" name="att_date" id="att_date" required value="<?php echo htmlspecialchars($attendance['attendance_date']); ?>">
            </div>

            <div class="form-group">
                <label>Status:</label><br>
                <input type="radio" id="present" name="status" value="P" <?php echo ($attendance['status'] == 'P') ? 'checked' : ''; ?> required>
                <label for="present">Present</label> &nbsp;
                <input type="radio" id="absent" name="status" value="A" <?php echo ($attendance['status'] == 'A') ? 'checked' : ''; ?>>
                <label for="absent">Absent</label>
            </div>

            <button type="submit" name="update_attendance" class="submit-btn">Update Attendance Record</button>
        </form>
        <div class="back-link">
            <a href="view_profile.php?id=<?php echo $labour_id; ?>">← Cancel and Back to Profile</a>
        </div>
    </div>
</body>
</html>