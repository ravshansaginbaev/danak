<?php
session_start();
require 'db.php';

$phone_number = trim($_POST['phone_number']);
$password = $_POST['password'];

// Find user by phone number and password directly
$stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ? AND password = ?");
$stmt->bind_param("ss", $phone_number, $password);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($user_id);
    $stmt->fetch();

    // Login success
    $_SESSION['user_id'] = $user_id;
    $_SESSION['toast'] = "✅ Welcome!";
    $_SESSION['toast_type'] = "success";
    header("Location: user_page.php");
    exit();
} else {
    // Login failed
    $_SESSION['toast'] = "❌ Incorrect phone number or password.";
    $_SESSION['toast_type'] = "error";
    header("Location: index.php");
    exit();
}
?>
