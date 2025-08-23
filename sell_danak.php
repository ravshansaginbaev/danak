<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'];

// Get user's phone
$stmt = $conn->prepare("SELECT phone_number, ball FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$phone = $user['phone_number'];
$current_ball = $user['ball'];

$ball = intval($_POST['ball']);
$price = floatval($_POST['price']);

if ($ball <= 0 || $price <= 0 || $ball > $current_ball) {
    $_SESSION['toast'] = "❌ Yaroqsiz son yoki sizda buncha danak yo‘q!";
    $_SESSION['toast_type'] = "danger";
    header("Location: user_page.php");
    exit();
}

// 1. Reduce user's ball
$update = $conn->prepare("UPDATE users SET ball = ball - ? WHERE id = ?");
$update->bind_param("ii", $ball, $user_id);
$update->execute();

// 2. Insert to target_sell
$insert = $conn->prepare("INSERT INTO target_sell (sell_by, ball, price) VALUES (?, ?, ?)");
$insert->bind_param("sid", $phone, $ball, $price);
$insert->execute();

$_SESSION['toast'] = "✅ $ball dona danak sotuvga qo‘yildi!";
$_SESSION['toast_type'] = "success";
header("Location: user_page.php");
exit();
