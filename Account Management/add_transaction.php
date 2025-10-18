<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile_id = mysqli_real_escape_string($conn, $_POST['profile_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $note = mysqli_real_escape_string($conn, $_POST['note']);
    $transaction_date = mysqli_real_escape_string($conn, $_POST['transaction_date']);
    $type = isset($_POST['type']) ? mysqli_real_escape_string($conn, $_POST['type']) : '';

    // Validation
    if (empty($profile_id) || empty($type) || empty($amount) || empty($payment_method) || empty($transaction_date)) {
        echo "<script>alert('Please fill all required fields.'); window.history.back();</script>";
        exit;
    }

    // ✅ Fixed query — now includes both `type` and `payment_method` properly
    $query = "INSERT INTO transactions (profile_id, type, amount, payment_method, note, transaction_date)
              VALUES ('$profile_id', '$type', '$amount', '$payment_method', '$note', '$transaction_date')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Transaction added successfully!'); window.location.href='view_profile.php?id=$profile_id';</script>";
    } else {
        echo "<script>alert('Error adding transaction: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Transaction</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #74ebd5, #9face6);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            width: 380px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .container:hover {
            transform: translateY(-5px);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        form label {
            float: left;
            font-weight: 600;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #5c6bc0;
            outline: none;
        }
        button {
            width: 100%;
            background: #5c6bc0;
            color: #fff;
            font-weight: bold;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s, transform 0.2s;
        }
        button:hover {
            background: #3f51b5;
            transform: scale(1.03);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Transaction</h2>
        <form id="transactionForm" method="POST" action="">
            <input type="hidden" name="profile_id" value="<?php echo $_GET['id']; ?>">

            <label>Transaction Type:</label>
            <select name="type" id="type" required>
                <option value="">Select Type</option>
                <option value="credit">Credit (Money In)</option>
                <option value="debit">Debit (Money Out)</option>
            </select>

            <label>Amount (₹):</label>
            <input type="number" step="0.01" name="amount" id="amount" required>

            <label>Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="">Select Method</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="Online">Online</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Debit Card">Debit Card</option>
            </select>

            <label>Note (optional):</label>
            <textarea name="note" id="note" rows="3"></textarea>

            <label>Transaction Date:</label>
            <input type="date" name="transaction_date" id="transaction_date" required>

            <button type="submit">Add Transaction</button>
        </form>
    </div>

    <script>
        // Simple front-end validation
        document.getElementById('transactionForm').addEventListener('submit', function(e) {
            const type = document.getElementById('type').value;
            const amount = document.getElementById('amount').value;
            const paymentMethod = document.getElementById('payment_method').value;
            const date = document.getElementById('transaction_date').value;

            if (!type || !amount || !paymentMethod || !date) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
            }
        });
    </script>
</body>
</html>
