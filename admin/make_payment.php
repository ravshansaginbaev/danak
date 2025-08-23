<?php
require '../db.php';
session_start();

$user_id = $_POST['user_id'];
$full_name = $_POST['full_name'];
$phone_number = $_POST['phone_number'];
$payment_amount = floatval($_POST['payment_amount']);

// Upload image
$img_name = '';
if (isset($_FILES['img_check']) && $_FILES['img_check']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['img_check']['name'], PATHINFO_EXTENSION);
    $img_name = 'check_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    move_uploaded_file($_FILES['img_check']['tmp_name'], 'uploads/' . $img_name);
}

// Insert into make_payment
$stmt = $conn->prepare("INSERT INTO make_payment (user_id, user_name, phone_number, payment_amount, img_check) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issds", $user_id, $full_name, $phone_number, $payment_amount, $img_name);
$stmt->execute();

// Update user balance
$update = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
$update->bind_param("di", $payment_amount, $user_id);
$update->execute();

// Redirect back
$_SESSION['toast'] = "✅ Hisob muvaffaqiyatli to‘ldirildi.";
$_SESSION['toast_type'] = "success";
header("Location: users.php");
exit();
