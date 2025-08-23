<?php
session_start();
require 'db.php';
$sells = $conn->query("SELECT * FROM target_sell WHERE status = 0 ORDER BY created_at DESC");
?>

<table class="table mt-4">
    <thead>
        <tr>
            <th>Sotuvchi</th>
            <th>Soni</th>
            <th>Narxi</th>
            <th>Jami</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = $sells->fetch_assoc()): ?>
        <tr>
            <td>+998<?= htmlspecialchars($row['sell_by']) ?></td>
            <td><?= $row['ball'] ?></td>
            <td><?= number_format($row['price'], 2) ?> uzs</td>
            <td><?= number_format($row['ball'] * $row['price'], 2) ?> uzs</td>
            <td>
                <form action="buy_from_user.php" method="POST">
                    <input type="hidden" name="sell_id" value="<?= $row['id'] ?>">
                    <button class="btn btn-success btn-sm" type="submit">Sotib olish</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
