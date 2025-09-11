<?php
require_once '../config.php';//เชื่อมฐานข้อมูล
require_once 'auth_admin.php';//เชื่อมฐานข้อมูล

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>ระบบผู้ดูแลระบบ</h2>
    <p class="mb-4">ยินดีต ้อนรับ, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
    <div class="row">
        <div class="col-md-4 mb-3">
            <a href="users.php" class="btn btn-warning w-100">จัดกำรสมำชกิ</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="category.php" class="btn btn-dark w-100">จัดกำรหมวดหมู่</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="orders.php" class="btn btn-success w-100">จัดกำรค ำสั่งซอื้ </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="products.php" class="btn btn-primary w-100">จัดกำรสนิ คำ้</a>
        </div>
    </div>
    <a href="../logout.php" class="btn btn-secondary mt-3">ออกจำกระบบ</a>
</body>

</html>