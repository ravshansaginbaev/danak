<?php
session_start();
$message = '';
$type = '';
if (isset($_SESSION['toast'])) {
    $message = $_SESSION['toast'];
    $type = $_SESSION['toast_type'];
    unset($_SESSION['toast']);
    unset($_SESSION['toast_type']);
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
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 20px;
        border-radius: 5px;
        color: white;
        opacity: 0;
        transition: opacity 0.5s ease;
        z-index: 9999;
    }

    .toast.show {
        opacity: 1;
    }

    .success {
        background: #28a745;
    }

    .error {
        background: #dc3545;
    }

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

    .span {
        padding: 8px 24px !important;
        background-color: #FF8400 !important;
        border-radius: 10px;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff !important;
        font-size: 24px;
    }

    .home__title {
        display: flex;
        flex-direction: column;
        align-items: center;
        border-bottom: 2px solid #fff;
        padding-bottom: 20px;
    }

    .home__title a {
        /* width: 247px; */
        text-decoration: none;
        margin-top: 70px;
    }

    .home__title h2 {
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff !important;
        font-size: 96px;
        margin: 0 !important;
        text-align: center;

    }

    .home__title h1 {
        text-align: center;
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff !important;
        font-size: 128px;
        margin: 0 !important;

    }

    /*  */
    #news {
        padding-top: 50px;
    }

    .news_img {
        display: flex;
        justify-content: center;
    }

    .news_img img {
        width: 100%;
        max-width: 412px;

    }

    .news_info {
        display: flex;
        align-items: center;
        justify-content: center;
        height: auto;
    }

    .news_info h1 {
        font-family: "Inter", sans-serif;
        font-weight: bold;
        color: #fff !important;
        font-size: 40px;
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

    @media (max-width: 768px) {
            .home__title h2{
                font-size: 46px;
            }
            .home__title h1{
                font-size: 69px;

            }
            .news_info.mobile h1{
                display: none;
            }
            footer .footer_text h1{
                width: 100%;
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

  


    <?php if (!empty($message)): ?>
    <div id="toast" class="toast <?php echo $type; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <script>
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
    </script>
    <?php endif; ?>

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
                    <li class="nav-item">
                        <button class="nav-link span border-0" href="#" data-bs-toggle="modal"
                            data-bs-target="#staticBackdrop2">Kirish</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link span border-0" href="#" data-bs-toggle="modal"
                            data-bs-target="#staticBackdrop">Ro’yxatdan o’tish</button>
                    </li>
                </ul>

            </div>
        </div>
    </nav>

    <!-- home -->

    <section id="home">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="home__title">
                        <h2>Bonuslar</h2>
                        <h1>Sovg’alar</h1>
                        <h2>Birinchi qadam Omadli</h2>
                        <a class="span" href="#">ilk qadam</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- card -->

    <section id="news">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="news_img">
                        <img src="./img/iphone.png" alt="">
                    </div>
                </div>
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="news_info">
                        <h1>Ballar orqali Qimmatbaho
                            Sovg’alarni qo’lga kiriting!</h1>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="news_info mobile">
                        <h1>Ballar orqali Qimmatbaho
                            Sovg’alarni qo’lga kiriting!</h1>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="news_img">
                        <img src="./img/tv.png" alt="">
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

    <!-- Modal register-->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Ro’yxatdan o’tish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class action="register.php" method="POST">
                        <label class="form-label">Phone Number:</label><br>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">+998</span>
                            <input type="text" class="form-control" placeholder="Username" name="phone_number"
                                aria-label="Username" aria-describedby="basic-addon1">
                        </div>

                        <label class="form-label">Full Name:</label><br>
                        <input type="text" name="full_name" class="form-control" required><br><br>

                        <label class="form-label">Password:</label><br>
                        <input type="password" name="password" class="form-control" required><br><br>

                        <button class="border-0 span" type="submit">Register</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal login-->
    <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Kirish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class action="login.php" method="POST">
                        <label class="form-label">Phone Number:</label><br>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">+998</span>
                            <input type="text" class="form-control" placeholder="Username" name="phone_number"
                                aria-label="Username" aria-describedby="basic-addon1">
                        </div> <br>

                        <label class="form-label">Password:</label><br>
                        <input type="password" name="password" class="form-control" required> <br>

                        <button class="border-0 span" type="submit">Kirish</button>
                    </form>
                </div>

            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
</body>

</html>