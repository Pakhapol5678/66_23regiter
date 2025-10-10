<?php
session_start();
require_once 'config.php';
$isLoggedIn = isset($_SESSION['user_id']);
// ตัวอย่างข้อมูลสินค้า (สามารถดึงจากฐานข้อมูลจริงได้)
$products = [
    [
        'image' => 'product_images/no-image.jpg',
        'name' => 'เสื้อยืดแฟชั่น',
        'category' => 'เสื้อผ้า',
        'price' => 299.00,
        'desc' => 'เสื้อยืดผ้าคอตตอน 100% ใส่สบาย ระบายอากาศดี',
        'isNew' => true,
        'isHot' => false
    ],
    [
        'image' => 'product_images/no-image.jpg',
        'name' => 'รองเท้าผ้าใบ',
        'category' => 'รองเท้า',
        'price' => 899.00,
        'desc' => 'รองเท้าผ้าใบดีไซน์ทันสมัย พื้นนุ่ม ใส่เดินสบาย',
        'isNew' => false,
        'isHot' => true
    ],
    [
        'image' => 'product_images/no-image.jpg',
        'name' => 'กระเป๋าสะพาย',
        'category' => 'กระเป๋า',
        'price' => 499.00,
        'desc' => 'กระเป๋าสะพายข้างแฟชั่น ใส่ของได้เยอะ',
        'isNew' => false,
        'isHot' => false
    ]
];
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
            background: linear-gradient(120deg, #f8e1ff 0%, #fff 60%, #ffe3f0 100%);
            color: #232323;
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .main-container {
            min-height: 100vh;
        }
        .content-card {
            background: linear-gradient(120deg, #fff 80%, #e3e3e3 100%);
            padding: 2.5rem 2rem 2rem 2rem;
            border-radius: 1rem;
            box-shadow: 0 6px 24px rgba(60,60,60,0.10), 0 1.5px 0 #232323;
            text-align: center;
            border-top: 6px solid #c850c0;
        }
        h1 {
            color: #c850c0;
            font-weight: 700;
            border-bottom: 2px solid #23232333;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
            text-shadow: 1px 1px 0 #fff, 2px 2px 0 #23232322;
        }
        p {
            font-size: 1.1rem;
            margin-top: 1rem;
            margin-bottom: 2rem;
            color: #555;
        }
        .btn-success {
            background: linear-gradient(90deg, #c850c0 0%, #ffb6b9 100%);
            border: none;
            color: #fff;
        }
        .btn-primary {
            background: linear-gradient(90deg, #4f8cff 0%, #c850c0 100%);
            border: none;
            color: #fff;
        }
        .btn-info {
            background: linear-gradient(90deg, #ffb6b9 0%, #c850c0 100%);
            border: none;
            color: #fff;
        }
        .btn-warning {
            background: linear-gradient(90deg, #ffe3f0 0%, #f8e1ff 100%);
            border: none;
            color: #c850c0;
        }
        .btn-secondary {
            background: #3a235b;
            border: none;
            color: #fff;
        }
        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(79,140,255,0.08);
        }
        .btn:hover {
            opacity: 0.95;
            transform: translateY(-2px) scale(1.03);
        }
        span.me-3 {
            color: #c850c0;
            font-weight: 600;
            padding: 0.25rem 0.7rem;
            border-radius: 6px;
            background-color: #ffe3f0;
        }
        .product-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(60,60,60,0.10), 0 1.5px 0 #23232311;
            transition: box-shadow 0.2s, transform 0.2s;
            background: linear-gradient(120deg, #fff 80%, #f3e6f9 100%);
        }
        .product-card:hover {
            box-shadow: 0 8px 32px rgba(60,60,60,0.18), 0 2px 0 #23232322;
            transform: translateY(-4px) scale(1.02);
        }
        .card-img-top {
            border-radius: 1rem 1rem 0 0;
            object-fit: cover;
            height: 180px;
            box-shadow: 0 4px 16px 0 rgba(200,80,192,0.13), 0 1.5px 0 #c850c0;
            border: 3px solid #fff;
            background: linear-gradient(120deg, #fff 60%, #f8e1ff 100%);
            transition: box-shadow 0.25s, border-color 0.25s, filter 0.25s;
            position: relative;
        }
        .product-card:hover .card-img-top {
            box-shadow: 0 8px 32px 0 rgba(60,60,60,0.18), 0 2px 0 #c850c0;
            border-color: #c850c0;
            filter: brightness(1.07) saturate(1.15);
        }
        .badge {
            font-size: 0.9em;
            padding: 0.5em 0.8em;
        }
        .card-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #333;
        }
        .card-text {
            font-size: 0.98rem;
            color: #666;
        }
        .price {
            color: #c850c0;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .footer {
            color:#232323; font-size:14px; opacity:0.7;
        }
    </style>
</head>

<body class="container mt-4">
    <div class="main-container d-flex flex-column align-items-center justify-content-center">
        <div class="content-card mt-5 mb-4 w-100" style="max-width: 1000px;">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h1><i class="fas fa-book-open me-2" style="color: #c850c0;"></i>สินค้าแนะนำ</h1>
                <div class="mb-2 mb-md-0">
                    <?php if ($isLoggedIn): ?>
                        <span class="me-3">ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</span>
                        <a href="profile.php" class="btn btn-info">ข้อมูลส่วนตัว</a>
                        <a href="cart.php" class="btn btn-warning">ดูตะกร้า</a>
                        <a href="logout.php" class="btn btn-secondary">ออกจากระบบ</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-success">เข้าสู่ระบบ</a>
                        <a href="register.php" class="btn btn-primary">สมัครสมาชิก</a>
                    <?php endif; ?>
                </div>
            </div>
            <p class="mb-4">ระบบร้านค้าออนไลน์ที่ใช้งานง่ายและปลอดภัย<br>เลือกซื้อสินค้าได้ตลอด 24 ชั่วโมง</p>
            <div class="row g-4 justify-content-center">
                <?php foreach ($products as $p): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card product-card h-100">
                        <img src="<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1"> <?= htmlspecialchars($p['name']) ?> </h5>
                            <div class="mb-2 text-secondary" style="font-size:0.95em;">หมวดหมู่: <?= htmlspecialchars($p['category']) ?></div>
                            <div class="mb-2 text-truncate" style="font-size:0.97em;"> <?= htmlspecialchars($p['desc']) ?> </div>
                            <?php if ($p['isNew']): ?>
                                <span class="badge bg-success mb-2">NEW</span>
                            <?php elseif ($p['isHot']): ?>
                                <span class="badge bg-danger mb-2">HOT</span>
                            <?php endif; ?>
                            <div class="price mb-3"> <?= number_format($p['price'], 2) ?> บาท</div>
                            <a href="#" class="btn btn-primary mt-auto">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <footer class="footer text-center mt-4 mb-2">
            © 2025 ระบบร้านค้าออนไลน์ | Nawapath
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>