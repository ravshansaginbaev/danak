<?php
require 'db.php';
date_default_timezone_set('Asia/Tashkent');

// Get all active auctions
$actions = $conn->query("SELECT * FROM actions WHERE status = 'active'");

while ($action = $actions->fetch_assoc()) {
    $action_id = $action['action_id'];
    $period = (int)$action['period'];

    // Get last bid time
    $lastBidResult = $conn->query("
        SELECT MAX(time) as last_time 
        FROM action_users 
        WHERE action_id = $action_id
    ");
    $lastBidData = $lastBidResult->fetch_assoc();

    if (!$lastBidData['last_time']) continue;

    $lastBidTime = strtotime($lastBidData['last_time']);
    $now = time();

    // If period passed with no new bids
    if (($now - $lastBidTime) >= ($period * 60)) {

        // Get the winner
        $winnerResult = $conn->query("
            SELECT phone_number, bid_ball FROM action_users 
            WHERE action_id = $action_id 
            ORDER BY bid_ball DESC, time ASC LIMIT 1
        ");
        $winner = $winnerResult->fetch_assoc();

        if ($winner) {
            $winnerPhone = $winner['phone_number'];
            $bidBall = (int)$winner['bid_ball'];

            // 1. Mark winner in action_users
            $conn->query("UPDATE action_users SET winner = 1 WHERE action_id = $action_id AND phone_number = '$winnerPhone'");

            // 2. Mark auction as finished
            $conn->query("UPDATE actions SET status = 'finished', winner = '$winnerPhone' WHERE action_id = $action_id");

            // 3. Subtract bid_ball from winner in users table
            $conn->query("UPDATE users SET ball = ball - $bidBall WHERE phone_number = '$winnerPhone'");

            // 4. Update admin balance in danak_ball (not user row!)

          
            $stmt = $conn->prepare("INSERT INTO returned_balls (phone_number, returned_ball, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("si", $winnerPhone, $bidBall);
$stmt->execute();


            echo "✅ G'olib: $winnerPhone | Ball: $bidBall ➕ Adminga qo‘shildi\n";
        }
    }
}