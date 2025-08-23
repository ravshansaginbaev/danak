<?php
session_start();
require 'db.php';

$last_id = $_SESSION['last_payment_id'] ?? 0;

$stmt = $conn->prepare("SELECT id FROM make_payment WHERE id > ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $last_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($new_id);
    $stmt->fetch();
    $_SESSION['last_payment_id'] = $new_id;
    echo json_encode(['new' => true]);
} else {
    echo json_encode(['new' => false]);
}
