<?php
session_start();
require '../db.php';

$id = $_POST['id'];
$price = $_POST['price_ball'];
$total = $_POST['overall_ball'];

$stmt = $conn->prepare("UPDATE danak_ball SET price_ball = ?, overall_ball = ? WHERE id = ?");
$stmt->bind_param("dii", $price, $total, $id);

if ($stmt->execute()) {
    $_SESSION['admin_toast'] = "✅ Danak Ball updated!";
} else {
    $_SESSION['admin_toast'] = "❌ Update failed!";
}

header("Location: danak.php");
exit();
