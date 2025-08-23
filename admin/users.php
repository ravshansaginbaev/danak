<?php
require '../db.php';

$result = $conn->query("SELECT `id`, `phone_number`, `full_name`, `password`, `balance`, `ball`, `created_at` FROM `users`");
?>
<?php include 'header.php' ?>
<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h2>📋 All Registered Users</h2>
                    <button type="button" class="btn btn-primary align-items-center d-flex" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        <i class="mdi mdi-cash-100 me-2" style="font-size: 30px;"></i> Hisobni to`ldirish
                    </button>
                </div>

                <table class="table mt-5">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Phone Number</th>
                            <th>Full Name</th>
                            <th>Password</th>
                            <th>Balance</th>
                            <th>Ball</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td>+998<?= htmlspecialchars($row['phone_number']); ?></td>
                            <td><?= htmlspecialchars($row['full_name']); ?></td>
                            <td><?= htmlspecialchars($row['password']); ?></td>
                            <td><?= number_format($row['balance'], 2); ?> uzs</td>
                            <td><?= $row['ball']; ?></td>
                            <td><?= $row['created_at']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning editBtn" data-id="<?= $row['id']; ?>"
                                    data-phone="<?= $row['phone_number']; ?>"
                                    data-name="<?= htmlspecialchars($row['full_name']); ?>"
                                    data-password="<?= htmlspecialchars($row['password']); ?>"
                                    data-balance="<?= $row['balance']; ?>" data-ball="<?= $row['ball']; ?>">
                                    ✏️
                                </button>
                                <a href="delete_user.php?id=<?= $row['id']; ?>"
                                    onclick="return confirm('Haqiqatan ham o\'chirmoqchimisiz?')"
                                    class="btn btn-sm btn-danger">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="update_user.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Foydalanuvchini tahrirlash</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <label>Phone Number:</label>
                    <input type="text" name="phone_number" id="edit-phone" class="form-control" required>

                    <label>Full Name:</label>
                    <input type="text" name="full_name" id="edit-name" class="form-control" required>

                    <label>Password:</label>
                    <input type="text" name="password" id="edit-password" class="form-control" required>

                    <label>Balance:</label>
                    <input type="number" name="balance" id="edit-balance" class="form-control" required>

                    <label>Ball:</label>
                    <input type="number" name="ball" id="edit-ball" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Saqlash</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- Bootstrap Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="paymentForm" enctype="multipart/form-data" method="POST" action="make_payment.php"
            class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hisobni to‘ldirish</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
            </div>
            <div class="modal-body">
                <!-- User Search Input -->
                <label>Foydalanuvchi nomi yoki raqam:</label>
                <input type="text" class="form-control" id="userSearch" name="user_search" autocomplete="off" required>

                <!-- Hidden Inputs -->
                <input type="hidden" name="user_id" id="user_id">
                <input type="hidden" name="full_name" id="full_name">
                <input type="hidden" name="phone_number" id="phone_number">

                <!-- Suggestion Box -->
                <ul id="suggestions" class="list-group mt-2" style="cursor:pointer; display:none;"></ul>

                <!-- Payment amount -->
                <label class="mt-3">To‘lov miqdori (UZS):</label>
                <input type="number" step="0.01" name="payment_amount" class="form-control" required>

                <!-- Image upload -->
                <label class="mt-3">Chek rasmi (jpg/png):</label>
                <input type="file" name="img_check" class="form-control" accept="image/*" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Tasdiqlash</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('edit-id').value = this.dataset.id;
        document.getElementById('edit-phone').value = this.dataset.phone;
        document.getElementById('edit-name').value = this.dataset.name;
        document.getElementById('edit-password').value = this.dataset.password;
        document.getElementById('edit-balance').value = this.dataset.balance;
        document.getElementById('edit-ball').value = this.dataset.ball;

        var myModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        myModal.show();
    });
});
</script>


<script>
document.getElementById('userSearch').addEventListener('input', function() {
    const query = this.value.trim();
    if (query.length < 2) return;

    fetch('search_users.php?q=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            const suggestions = document.getElementById('suggestions');
            suggestions.innerHTML = '';
            if (data.length > 0) {
                suggestions.style.display = 'block';
                data.forEach(user => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.textContent = user.full_name + ' (+998' + user.phone_number + ')';
                    li.onclick = () => {
                        document.getElementById('userSearch').value = user.full_name;
                        document.getElementById('user_id').value = user.id;
                        document.getElementById('full_name').value = user.full_name;
                        document.getElementById('phone_number').value = user.phone_number;
                        suggestions.style.display = 'none';
                    };
                    suggestions.appendChild(li);
                });
            } else {
                suggestions.style.display = 'none';
            }
        });
});
</script>
<?php include 'footer.php' ?>