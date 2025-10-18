<?php
// create_profile.php

// 1. Include the configuration file
include 'config.php';

$message = ""; // Variable to store success or error messages

// 2. Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input data
    $name = $conn->real_escape_string($_POST['name']);
    $mobile = $conn->real_escape_string($_POST['mobile']);

    // Basic Validation
    if (empty($name) || empty($mobile)) {
        $message = "<div class='error'>Error: Both Name and Mobile Number are required.</div>";
    } else {
        // Prepare and execute the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO labours (name, mobile_number) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $mobile);

        if ($stmt->execute()) {
            $message = "<div class='success'>✅ Labour **" . htmlspecialchars($name) . "** profile created successfully!</div>";
            // Clear the form fields after successful submission
            $_POST['name'] = $_POST['mobile'] = '';
        } else {
            // Check for duplicate entry (e.g., mobile number is unique)
            if ($conn->errno == 1062) {
                $message = "<div class='error'>Error: Mobile number already exists.</div>";
            } else {
                $message = "<div class='error'>Error creating profile: " . $stmt->error . "</div>";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Labour Profile</title>
    <style>
        /* Basic CSS for attractiveness and usability */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 20px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; text-align: center; }
        input[type="text"], input[type="tel"] { width: 100%; padding: 10px; margin: 8px 0 15px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #4CAF50; color: white; padding: 14px 20px; margin: 8px 0; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #45a049; }
        .success { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Labour Profile</h2>
        <?php echo $message; // Display the status message ?>

        <form method="POST" action="">
            <label for="name"><b>Name</b></label>
            <input type="text" placeholder="Enter Name" name="name" id="name" required 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">

            <label for="mobile"><b>Mobile Number</b></label>
            <input type="tel" placeholder="Enter Mobile Number" name="mobile" id="mobile" required 
                   value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>">

            <button type="submit">Create Profile</button>
        </form>
        <a href="index.php" class="back-link">← Go to Main Page</a>
    </div>
</body>
</html>