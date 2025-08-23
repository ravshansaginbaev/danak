<?php
require '../db.php';

// ✅ Function to generate a unique 10-digit action_id
function generateUniqueActionId($conn) {
    do {
        $randomId = strval(random_int(1000000000, 9999999999)); // always 10 digits, as string
        $stmt = $conn->prepare("SELECT 1 FROM actions WHERE action_id = ?");
        $stmt->bind_param("s", $randomId);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    return $randomId;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_id = generateUniqueActionId($conn); // 👈 Generate once and ensure uniqueness

    $prize_name = $_POST['prize_name'];
    $min_bid_ball = intval($_POST['min_bid_ball']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $period = $_POST['period'];
    $status = $_POST['status'];

    // ✅ Upload image
    $img_name = $_FILES['prize_img']['name'];
    $img_tmp = $_FILES['prize_img']['tmp_name'];
    $img_path = 'uploads/' . time() . '_' . basename($img_name);
    move_uploaded_file($img_tmp, $img_path);

    // ✅ Save action to database
    $stmt = $conn->prepare("INSERT INTO actions (action_id, prize_img, prize_name, min_bid_ball, start_date, end_date, period, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssis", $action_id, $img_path, $prize_name, $min_bid_ball, $start_date, $end_date, $period, $status);

    if ($stmt->execute()) {
        header("Location: game.php");
        exit();
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
