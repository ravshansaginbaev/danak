<?php
session_start();
require '../db.php';

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM danak_ball WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$_SESSION['admin_toast'] = "🗑️ Danak Ball deleted!";
header("Location: danak.php");
exit();
