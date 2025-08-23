<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$action_id = $_POST['action_id'] ?? null;

if (!$user_id || !$action_id) {
    die("Invalid request.");
}

// // ✅ Check if action exists
// $checkAction = $conn->query("SELECT id FROM actions WHERE id = $action_id");
// if ($checkAction->num_rows == 0) {
//     die("Auksion topilmadi.");
// }

// Fetch user
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
if ($user['ball'] < 10) {
    die("Sizda yetarli ball yo'q");
}

// Deduct 1 ball

// Check if already registered
$phone = $user['phone_number'];
$check = $conn->query("SELECT id FROM action_users WHERE action_id = $action_id AND phone_number = '$phone'");
if ($check->num_rows > 0) {
    die("Siz ushbu auksionda allaqachon ishtirok etgansiz.");
}

// Insert into action_users
$conn->query("INSERT INTO action_users (action_id, phone_number, min_bid_ball, bid_ball, winner, time) VALUES (
    $action_id,
    '$phone',
    1,
    0,
    0,
    NOW()
)");
$conn->query("UPDATE users SET ball = ball - 1 WHERE id = $user_id");
// $conn->query("UPDATE danak_ball SET overall_ball = overall_ball + 1 WHERE added_by = 'admin'");
$stmt = $conn->prepare("
    INSERT INTO returned_balls (phone_number, returned_ball, created_at)
    VALUES (?, ?, NOW())
");
$returned_ball = 1;
$stmt->bind_param("si", $phone, $returned_ball);

$stmt->execute();

$_SESSION['joined_action_id'] = $action_id;
header("Location: user_game.php");
exit;
