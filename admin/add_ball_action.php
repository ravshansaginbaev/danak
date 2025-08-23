<?php
session_start();
require '../db.php';

$price = $_POST['price_ball'];
$total = $_POST['overall_ball'];
$added_by = $_SESSION['username'] ?? 'admin';

$stmt = $conn->prepare("INSERT INTO danak_ball (price_ball, overall_ball, added_by) VALUES (?, ?, ?)");
$stmt->bind_param("dis", $price, $total, $added_by);

if ($stmt->execute()) {
    $_SESSION['admin_toast'] = "✅ Danak Ball added!";
} else {
    $_SESSION['admin_toast'] = "❌ Failed to add!";
}

header("Location: danak.php");
exit();
