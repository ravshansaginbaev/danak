<?php
require 'db.php';

$action_id = $_POST['action_id'] ?? null;
$phone = $_POST['phone_number'] ?? null;
$bid_ball = (int)($_POST['bid_ball'] ?? 0);

if (!$action_id || !$phone || $bid_ball <= 0) {
    die("❌ Notog‘ri ma’lumot");
}

// Check if user already bid
$check = $conn->query("SELECT * FROM action_users WHERE action_id = $action_id AND phone_number = '$phone'");
if ($check->num_rows > 0) {
    // Update bid (+)
    $conn->query("UPDATE action_users SET bid_ball =  $bid_ball, time = NOW() WHERE action_id = $action_id AND phone_number = '$phone'");
} else {
    // Insert new bid
    $conn->query("INSERT INTO action_users (action_id, phone_number, min_bid_ball, bid_ball, time) VALUES (
        $action_id,
        '$phone',
        1,
        $bid_ball,
        NOW()
    )");
}

// Auto-check if period is passed without bid (winner assignment)
$action = $conn->query("SELECT * FROM actions WHERE action_id = $action_id")->fetch_assoc();
$periodMin = (int)$action['period'];

$latestBid = $conn->query("SELECT MAX(time) as last_bid_time FROM action_users WHERE action_id = $action_id")->fetch_assoc();
$lastBidTime = strtotime($latestBid['last_bid_time']);
$now = time();

if (($now - $lastBidTime) > ($periodMin * 60)) {
    // Set winner: max bid_ball
    $winnerRow = $conn->query("
        SELECT phone_number FROM action_users 
        WHERE action_id = $action_id 
        ORDER BY bid_ball DESC LIMIT 1
    ")->fetch_assoc();
    
    $winnerPhone = $winnerRow['phone_number'];

    // Update winner info
    $conn->query("UPDATE action_users SET winner = 1 WHERE action_id = $action_id AND phone_number = '$winnerPhone'");
    $conn->query("UPDATE actions SET winner = '$winnerPhone', status = 'finished' WHERE action_id = $action_id");
}

header("Location: user_game.php");
exit;
?>
