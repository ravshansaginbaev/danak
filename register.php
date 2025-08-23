<?php
session_start();
require 'db.php';

$phone_number = trim($_POST['phone_number']);
$full_name = trim($_POST['full_name']);
$password = trim($_POST['password']);

// 🔍 Check if phone number already exists
$check = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
$check->bind_param("s", $phone_number);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $_SESSION['toast'] = "⚠️ This phone number is already registered!";
    $_SESSION['toast_type'] = "error";
    header("Location: index.php");
    exit();
}
$check->close();

// ✅ Insert new user
$stmt = $conn->prepare("INSERT INTO users (phone_number, full_name, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $phone_number, $full_name, $password);

if ($stmt->execute()) {
    $_SESSION['toast'] = "🎉 Registration successful!";
    $_SESSION['toast_type'] = "success";
    header("Location: index.php");
    exit();
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
