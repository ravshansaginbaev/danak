<?php
require '../db.php';
include 'header.php';

$res = $conn->query("SELECT * FROM target_sell ORDER BY created_at DESC");
?>

<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h4 class="mb-3">Danak Ball Sotuv Jarayoni</h4>
        <table class="table table-bordered mt-3">
          <thead class="table-dark text-center">
            <tr>
              <th>ID</th>
              <th>Ball</th>
              <th>Narxi</th>
              <th>Sotuvchi</th>
              <th>Xaridor</th>
              <th>Status</th>
              <th>Vaqti</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php while ($row = $res->fetch_assoc()): ?>
              <tr class="<?= $row['status'] == 1 ? 'table-success' : 'table-warning' ?>">
                <td><?= $row['id'] ?></td>
                <td><?= $row['ball'] ?></td>
                <td><?= number_format($row['price'], 0, ',', ' ') ?> UZS</td>
                <td><?= htmlspecialchars($row['sell_by']) ?></td>
                <td><?= $row['status'] == 1 ? htmlspecialchars($row['buy_by']) : '—' ?></td>
                <td>
                  <?php if ($row['status'] == 1): ?>
                    <span class="badge bg-success">✅ Sotildi</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">⏳ Sotilmagan</span>
                  <?php endif; ?>
                </td>
                <td><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<!-- Optional: auto refresh every 30 seconds -->
<script>
  setInterval(() => {
    location.reload();
  }, 30000); // 30 seconds
</script>
