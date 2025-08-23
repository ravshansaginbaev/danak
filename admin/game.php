<?php
// game.php
require '../db.php';

$result = $conn->query("SELECT action_id, prize_name, winner, status, start_date, end_date FROM actions ORDER BY id DESC");
?>
<?php include 'header.php' ?>
<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2>Create New Auction</h2>
                <form class="form-control bg-white" action="admin_save_action.php" method="POST"
                    enctype="multipart/form-data">
                    <label>Prize Name:</label><br>
                    <input class="form-control" type="text" name="prize_name" required><br><br>

                    <label>Prize Image:</label><br>
                    <input class="form-control" type="file" name="prize_img" accept="image/*" required><br><br>

                    <label>Min Bid Ball:</label><br>
                    <input class="form-control" type="number" name="min_bid_ball" required><br><br>

                    <label>Start Date & Time:</label><br>
                    <input class="form-control" type="datetime-local" name="start_date" required><br><br>

                    <label>End Date & Time:</label><br>
                    <input class="form-control" type="datetime-local" name="end_date" required><br><br>

                    <label>Period (in minutes):</label><br>
                    <input class="form-control" type="number" name="period" required><br><br>

                    <label>Status:</label><br>
                    <select name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select><br><br>

                    <input class="form-control" type="submit" value="Create Auction">
                </form>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <h2 style="text-align: center;">🎯 All Auction Actions</h2>
                <table class="table mt-4">
                    <thead>
                        <tr>
                            <th>Action ID</th>
                            <th>Prize Name</th>
                            <th>Winner</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['action_id'] ?></td>
                            <td><?= htmlspecialchars($row['prize_name']) ?></td>
                            <td><?= $row['winner'] ?: '❓ Pending' ?></td>
                            <td><?= ucfirst($row['status']) ?></td>
                            <td><?= $row['start_date'] ?></td>
                            <td><?= $row['end_date'] ?></td>
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