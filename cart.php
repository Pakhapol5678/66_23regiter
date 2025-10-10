<?php
session_start();
// สมมติว่าไฟล์ config.php มีการสร้าง $pdo เป็น PDO object
require 'config.php'; 

// 1. ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id']; 

// 2. เพิ่มสินค้าเข้าตะกร้า (POST Request)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id']; 
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    
    // ตรวจสอบว่าสินค้านี้อยู่ในตะกร้าแล้วหรือยัง
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        // ถ้ามีแล้ว ให้เพิ่มจำนวน
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        // ถ้ายังไม่มี ให้เพิ่มใหม่
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    header("Location: cart.php"); 
    exit;
}

// 3. ลบสินค้าออกจากตะกร้า (GET Request)
// -----------------------------
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: cart.php"); 
    exit;
}

// 4. ดึงรายการสินค้าในตะกร้าเพื่อแสดงผล
$stmt = $pdo->prepare("SELECT 
    cart.cart_id, 
    cart.quantity, 
    products.product_name, 
    products.price
FROM cart
JOIN products ON cart.product_id = products.product_id
WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. คำนวณราคารวม
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price']; 
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า - My Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 🎨 Theme: Purple-Black-Pink Accent */
        :root {
            --primary-purple: #4a235a; /* ม่วงเข้มหลัก (เกือบดำ) */
            --accent-pink: #e83e8c; /* ชมพูเน้น */
            --light-bg: #fcf4f8; /* พื้นหลังสีอ่อนมาก */
            --dark-text: #2c3e50; /* สีข้อความเข้ม */
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
            min-height: 100vh;
        }
        
        /* Header and Title Style */
        .page-header {
            color: var(--primary-purple);
            font-weight: 700;
            border-bottom: 3px solid var(--accent-pink);
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .icon-accent {
            color: var(--accent-pink);
        }

        /* Card/Table Container Style */
        .cart-container {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(74, 35, 90, 0.15); 
            padding: 35px;
            border-top: 8px solid var(--primary-purple); 
        }

        /* Table Styling */
        .table-bordered {
            border-radius: 8px;
            overflow: hidden; /* เพื่อให้มุมโค้งแสดงผลได้ดี */
            border: 1px solid #dee2e6;
        }

        .table th {
            background-color: var(--primary-purple);
            color: white;
            font-weight: 600;
            vertical-align: middle;
        }
        .table td {
            vertical-align: middle;
        }
        .table tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        /* Total Row Styling */
        .table tfoot tr, .table tbody tr:last-child {
            background-color: #fff0f5; /* ชมพูอ่อนสำหรับแถวรวม */
            border-top: 3px solid var(--accent-pink);
        }
        .total-cell strong {
            color: var(--primary-purple);
            font-size: 1.25rem;
        }

        /* Button Styling */
        .btn-secondary-theme {
            background-color: #e6e6fa; 
            border-color: #d8bfd8;
            color: var(--primary-purple);
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-secondary-theme:hover {
            background-color: #dcd0ff;
        }
        .btn-success {
            background-color: var(--accent-pink);
            border-color: var(--accent-pink);
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(232, 62, 140, 0.3);
        }
        .btn-success:hover {
            background-color: #d12e7b;
            border-color: #d12e7b;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(232, 62, 140, 0.4);
        }
        .btn-danger {
            border-radius: 6px;
        }
    </style>
</head>

<body class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-header">
            <i class="fas fa-shopping-cart me-2 icon-accent"></i> ตะกร้าสินค้า
        </h2>
        <a href="index.php" class="btn btn-secondary-theme mb-3">
            <i class="fas fa-arrow-left me-1"></i> กลับไปเลือกสินค้า
        </a>
    </div>

    <div class="cart-container">
        <?php if (count($items) === 0): ?>
            <div class="alert alert-warning text-center fw-bold fs-5">
                <i class="fas fa-exclamation-triangle me-2"></i> 
                ยังไม่มีสินค้าในตะกร้า! ไปเลือกซื้อกันเลย
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40%;">ชื่อสินค้า</th>
                            <th class="text-center" style="width: 15%;">จำนวน</th>
                            <th class="text-end" style="width: 15%;">ราคาต่อหน่วย</th>
                            <th class="text-end" style="width: 15%;">ราคารวม</th>
                            <th class="text-center" style="width: 15%;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end"><?= number_format($item['price'], 2) ?></td>
                                <td class="text-end fw-bold"><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td class="text-center">
                                    <a href="cart.php?remove=<?= (int)$item['cart_id'] ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('คุณต้องการลบสินค้านี้ออกจากตะกร้าหรือไม่?')">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end"><strong>รวมทั้งหมด:</strong></td>
                            <td class="text-end total-cell"><strong><?= number_format($total, 2) ?> บาท</strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <a href="checkout.php" class="btn btn-success btn-lg px-4">
                    <i class="fas fa-money-check-alt me-2"></i> สั่งซื้อสินค้า
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>