<?php
session_start(); // เริ่มต้น session เพื่อจัดการการเข้าสู่ระบบ
require_once 'config.php';
// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่
$isLoggedIn = isset($_SESSION['user_id']);

$stmt = $conn->query("SELECT p.*, c.category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้ำหลัก - ร้านค้าออนไลน์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>

    </style>
</head>

<body class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ยินดีต้อนรับสู่หน้าหลัก</h2>
        <div class="">
            <?php
            if ($isLoggedIn): ?>
                <span class="me-3">ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']) ?>
                    (<?= $_SESSION['role'] ?>)</span>
                <a href="profile.php" class="btn btn-info">ข้อมูลส่วนตัว</a>
                <a href="cart.php" class="btn btn-warning">ดูตะกร้าสินค้า</a>
                <a href="logout.php" class="btn btn-secondary">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-success">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn btn-primary">สมัครสมาชิก</a>

            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($product['category_name']) ?>
                        </h6>
                        <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        <p><strong>รำคำ:</strong> <?= number_format($product['price'], 2) ?>บาท</p>
                        <?php if ($isLoggedIn): ?>
                            <form action="cart.php" method="post" class="d-inline">
                                <input type="hidden" name="product_id" value="<?= ($product['product_id']) ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-success">เพิ่มในตะกร้า</button>
                            </form>
                        <?php else: ?>
                            <small class="text-muted">เข้าสู่ระบบเผื่อสั่งซื้อ</small>
                        <?php endif; ?>
                        <a href="product_detail.php?id=<?= $product['product_id'] ?>"
                            class="btn btn-sm btn-outline-primary float-end">ดูรายละเอียด</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
        </script>
</body>

</html>