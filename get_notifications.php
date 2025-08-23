<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT phone_number FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$phone = $user['phone_number'];

$notifQuery = $conn->prepare("SELECT id, message FROM notifications WHERE phone_number = ? AND seen = 0 ORDER BY id DESC LIMIT 1");
$notifQuery->bind_param("s", $phone);
$notifQuery->execute();
$result = $notifQuery->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row); // { id: ..., message: "..." }
} else {
    echo json_encode(null);
}
