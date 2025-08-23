<?php
session_start();
require '../db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $user, $pass);
    $stmt->fetch();

    if ($password === $pass) { // For plaintext password only
        $_SESSION['admin_id'] = $id;
        $_SESSION['username'] = $user; // ✅ store username here
        header("Location: index.php");
        exit();
    }
}

$_SESSION['admin_toast'] = "❌ Login failed!";
header("Location: index.php");
exit();
