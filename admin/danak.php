<?php
require '../db.php';
$result = $conn->query("SELECT * FROM danak_ball WHERE added_by = 'admin'");

$result2 = $conn->query("SELECT phone_number, SUM(returned_ball) as total_ball, MIN(created_at) as first_return
                        FROM returned_balls
                        GROUP BY phone_number");

$totalQuery = $conn->query("SELECT SUM(overall_ball) AS total_supply FROM danak_ball");
$totalData = $totalQuery->fetch_assoc();
$totalSupply = $totalData['total_supply'] ?? 0;

// Total sold
$soldQuery = $conn->query("SELECT SUM(sell_by) AS total_sold FROM danak_ball");
$soldData = $soldQuery->fetch_assoc();
$totalSold = $soldData['total_sold'] ?? 0;

$userSalesQuery = $conn->query("
    SELECT phone_number, SUM(sell_by) AS total_balls
    FROM danak_ball
    WHERE phone_number IS NOT NULL AND phone_number != ''
    GROUP BY phone_number
    ORDER BY total_balls DESC
");



?>

<?php include 'header.php' ?>
<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <!-- Add Danak Ball -->
                <form action="add_ball_action.php" method="POST" class="p-3 border rounded mt-4 bg-light">
                    <h5>Add Danak Ball</h5>
                    <div class="mb-3">
                        <label>Danak Ball Price (UZS):</label>
                        <input type="number" name="price_ball" step="0.01" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Total Balls (overall):</label>
                        <input type="number" name="overall_ball" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Danak Ball</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4">Danak Ball List</h5>
                <div class="alert alert-info mt-4 fs-5 fw-bold">
                    <?= number_format($totalSold) ?> / <?= number_format($totalSupply) ?> Danak Ball
                </div>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Price (UZS)/ 1 Danak Ball</th>
                            <th>Total</th>
                            <th>Added By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= number_format($row['price_ball'], 2) ?></td>
                            <td><?= $row['overall_ball'] ?></td>
                            <td><?= htmlspecialchars($row['added_by']) ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <a href="edit_ball.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                <a href="delete_ball.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this?')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h4 class="mt-5">📊 Danak Ball Sotib Olinganlar Ro‘yxati</h4>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>📞 Phone Number</th>
                            <th>🪙 Balls Bought</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $index = 1;
                            while ($row = $userSalesQuery->fetch_assoc()):
                            ?>
                        <tr>
                            <td><?= $index++ ?></td>
                            <td>+998<?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><?= number_format($row['total_balls']) ?> ball</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h4 class="mt-4">📊Qaytarilgan Danak Ballar</h4>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Telefon raqami</th>
                            <th>Qaytarilgan ball (jami)</th>
                            <th>Birinchi qaytarilgan sana</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = $result2->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><?= (int)$row['total_ball'] ?></td>
                            <td><?= htmlspecialchars($row['first_return']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
</div>
<?php include 'footer.php' ?>