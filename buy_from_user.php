<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'];
$sell_id = intval($_POST['sell_id']);

// 1. Get buyer info
$stmt = $conn->prepare("SELECT phone_number, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$buyer = $stmt->get_result()->fetch_assoc();

$buyer_phone = $buyer['phone_number'];
$balance = $buyer['balance'];

// 2. Get sell info
$sellStmt = $conn->prepare("SELECT * FROM target_sell WHERE id = ? AND status = 0");
$sellStmt->bind_param("i", $sell_id);
$sellStmt->execute();
$sell = $sellStmt->get_result()->fetch_assoc();

if (!$sell) {
    $_SESSION['toast'] = "❌ Bu danak sotuvda mavjud emas!";
    $_SESSION['toast_type'] = "danger";
    header("Location: user_page.php");
    exit();
}

// Check self-buy
if ($sell['sell_by'] === $buyer['phone_number']) {
    $_SESSION['toast'] = "❌ O‘zingiz joylagan danakni sotib ololmaysiz!";
    $_SESSION['toast_type'] = "danger";
    header("Location: user_page.php");
    exit();
}

$total = $sell['price'];

if ($balance < $total) {
    $_SESSION['toast'] = "❌ Hisobingizda mablag‘ yetarli emas!";
    $_SESSION['toast_type'] = "danger";
    header("Location: user_page.php");
    exit();
}

// ✅ Deduct from buyer
$conn->query("UPDATE users SET balance = balance - $total, ball = ball + {$sell['ball']} WHERE id = $user_id");

// ✅ Add to seller balance
$sellerStmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE phone_number = ?");
$sellerStmt->bind_param("ds", $total, $sell['sell_by']);
$sellerStmt->execute();

// ✅ Update target_sell status
$updateSell = $conn->prepare("UPDATE target_sell SET buy_by = ?, status = 1 WHERE id = ?");
$updateSell->bind_param("si", $buyer_phone, $sell_id);
$updateSell->execute();

$notif_text = "📢 Siz joylagan {$sell['ball']} danak ballni {$buyer_phone} foydalanuvchi {$total} so‘mga sotib oldi.";

$notifStmt = $conn->prepare("INSERT INTO notifications (phone_number, message) VALUES (?, ?)");
$notifStmt->bind_param("ss", $sell['sell_by'], $notif_text);
$notifStmt->execute();


// ✅ Show toast to buyer
$_SESSION['toast'] = "✅ Sotib olindi!";
$_SESSION['toast_type'] = "success";

header("Location: user_page.php");
exit();


?>
