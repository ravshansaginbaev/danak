<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$userStmt = $conn->prepare("SELECT phone_number FROM users WHERE id = ?");
$userStmt->bind_param("i", $_SESSION['user_id']);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$phone = $user['phone_number'];

$notifQuery = $conn->prepare("SELECT id, message FROM notifications WHERE phone_number = ? AND seen = 0 ORDER BY id DESC");
$notifQuery->bind_param("s", $phone);
$notifQuery->execute();
$notifs = $notifQuery->get_result();


if (!isset($_SESSION['last_payment_id'])) {
    $check = $conn->query("SELECT MAX(id) as max_id FROM make_payment");
    $row = $check->fetch_assoc();
    $_SESSION['last_payment_id'] = $row['max_id'] ?? 0;
}

// Fetch user details
$stmt = $conn->prepare("SELECT full_name, balance, ball FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $balance, $ball);
$stmt->fetch();
$stmt->close();


// Get latest active auction
$sql = "SELECT * FROM `actions` WHERE `status` = 'active' ORDER BY `id` DESC LIMIT 1";
$result = $conn->query($sql);
$auction = $result->fetch_assoc();

// ✅ Get latest active auction
$actionResult = $conn->query("SELECT * FROM `actions` WHERE `status` = 'active' ORDER BY `id` DESC LIMIT 1");
if ($actionResult && $actionResult->num_rows > 0) {
    $action = $actionResult->fetch_assoc();
}

// ✅ Get user
if ($user_id) {
    $userResult = $conn->query("SELECT * FROM `users` WHERE `id` = $user_id");
    if ($userResult && $userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        $user_ball = (int)$user['ball'];
    }
}




$sql = "SELECT overall_ball FROM danak_ball WHERE overall_ball > 0 ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

$overall_ball = 0;
if ($row = $result->fetch_assoc()) {
    $overall_ball = $row['overall_ball'];
}

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

<?php while ($row = $notifs->fetch_assoc()): ?>
<div class="alert alert-info alert-dismissible fade show position-fixed bottom-0 end-0 m-3 shadow" role="alert">
    <?= htmlspecialchars($row['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
        onclick="markNotificationSeen(<?= $row['id'] ?>)"></button>
</div>
<?php endwhile; ?>



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

    .navbar-nav li {
        font-size: 24px;
        font-family: "Inter", sans-serif;
        color: #fff;
    }

    .navbar-brand span {
        font-size: 32px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff;
        margin-left: 15px;
    }


    .footer_text {
        text-align: center;
        margin-top: 100px;
        padding-bottom: 70px;
    }

    .footer_text h1 {
        font-family: "Inter", sans-serif;
        font-weight: lighter;
        padding-top: 50px;
        border-top: 1px solid #fff;
        width: 20%;
        margin: auto;
        color: #fff !important;
        font-size: 40px;
    }


    /* main */

    #main {
        padding-top: 70px;
        padding-bottom: 70px;
    }

    .main_row {
        border-bottom: 2px solid #fff;
        padding-bottom: 70px;
    }

    .main_btn .buy,
    .main_btn .sell {
        background-color: #73D3FF !important;
        border-radius: 10px;
        border: none;
        padding: 31px 60px;
        font-size: 24px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #111;
        width: 100%;
        max-width: 249px;
        text-decoration: none;
    }

    .main_btn .sell {
        background-color: #FF8585 !important;
    }

    .main_push {
        display: flex;
        flex-direction: column;
        text-align: center;
        justify-content: center;
        align-items: center;
    }

    .main_push h1 {
        font-size: 24px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff;
        width: 70%;
    }

    .main_push .push {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 12px !important;
        width: 100% !important;
        padding: 23px 37px;
        background-color: #ffffff54 !important;
        margin-top: 10px;
    }

    .main_push .push h1 {
        font-size: 24px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #111 !important;
        text-align: start;
        margin: 0;
    }

    .main_push .push h2 {
        font-size: 20px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff;
        width: 100%;
        margin: 0;
    }

    .main_push .push button {
        border: none;
        background-color: #64FF4D;
        padding: 9px 36px;
        margin-left: 10px;
        border-radius: 10px;
        font-size: 20px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #111;
    }

    .game_title h1 {
        font-size: 28px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff;
        text-align: center;
    }

    .game_prize ul {
        list-style: none;
    }

    .game_prize ul li,
    .game_time h4 {
        font-size: 24px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff;
    }

    .game_attend button {
        background: #DE91FF;
        font-family: "Inter", sans-serif;
        font-weight: 700;
        font-style: Bold;
        font-size: 24px;
        leading-trim: NONE;
        line-height: 100%;
        letter-spacing: 0%;
        color: #000;
        padding: 17px 45px;
        border: none;
        border-radius: 10px;

    }

    @media (max-width: 768px) {
        .home__title h2 {
            font-size: 46px;
        }

        .home__title h1 {
            font-size: 69px;

        }

        .news_info.mobile h1 {
            display: none;
        }

        footer .footer_text h1 {
            width: 100%;
        }

        .main_btn .buy,
        .main_btn .sell {
            padding: 7px 30px;
            font-size: 19px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        #main {
            padding-top: 20px;
            padding-bottom: 20px;
        }
    }

    @media (max-width: 576px) {
        .card-title {
            font-size: 16px;
        }

        .big-number {
            font-size: 24px;
        }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="./img/logo.png" alt="">
                <span>Danak Bonus</span>
            </a>



            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">

                <ul class="navbar-nav  mb-2 mb-lg-0 gap-4">
                    <li>
                        <?php
                        if ($user_id) {
                                $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
                                $phone = $user['phone_number'];

                                $check = $conn->query("SELECT * FROM action_users WHERE phone_number = '$phone'");
                                if ($check->num_rows > 0) {
                                    echo "<a href='user_game.php' class='btn btn-primary'>🎮</a>";
                                } else {
                                    
                                }
                            } else {
                                echo "<p>Iltimos, tizimga kiring.</p>";
                            }
                        ?>
                    </li>
                    <li>Qolgan Danaklar Soni: <strong><?= number_format($overall_ball) ?></strong> dona</li>
                    <li class="nav-item">
                        <b>Hisob:</b> <?php echo number_format($balance, 2); ?> uzs
                    </li>
                    <li class="nav-item">
                        <b>Danak:</b> <?php echo (int)$ball; ?> ball
                    </li>
                    <!-- <li class="nav-item">
                        <b>Danak:</b> <?php echo (int)$phone; ?> ball
                    </li> -->
                </ul>

            </div>
        </div>
    </nav>

    <!-- main -->

    <section id="main">
        <div class="container">
            <div class="main_row">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Salom, <?php echo htmlspecialchars($full_name); ?>!</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="main_btn gap-4 d-flex">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#buy" class="buy">Sotib
                                Olish</button>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#sell"
                                class="sell">Sotish</button>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="main_push">
                            <h1>Ishtrokchilar tomonidan Ballar Sotilmoqda:</h1>
                            <div id="sellDanakContainer" class="d-flex flex-wrap gap-3 mt-3 w-100"></div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>


    <section id="game">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="game_title ">
                        <h1>Qimmat Baho Texnikalarni Ballarga almashtring:</h1>
                    </div>
                </div>
            </div>

            <div class="mt-5"></div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="game_prize">
                        <ul>
                            <li>5 dona Iphone 15 Pro Max</li>
                            <li>5 dona Smartfon</li>
                            <li>5 dona Smart Televizor</li>
                            <li>1 dona Kir yuvish mashinasi</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="game_time">
                        <h4 class="text-center">Boshlanadi</h4>

                        <?php if ($auction): ?>
                        <div style=" padding: 20px; text-align: center; border-radius: 12px; width: 100%;">
                            <div
                                style="color: red; font-weight: bold; font-size: 20px; display: flex; justify-content: space-around;">
                                <div>Kun</div>
                                <div>Soat</div>
                                <div>Daqiqa</div>
                                <div>Soniya</div>
                            </div>

                            <div id="countdown"
                                style="color: white; font-weight: bold; font-size: 48px; display: flex; justify-content: space-around; margin-top: 10px;">
                                <div id="days">0</div>
                                <div id="hours">00</div>
                                <div id="minutes">00</div>
                                <div id="seconds">00</div>
                            </div>
                        </div>

                        <script>
                        const endTime = new Date("<?= $auction['end_date'] ?>").getTime();

                        function updateCountdown() {
                            const now = new Date().getTime();
                            const distance = endTime - now;

                            if (distance <= 0) {
                                document.getElementById("countdown").innerHTML = "⏳ Boshlandi!";
                                return;
                            }

                            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            document.getElementById("days").innerText = days;
                            document.getElementById("hours").innerText = hours.toString().padStart(2, '0');
                            document.getElementById("minutes").innerText = minutes.toString().padStart(2, '0');
                            document.getElementById("seconds").innerText = seconds.toString().padStart(2, '0');
                        }

                        updateCountdown();
                        setInterval(updateCountdown, 1000);
                        </script>
                        <?php else: ?>
                        <p class="text-center text-white">Aktiv auktsion topilmadi.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="game_attend text-white text-center">
                        <?php if ($action &&  $user): ?>
                        <form method="POST" class="mb-4 mt-4" action="join_action.php" onsubmit="return checkBall();">
                            <input type="hidden" name="action_id" value="<?= $action['action_id'] ?>">
                            <button type="submit">Ishtirok qilish</button>
                        </form>

                        <!-- Modal for Low Ball -->
                        <div id="ballModal" class="modal" tabindex="-1"
                            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6);">
                            <div
                                style="background:transparent; padding:20px; max-width:400px; margin:100px auto; border-radius:10px; text-align:center;">
                                <h4>❌ Sizda yetarli ball mavjud emas</h4>
                                <button onclick="document.getElementById('ballModal').style.display='none'"
                                    class="btn btn-danger mt-3">Yopish</button>
                            </div>
                        </div>

                        <!-- Modal for Not Started Yet -->
                        <div id="notStartedModal" class="modal" tabindex="-1"
                            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6);">
                            <div
                                style="background:transparent; padding:20px; max-width:400px; margin:100px auto; border-radius:10px; text-align:center;">
                                <h4>❌ Hali auksion boshlanmagan</h4>
                                <button onclick="document.getElementById('notStartedModal').style.display='none'"
                                    class="btn btn-danger mt-3">Yopish</button>
                            </div>
                        </div>

                        <script>
                        function checkBall() {
                            let userBall = <?= (int)$user_ball ?>;
                            let startDate = new Date("<?= $action['end_date'] ?>");
                            let now = new Date();

                            if (now < startDate) {
                                document.getElementById('notStartedModal').style.display = 'block';
                                return false;
                            }

                            if (userBall < 10) {
                                document.getElementById('ballModal').style.display = 'block';
                                return false;
                            }

                            return true;
                        }
                        </script>
                        <?php else: ?>
                        <!-- <p>❌ Auktsion yoki foydalanuvchi topilmadi.</p> -->
                        <?php endif; ?>

                        <b>Ishtrok qilishingiz uchun 1 bal taqdim qilasiz. va xisobingizda 10 ball dan yuqori bo’lishi
                            shart!</b>
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

    <!-- Modal -->
    <div class="modal fade" id="buy" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Sotib Olish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="target_buy d-flex align-items-center justify-content-between gap-3">
                        <input type="number" id="quantity" class="form-control d-inline w-50 " placeholder="Necha dona?"
                            style="width: 120px;">
                        <button class="btn btn-info w-50" onclick="openBuyModal()">Sotib olish</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2 -->
    <div class="modal fade" id="sell" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Sotish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="target_sell d-flex gap-3" action="sell_danak.php" method="POST" class="d-flex gap-2">
                        <input type="number" name="ball" class="form-control" placeholder="Soni" required min="1">
                        <input type="number" name="price" class="form-control" placeholder="Narxi" required min="100">
                        <button type="submit" class="btn btn-danger px-5" style="white-space: nowrap;"> Sotuvga
                            qo‘yish</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



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




    <!-- Modal for time not started -->
    <div id="timeModal" class="modal" tabindex="-1"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6);">
        <div
            style="background:transparent; padding:20px; max-width:400px; margin:100px auto; border-radius:10px; text-align:center;">
            <h4>⏳ Auksion hali boshlanmagan</h4>
            <button onclick="document.getElementById('timeModal').style.display='none'"
                class="btn btn-warning mt-3">Yopish</button>
        </div>
    </div>

    <!-- Toast container -->
    <!-- <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    🔔 Yangi to‘lov kiritildi!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div> -->

    <script>
    function fetchSellDanaks() {
        fetch('get_sell_danaks.php')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('sellDanakContainer');
                container.innerHTML = '';

                if (data.length === 0) {
                    container.innerHTML = "<p>Hozircha hech kim danak joylamagan.</p>";
                    return;
                }

                data.forEach(item => {
                    const block = document.createElement('div');
                    block.className = 'push p-3 rounded shadow-sm bg-light text-center';
                    block.innerHTML = `
                    <h1>${item.ball} Bal</h1>
                    <h2>${parseInt(item.price).toLocaleString()} uzs</h2>
                    <form action="buy_from_user.php" method="POST">
                        <input type="hidden" name="sell_id" value="${item.id}">
                        <button class="btn btn-success">Olish</button>
                    </form>
                `;
                    container.appendChild(block);
                });
            });
    }

    // Initial load + repeat every 5 seconds
    fetchSellDanaks();
    setInterval(fetchSellDanaks, 5000);
    </script>

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


    <script>
    function showToastAndReload() {
        const toastEl = document.getElementById('liveToast');
        const toast = new bootstrap.Toast(toastEl, {
            delay: 3000
        });
        toast.show();

        // Reload after toast finishes (3s)
        setTimeout(() => {
            location.reload();
        }, 3000);
    }

    function checkNewPayments() {
        fetch('check_payment.php')
            .then(res => res.json())
            .then(data => {
                if (data.new) {
                    showToastAndReload();
                }
            })
            .catch(err => console.error("Check error:", err));
    }

    // Poll every 10 seconds
    setInterval(checkNewPayments, 10000);
    </script>

    <script>
    let shownNotifs = new Set();

    function checkForNotifications() {
        fetch('get_notifications.php')
            .then(res => res.json())
            .then(data => {
                if (data && !shownNotifs.has(data.id)) {
                    shownNotifs.add(data.id);
                    showNotification(data.id, data.message);
                }
            });
    }

    function showNotification(id, message) {
        const div = document.createElement('div');
        div.className = 'alert alert-info alert-dismissible fade show position-fixed bottom-0 end-0 m-3 shadow';
        div.role = 'alert';
        div.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
        document.body.appendChild(div);

        // ✅ Initialize Bootstrap alert manually
        bootstrap.Alert.getOrCreateInstance(div);
    }


    function markNotificationSeen(id) {
        fetch("mark_notification_seen.php?id=" + id);
    }

    // check every 5 seconds
    setInterval(checkForNotifications, 5000);
    </script>




    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>

</body>

</html>