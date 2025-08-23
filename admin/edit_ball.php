<?php
session_start();
require '../db.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM danak_ball WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$ball = $result->fetch_assoc();
?>

<form action="update_ball.php" method="POST" class="p-3 border bg-white mt-3">
    <input type="hidden" name="id" value="<?= $ball['id'] ?>">
    <h5>Edit Danak Ball</h5>
    <div class="mb-3">
        <label>Price (UZS):</label>
        <input type="number" step="0.01" name="price_ball" class="form-control" value="<?= $ball['price_ball'] ?>"
            required>
    </div>
    <div class="mb-3">
        <label>Overall Ball:</label>
        <input type="number" name="overall_ball" class="form-control" value="<?= $ball['overall_ball'] ?>" required>
    </div>
    <button type="submit" class="btn btn-success">Update</button>
</form>