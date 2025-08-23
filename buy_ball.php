<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'];
$quantity = intval($_POST['quantity']);

// 1. Get user info
$userStmt = $conn->prepare("SELECT balance, phone_number, full_name FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// 2. Get current danak ball price and latest danak row
$priceResult = $conn->query("SELECT id, price_ball, overall_ball FROM danak_ball WHERE overall_ball > 0 ORDER BY id DESC LIMIT 1");

$priceRow = $priceResult->fetch_assoc();
$danak_id = $priceRow['id'];
$price = $priceRow['price_ball'];
$current_overall = $priceRow['overall_ball'];

// 3. Calculate total
$total_cost = floatval($_POST['total_cost']); 

// 4. Check balance
if ($user['balance'] < $total_cost) {
    $_SESSION['toast'] = "❌ Hisobda mablag‘ yetarli emas!";
    $_SESSION['toast_type'] = "danger";
    header("Location: user_page.php");
    exit();
}

// 5. Check danak availability
if ($current_overall < $quantity) {
    $_SESSION['toast'] = "❌ Yetarli danak mavjud emas!";
    $_SESSION['toast_type'] = "danger";
    header("Location: user_page.php");
    exit();
}



// 7. Update overall_ball (minus purchased quantity)
$new_overall = $current_overall - $quantity;
$updateBall = $conn->prepare("UPDATE danak_ball SET overall_ball = ? WHERE id = ?");
$updateBall->bind_param("ii", $new_overall, $danak_id);
$updateBall->execute();

// 8. Insert transaction into danak_ball (log for selling)
$insert = $conn->prepare("INSERT INTO danak_ball (price_ball, overall_ball, sell_by, phone_number, added_by) VALUES (?, 0, ?, ?, ?)");
$insert->bind_param("diss", $price, $quantity, $user['phone_number'], $user['full_name']);
$insert->execute();

$update = $conn->prepare("UPDATE users SET balance = balance - ?, ball = ball + ? WHERE id = ?");
$update->bind_param("dii", $total_cost, $quantity, $user_id);
$update->execute();

// 9. Toast + redirect
$_SESSION['toast'] = "✅ $quantity dona danak sotib oldingiz!";
$_SESSION['toast_type'] = "success";
header("Location: user_page.php");
exit();
?>
