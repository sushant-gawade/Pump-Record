<?php
// edit_payment.php

include 'config.php';

// 1. Get Payment ID
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    die("Error: Invalid payment ID.");
}
$payment_id = (int)$_GET['pid'];
$message = "";
$labour_id = 0;

// 2. Handle Form Submission (Update)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_payment'])) {
    $paid_amount = filter_var($_POST['paid_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $note = $conn->real_escape_string($_POST['note']);
    $payment_date = $_POST['payment_date'];
    $current_payment_id = (int)$_POST['payment_id'];
    $redirect_labour_id = (int)$_POST['labour_id'];

    if ($paid_amount > 0 && !empty($payment_date)) {
        // Prepare UPDATE statement
        $stmt_update = $conn->prepare("UPDATE payments SET paid_amount = ?, note = ?, payment_date = ? WHERE payment_id = ?");

        // FIX IS HERE: Changed "dsi" to "dssi"
        // Types: d (double/decimal), s (string for note), s (string for date), i (integer for payment_id)
        $stmt_update->bind_param("dssi", $paid_amount, $note, $payment_date, $current_payment_id); 
        // 4 Types   <--> 4 Variables: $paid_amount, $note, $payment_date, $current_payment_id

        if ($stmt_update->execute()) {
            header("Location: view_profile.php?id=" . $redirect_labour_id . "&msg=paid_updated");
            exit();
        } else {
            $message = "<div class='error'>Error updating payment: " . $stmt_update->error . "</div>";
        }
        $stmt_update->close();
    } else {
        $message = "<div class='error'>Please ensure amount and date are valid.</div>";
    }
}

// 3. Fetch current payment details
$stmt_fetch = $conn->prepare("SELECT p.paid_amount, p.note, p.payment_date, l.name, l.labour_id 
                               FROM payments p 
                               JOIN labours l ON p.labour_id = l.labour_id 
                               WHERE p.payment_id = ?");
$stmt_fetch->bind_param("i", $payment_id);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();

if ($result_fetch->num_rows === 0) {
    die("Payment record not found.");
}
$payment = $result_fetch->fetch_assoc();
$labour_id = $payment['labour_id'];
$stmt_fetch->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Payment for <?php echo htmlspecialchars($payment['name']); ?></title>
    <style>
        /* Basic CSS styles for the form */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="date"], input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .submit-btn { background-color: #ffc107; color: #333; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; margin-top: 10px; }
        .submit-btn:hover { background-color: #e0a800; }
        .back-link { display: block; text-align: center; margin-top: 20px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Payment Record</h2>
        <p>Editing record for: <strong><?php echo htmlspecialchars($payment['name']); ?></strong></p>
        <?php echo $message; ?>

        <form method="POST">
            <input type="hidden" name="payment_id" value="<?php echo $payment_id; ?>">
            <input type="hidden" name="labour_id" value="<?php echo $labour_id; ?>">

            <div class="form-group">
                <label for="payment_date">Date Paid:</label>
                <input type="date" name="payment_date" id="payment_date" required value="<?php echo htmlspecialchars($payment['payment_date']); ?>">
            </div>
            <div class="form-group">
                <label for="paid_amount">Amount Paid (₹):</label>
                <input type="number" step="0.01" name="paid_amount" id="paid_amount" required value="<?php echo htmlspecialchars($payment['paid_amount']); ?>">
            </div>
            <div class="form-group">
                <label for="note">Note (Reason/Details):</label>
                <textarea name="note" id="note" rows="3"><?php echo htmlspecialchars($payment['note']); ?></textarea>
            </div>

            <button type="submit" name="update_payment" class="submit-btn">Update Payment Record</button>
        </form>
        <div class="back-link">
            <a href="view_profile.php?id=<?php echo $labour_id; ?>">← Cancel and Back to Profile</a>
        </div>
    </div>
</body>
</html>