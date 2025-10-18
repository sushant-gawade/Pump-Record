<?php
require_once 'includes/db_config.php';

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    header("location: index.php");
    exit;
}

if ($type == 'stock') {
    // 1. Get the amount of stock to be deleted
    $get_liters_sql = "SELECT liters_added FROM stock WHERE id = ?";
    $stmt_get = $conn->prepare($get_liters_sql);
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $row = $result->fetch_assoc();
    $liters_deleted = $row['liters_added'] ?? 0;
    $stmt_get->close();

    // 2. Delete the record from the 'stock' table
    $delete_sql = "DELETE FROM stock WHERE id = ?";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // 3. Subtract the deleted liters from the 'config' current stock
    if ($liters_deleted > 0) {
        $update_stock_sql = "UPDATE config SET current_stock = current_stock - ?";
        $stmt_update = $conn->prepare($update_stock_sql);
        $stmt_update->bind_param("d", $liters_deleted);
        $stmt_update->execute();
        $stmt_update->close();
    }

    // Redirect back to the stock history page
    header("location: petrol_history.php");
    exit;

} elseif ($type == 'nozzle') {
    // NOTE: Deleting nozzle readings is complex as it requires calculating the dispensed
    // amount and *adding* it back to the current stock.

    // 1. Get the dispensed amount of fuel to be added back to stock
    $get_dispensed_sql = "SELECT (n1_close - n1_open) + (n2_close - n2_open) AS dispensed FROM nozzle_readings WHERE id = ?";
    $stmt_get = $conn->prepare($get_dispensed_sql);
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $row = $result->fetch_assoc();
    $dispensed_amount = $row['dispensed'] ?? 0;
    $stmt_get->close();

    // 2. Delete the record from the 'nozzle_readings' table
    $delete_sql = "DELETE FROM nozzle_readings WHERE id = ?";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // 3. Add the dispensed amount back to the 'config' current stock
    if ($dispensed_amount > 0) {
        $update_stock_sql = "UPDATE config SET current_stock = current_stock + ?";
        $stmt_update = $conn->prepare($update_stock_sql);
        $stmt_update->bind_param("d", $dispensed_amount);
        $stmt_update->execute();
        $stmt_update->close();
    }

    // Redirect back to the main dashboard
    header("location: index.php");
    exit;
}

$conn->close();
?>