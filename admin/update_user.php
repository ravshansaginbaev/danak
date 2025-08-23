<?php
require '../db.php';

$id = $_POST['id'];
$phone = $_POST['phone_number'];
$name = $_POST['full_name'];
$password = $_POST['password'];
$balance = $_POST['balance'];
$ball = $_POST['ball'];

$stmt = $conn->prepare("UPDATE users SET phone_number=?, full_name=?, password=?, balance=?, ball=? WHERE id=?");
$stmt->bind_param("sssddi", $phone, $name, $password, $balance, $ball, $id);
$stmt->execute();

header("Location: users.php");
