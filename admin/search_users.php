<?php
require '../db.php';

$q = $_GET['q'] ?? '';
$q = "%$q%";

$stmt = $conn->prepare("SELECT id, full_name, phone_number FROM users WHERE full_name LIKE ? OR phone_number LIKE ?");
$stmt->bind_param("ss", $q, $q);
$stmt->execute();

$result = $stmt->get_result();
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
