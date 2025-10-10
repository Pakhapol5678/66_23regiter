<?php
require_once 'config.php';//เชื่อมฐานข้อมูล
session_start();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();

}
$product_id = $_GET['id'];
// ควรตรวจสอบให้แน่ใจว่าได้ดึงคอลัมน์ description (ถ้ามี)
$sql = "SELECT p.*, c.category_name, p.description FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$isLoggedIn = isset($_SESSION['user_id']);

// ตรวจสอบว่า $product มีข้อมูลหรือไม่ ก่อนใช้งาน
if (!$product) {
    header('Location: index.php');
    exit();
}

$img = !empty($product['image'])
    ? 'product_images/' . rawurlencode($product['image'])
    : 'product_images/no-image.jpg';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายละเอียดสินค้า: <?= htmlspecialchars($product['product_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 🎨 โทนสี: ม่วง (#6a0dad), ชมพู (#ff69b4), ดำ (#333), ขาว (#ffffff) */
        body {
            background-color: #d4ceceff; /* สีขาว/เทาอ่อนมาก */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333; /* สีดำสำหรับข้อความทั่วไป */
        }

        .card {
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(106, 0, 173, 0.1); /* เงาสีม่วงอ่อนๆ */
        }

        /* การปรับขนาดและสไตล์รูปภาพ */
        .product-image {
            width: 100%; /* ใช้พื้นที่เต็มความกว้าง */
            max-width: 500px; /* กำหนดความกว้างสูงสุดเพื่อให้ภาพไม่ใหญ่เกินไปบนจอใหญ่ */
            height: auto;
            max-height: 500px; /* กำหนดความสูงสูงสุด */
            object-fit: contain; /* ปรับให้ภาพแสดงครบ ไม่ถูกตัด */
            border-radius: 12px;
            margin: 1rem auto;
            display: block;
            border: 2px solid #6a0dad; /* ขอบสีม่วง */
        }

        .card-title {
            color: #6a0dad; /* สีม่วงสำหรับชื่อสินค้า */
            font-weight: 800;
            font-size: 2.25rem;
            margin-bottom: 0.5rem;
        }

        .category-text {
            color: #ff69b4; /* สีชมพูสำหรับหมวดหมู่ */
            font-weight: 600;
            font-size: 1.1rem;
            border-bottom: 1px dashed #e0e0e0;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .price-text {
            font-size: 1.8rem;
            color: #333; /* สีดำ */
            font-weight: bold;
            background-color: #ff69b440; /* พื้นหลังสีชมพูอ่อนๆ */
            display: inline-block;
            padding: 0.2rem 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .stock-text {
            color: #6a0dad; /* สีม่วงสำหรับสถานะคงเหลือ */
            font-weight: 600;
        }

        /* ปุ่มและฟอร์ม */
        .btn-secondary {
            background-color: #333; /* สีดำสำหรับปุ่มกลับ */
            border-color: #333;
            transition: background-color 0.3s;
        }
        .btn-secondary:hover {
            background-color: #6a0dad; /* ม่วงเมื่อโฮเวอร์ */
            border-color: #6a0dad;
        }
        .btn-success {
            background-color: #ff69b4; /* สีชมพูสำหรับปุ่มเพิ่มในตะกร้า */
            border-color: #ff69b4;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-success:hover {
            background-color: #e65a9f;
            border-color: #e65a9f;
        }

        .form-control, #quantity {
            border: 1px solid #6a0dad; /* กรอบสีม่วง */
            border-radius: 8px;
        }
    </style>
</head>

<body class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-4">
        <i class="fas fa-arrow-left me-2"></i> กลับหน้ารายการสินค้า
    </a>

    <div class="card">
        <div class="row g-0">
            <div class="col-md-6 d-flex justify-content-center align-items-center p-3">
                <img src="<?= $img ?>" class="product-image" alt="รูปสินค้า: <?= htmlspecialchars($product['product_name']) ?>">
            </div>

            <div class="col-md-6">
                <div class="card-body p-4">
                    <h3 class="card-title">
                        <?= htmlspecialchars($product['product_name']) ?>
                    </h3>
                    <h6 class="category-text">หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></h6>

                    <p class="card-text mb-4">
                        <strong>รายละเอียด:</strong> <?= htmlspecialchars($product['description'] ?? 'ไม่มีรายละเอียดสินค้า') ?>
                    </p>

                    <p class="price-text">
                        <i class="fas fa-tags me-2"></i> ราคา: <?= htmlspecialchars($product['price']) ?> บาท
                    </p>

                    <p class="stock-text mb-4">
                        <i class="fas fa-box me-2"></i> คงเหลือ: <?= htmlspecialchars($product['stock']) ?> ชิ้น
                    </p>

                    <?php if ($isLoggedIn): ?>
                        <form action="cart.php" method="post" class="mt-4">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <div class="d-flex align-items-center mb-3">
                                <label for="quantity" class="me-3 fw-bold">จำนวน:</label>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?=
                                    $product['stock'] ?>" required class="form-control me-3" style="width: 100px;">
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-cart-plus me-2"></i> เพิ่มในตะกร้า
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mt-4" style="border-left: 5px solid #6a0dad; color: #6a0dad; background-color: #6a0dad10;">
                            <i class="fas fa-lock me-2"></i> กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>