<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get last payment ID from session or DB
if (!isset($_SESSION['last_payment_id'])) {
    $check = $conn->query("SELECT MAX(id) as max_id FROM make_payment");
    $row = $check->fetch_assoc();
    $_SESSION['last_payment_id'] = $row['max_id'] ?? 0;
}

// Get latest danak ball price
$result = $conn->query("SELECT price_ball FROM danak_ball ORDER BY id DESC LIMIT 1");
$current_price = $result->num_rows > 0 ? (int)$result->fetch_assoc()['price_ball'] : 0;

// Toast message handling
$toast = '';
$toast_type = 'success'; // can be 'success', 'danger', etc.

if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    $toast_type = $_SESSION['toast_type'] ?? 'success';
    unset($_SESSION['toast'], $_SESSION['toast_type']);
}

// Fetch user info
$stmt = $conn->prepare("SELECT full_name, balance, ball FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $balance, $ball);
$stmt->fetch();
$stmt->close(); // ❗️ This fixes "commands out of sync"

// 1. Get admin's overall_ball from danak_ball
$adminQuery = $conn->query("
    SELECT IFNULL(SUM(overall_ball), 0) AS admin_ball
    FROM danak_ball
    WHERE added_by = 'admin' AND sell_by IS NULL AND phone_number IS NULL
");
$adminRow = $adminQuery->fetch_assoc();
$admin_ball = $adminRow['admin_ball'];

// 2. Get total ball from users
$userQuery = $conn->query("SELECT IFNULL(SUM(ball), 0) AS user_ball FROM users");
$userRow = $userQuery->fetch_assoc();
$user_ball = $userRow['user_ball'];

// 3. Get total sold (SUM of sell_by)
$soldQuery = $conn->query("SELECT IFNULL(SUM(sell_by), 0) AS total_sold FROM danak_ball");
$soldRow = $soldQuery->fetch_assoc();
$total_sold = $soldRow['total_sold'];

// 4. Total available = admin_ball + user_ball
$total_available = $admin_ball + $user_ball;

$sql = "SELECT IFNULL(SUM(sell_by), 0) AS total_sold FROM danak_ball";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_sold = intval($row['total_sold']);

$base_price = 2432;
$increment = 50;

echo "<script>
    const total_sold = $total_sold;
    const base_price = $base_price;
    const increment = $increment;
</script>";


$sql = "SELECT overall_ball FROM danak_ball WHERE overall_ball > 0 ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

$overall_ball = 0;
if ($row = $result->fetch_assoc()) {
    $overall_ball = $row['overall_ball'];
}

?>

<?php if (!empty($toast)): ?>
<div id="toastMsg"
    class="toast align-items-center text-white bg-<?= $toast_type ?> border-0 position-fixed bottom-0 end-0 m-3"
    role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
    <div class="d-flex">
        <div class="toast-body">
            <?= htmlspecialchars($toast) ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
    </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const toastEl = document.getElementById('toastMsg');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();

        setTimeout(() => {
            location.reload();
        }, 3000); // Reload page after 3 seconds
    }
});
</script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');

    body {
        background: linear-gradient(to bottom, #A139CD 0%, #15041C 100%);
        background-repeat: no-repeat;
    }

    .navbar-brand img {
        width: 69px;
        height: 69px;
    }

    .navbar-brand span {
        font-size: 32px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff;
        margin-left: 15px;
    }


    .navbar-nav li {
        font-size: 18px;
        font-family: "Inter", sans-serif;
        color: #fff;
    }

    .footer_text {
        text-align: center;
        margin-top: 100px;
        padding-bottom: 70px;
    }

    footer .footer_text h1 {
        font-family: "Inter", sans-serif;
        font-weight: lighter;
        padding-top: 50px;
        border-top: 1px solid #fff;
        width: 20%;
        margin: auto;
        color: #fff !important;
        font-size: 40px;
    }

    .target_buy input,
    .target_buy button,
    .target_sell button {
        height: 61.6px;
    }

    /* target */

    .target_notifactoin h3 {
        color: #16c60c;
    }

    .target_info h5 {
        font-size: 30px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff;
    }
    </style>
</head>

<body>



    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="user_page.php">
                <img src="./img/logo.png" alt="">
                <span>Danak Bonus</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav  mb-2 mb-lg-0 flex-column">
                    <li><?php echo htmlspecialchars($full_name); ?></li>
                    <li class="nav-item">
                        <b>Hisob:</b> <?php echo number_format($balance, 2); ?> uzs
                    </li>
                    <li class="nav-item">
                        <b>Danak:</b> <?php echo (int)$ball; ?> ball
                    </li>
                </ul>

            </div>
        </div>
    </nav>

    <!-- target -->

    <section id="target">
        <div class="container">
            <!-- <div class="row">
                <div class="col-lg-12">
                    <div class="target_notifactoin text-center">
                        <h3>✅ 2 dona danak sotuvga qo`ydingiz</h3>
                    </div>
                </div>
            </div> -->

            <div class="row mt-5">
                <div class="col-lg-4">
                    <div class="target_buy d-flex align-items-center justify-content-between gap-3">
                        <input type="number" id="quantity" class="form-control d-inline w-50 " placeholder="Necha dona?"
                            style="width: 120px;">
                        <button class="btn btn-info w-50" onclick="openBuyModal()">Sotib olish</button>
                    </div>
                </div>
                <div class="col-lg-8">
                    <form class="target_sell d-flex gap-3" action="sell_danak.php" method="POST" class="d-flex gap-2">
                        <input type="number" name="ball" class="form-control" placeholder="Soni" required min="1">
                        <input type="number" name="price" class="form-control" placeholder="Narxi" required min="100">
                        <button type="submit" class="btn btn-danger px-5" style="white-space: nowrap;"> Sotuvga
                            qo‘yish</button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="target_info text-center mt-5">
                        
                        <h5>Qolgan Danaklar Soni: <strong><?= number_format($overall_ball) ?></strong> dona</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- footer -->

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer_text">
                        <h1>Danak Bonuslari</h1>
                    </div>
                </div>
            </div>
        </div>
    </footer>


    <div class="modal fade" id="buyModal" tabindex="-1" aria-labelledby="buyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tasdiqlash</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
                </div>
                <div class="modal-body">
                    <p>Siz <span id="confirmQty"></span> dona danak ball sotib olmoqchisiz.</p>
                    <p>Narxi: <span id="confirmPrice"></span> UZS</p>
                    <p>Jami: <strong id="confirmTotal"></strong> UZS</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="buy_ball.php">
                        <input type="hidden" name="quantity" id="hiddenQty">
                        <input type="hidden" name="total_cost" id="hiddenCost">
                        <button type="submit" class="btn btn-success">Tasdiqlash</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($toast): ?>
    <div id="toastMsg"
        class="toast align-items-center text-white bg-<?= $toast_type ?> border-0 position-fixed bottom-0 end-0 m-3"
        role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($toast) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>

    <script>
    window.addEventListener('DOMContentLoaded', () => {
        const toastEl = document.getElementById('toastMsg');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl, {
                delay: 3000
            });
            toast.show();

            // Automatically reload after 3 seconds
            setTimeout(() => {
                location.reload();
            }, 3000);
        }
    });
    </script>
    <?php endif; ?>


    <script>
    function openBuyModal() {
        const qty = parseInt(document.getElementById('quantity').value);
        if (!qty || qty <= 0) {
            alert("Iltimos, soni kiriting.");
            return;
        }

        // Calculate total price using arithmetic progression
        const start_index = total_sold; // how many already sold
        const a = base_price + (start_index * increment); // price of the first ball the user will buy
        const total = (qty / 2) * (2 * a + (qty - 1) * increment);

        document.getElementById('confirmQty').textContent = qty;
        document.getElementById('confirmPrice').textContent = a.toLocaleString(); // Starting price
        document.getElementById('confirmTotal').textContent = Math.round(total).toLocaleString();
        document.getElementById('hiddenQty').value = qty;
        document.getElementById('hiddenCost').value = Math.round(total);

        const modal = new bootstrap.Modal(document.getElementById('buyModal'));
        modal.show();
    }
    </script>







    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
</body>

</html>