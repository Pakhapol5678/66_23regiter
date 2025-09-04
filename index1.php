<?php
session_start();

require_once 'config.php';//เชื่อมฐานข้อมูล
$isLoggedIn = isset($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            /* Gradient background from red to yellow */
            background: linear-gradient(to right, #ff416c, #ff4b2b, #ffda00);
            color: #333;
            /* Dark text for readability */
            min-height: 100vh;
        }

        .main-container {
            min-height: 100vh;
        }

        .content-card {
            background-color: #ffebee;
            /* Light pink background for the card */
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h1 {
            color: #d32f2f;
            /* Dark red for the heading */
            font-weight: bold;
        }

        p {
            font-size: 1.25rem;
            margin-top: 1rem;
            margin-bottom: 2rem;
            color: #c2185b;
            /* A shade of pink for the user info */
        }

        .logout-link {
            font-size: 1.1rem;
            color: #b71c1c;
            /* Darker red for the link */
            text-decoration: none;
            border: 2px solid #b71c1c;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .logout-link:hover {
            background-color: #b71c1c;
            color: #fff;
        }
    </style>
</head>

<body class="container mt-4">
    <div class=" d-flex justify-content-between align-items-center mb-4">
        <h1><i class=" fas fa-home me-2"></i>รายการสินค้า</h1>
        <div>
            <?php
            if ($isLoggedIn): ?>
                <span class="me-3">ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']) ?> (
                    <?=
                        $_SESSION['role'] ?>)
                </span>
                <a href="profile.php" class="btn btn-info">ข้อมูลส่วนตัว</a>
                <a href="cart.php" class="btn btn-warning">ดูตะกร้า</a>
                <a href="logout.php" class="btn btn-secondary">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-success">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn btn-primary">สมัครสมาชิก</a>



            <?php endif; ?>
        </div>
        <p>ผู้ใช้: **<?= htmlspecialchars($_SESSION['username']) ?>**
            (<?= htmlspecialchars($_SESSION['role']) ?>)
        </p>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>