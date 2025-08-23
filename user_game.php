<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$joined_action_id = $_SESSION['joined_action_id'] ?? null;

if (!$user_id || !$joined_action_id) {
    die("⛔ Ruxsat yo'q. Avval ishtirok eting.");
}

// Get user's phone number
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$phone = $user['phone_number'] ?? null;

// Check if this user registered for the current action
$check = $conn->query("SELECT * FROM action_users WHERE action_id = $joined_action_id AND phone_number = '$phone'");
if ($check->num_rows === 0) {
    die("❌ Siz bu o‘yinga ro‘yxatdan o‘tmagansiz.");
}


$stmt = $conn->prepare("SELECT full_name, balance, ball FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $balance, $ball);
$stmt->fetch();
$stmt->close();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Siz tizimga kirmagansiz.");
}

// Get logged-in user data
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$my_phone = $user['phone_number'] ?? '';

// Get all users (for name lookup by phone)
$userMap = [];
$result = $conn->query("SELECT phone_number, full_name FROM users");
while ($row = $result->fetch_assoc()) {
    $userMap[$row['phone_number']] = $row['full_name'];
}

$actionUsers = $conn->query("
    SELECT id, action_id, phone_number, min_bid_ball, bid_ball, winner, time 
    FROM action_users 
    WHERE winner = '0' 
    ORDER BY bid_ball DESC
");







$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$user_phone = $user['phone_number'] ?? '';

// Get latest finished auction
$auction = $conn->query("SELECT * FROM actions WHERE status = 'finished' ORDER BY id DESC LIMIT 1")->fetch_assoc();

$showWinnerModal = false;
$prize_img = $prize_name = $winner_name = '';
$isUserWinner = false;

if ($auction && $auction['winner']) {
    $showWinnerModal = true;
    $prize_img = $auction['prize_img'];
    $prize_name = $auction['prize_name'];
    $winner_phone = $auction['winner'];

    $winner_user = $conn->query("SELECT full_name FROM users WHERE phone_number = '$winner_phone'")->fetch_assoc();
    $winner_name = $winner_user['full_name'] ?? $winner_phone;

    $isUserWinner = ($winner_phone === $user_phone);
}

$action_id = $_SESSION['joined_action_id'] ?? null;

if (!$action_id) {
    header("Location: user_page.php");
    exit();
}

// Check auction status
$stmt = $conn->prepare("SELECT status FROM actions WHERE action_id = ?");
$stmt->bind_param("i", $action_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || $row['status'] === 'finished') {
    header("Location: user_page.php");
    exit();
}
?>
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

    .info h1 {}

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

    #user_game table {
        margin-top: 20px;
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
                <ul class="navbar-nav  mb-2 mb-lg-0 gap-4">
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

    <section id="user_game">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="info text-center text-white">
                        <h1>Xush kelibsiz, <?= htmlspecialchars($user['full_name']) ?>!</h1>
                        <p>Oyin Raqami: <?= $joined_action_id ?></p>
                        <p>Siz o‘yinda ishtirok etyapsiz. Omad!</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="row">
                        <?php
$result = $conn->query("SELECT id, action_id, prize_img, prize_name, min_bid_ball, start_date, end_date, period, status, winner 
                        FROM actions 
                        WHERE status = 'active' AND action_id = $joined_action_id 
                        LIMIT 1");

if ($result && $result->num_rows > 0):
    $row = $result->fetch_assoc();
?>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <img src="./admin/<?= htmlspecialchars($row['prize_img']) ?>" class="card-img-top"
                                        alt="Prize Image">
                                    <div class="card-body text-center">
                                        <h5 class="card-title"><?= htmlspecialchars($row['prize_name']) ?></h5>
                                        <p class="card-text">
                                            G'olib:
                                            <?= $row['winner'] ? htmlspecialchars($row['winner']) : '<span class="text-muted">Hali yo‘q</span>' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <form method="POST" action="submit_bid.php">
                                    <input type="hidden" name="action_id" value="<?= $joined_action_id ?>">
                                    <input type="hidden" name="phone_number" value="<?= $user['phone_number'] ?>">

                                    <div class="d-flex user_game_text flex-column gap-3">
                                        <label for="bid_ball" class="form-control">Taklif qilinayotgan ball:</label>
                                        <input type="number" class="form-control" name="bid_ball" min="5" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary mt-2">➕ Taklif qilish</button>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">❗ Hech qanday faol o‘yin topilmadi.</div>
                        <?php endif; ?>

                    </div>



                </div>
                <div class="col-lg-6">
                    <table class="table table-bordered text-white">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Foydalanuvchi</th>
                                <th>Taklif ball</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $i = 1;
                                while ($row = $actionUsers->fetch_assoc()):
                                    $isMyRow = $row['phone_number'] === $my_phone;
                                    $fullName = $userMap[$row['phone_number']] ?? 'Nomaʼlum';
                                ?>
                            <tr style="<?= $isMyRow ? 'background-color: #d1e7dd75;' : '' ?>">
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($fullName) ?></td>
                                <td><strong><?= (int)$row['bid_ball'] ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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

    <?php if ($showWinnerModal): ?>
    <div class="modal fade" id="winnerModal" tabindex="-1" aria-labelledby="winnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h5 class="modal-title text-success w-100" id="winnerModalLabel">🏆 Auktsion G'olibi</h5>
                </div>
                <div class="modal-body">
                    <img src="./admin/<?= $prize_img ?>" alt="Prize"
                        style="max-width: 100%; height: auto; border-radius: 10px;">
                    <h4 class="mt-3"><?= htmlspecialchars($prize_name) ?></h4>
                    <p class="mt-2">
                        G'olib:
                        <span class="<?= $isUserWinner ? 'text-success fw-bold' : 'text-primary' ?>">
                            <?= htmlspecialchars($winner_name) ?>
                        </span>
                        <?= $isUserWinner ? '🎉 (Siz gʻolibsiz!)' : '' ?>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Yopish</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>




    <script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($showWinnerModal): ?>
        var winnerModal = new bootstrap.Modal(document.getElementById('winnerModal'));
        winnerModal.show();
        <?php endif; ?>
    });
    </script>



    <script>
    setInterval(() => {
        fetch('check_auction_winner.php')
            .then(res => res.text())
            .then(data => console.log(data));
    }, 5000); // Every 5 seconds
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>

</body>

</html>