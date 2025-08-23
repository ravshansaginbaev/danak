<?php
require 'db.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT id, ball, price FROM target_sell WHERE status = 0 ORDER BY id DESC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
