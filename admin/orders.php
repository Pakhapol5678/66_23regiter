<?php
session_start();
require '../config.php'; // ✅ เชื่อมฐานข้อมูล (ใช้ $pdo)
require 'auth_admin.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../function.php'; // ✅ ดึงฟังก์ชัน getOrderItems และ getShippingInfo

// ดึงคำสั่งซื้อทั้งหมด (แก้ไข: ใช้ $pdo)
try {
    $stmt = $pdo->query("
        SELECT o.*, u.username
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        ORDER BY o.order_date DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // จัดการข้อผิดพลาด
    $orders = [];
    $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการดึงข้อมูลคำสั่งซื้อ: " . $e->getMessage();
}

// อัปเดตสถานะคำสั่งซื้อ (แก้ไข: ใช้ $pdo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        $_SESSION['success_message'] = "อัปเดตสถานะคำสั่งซื้อ #" . htmlspecialchars($_POST['order_id']) . " เรียบร้อย";
        header("Location: orders.php");
        exit;
    }
    if (isset($_POST['update_shipping'])) {
        $stmt = $pdo->prepare("UPDATE shipping SET shipping_status = ? WHERE shipping_id = ?");
        $stmt->execute([$_POST['shipping_status'], $_POST['shipping_id']]);
        $_SESSION['success_message'] = "อัปเดตสถานะการจัดส่งเรียบร้อย";
        header("Location: orders.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการคำสั่งซื้อ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* 🎨 Theme: Purple-Black-Pink Accent */
        :root {
            --primary-purple: #4a235a; /* Dark Purple (Black substitute) */
            --accent-pink: #e83e8c; /* Vivid Pink */
            --light-bg: #fcf4f8; /* Light Pink/Purple background */
            --dark-text: #2c3e50;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
        }

        /* Header and Title Style */
        .page-header-container {
            border-bottom: 3px solid var(--accent-pink);
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .page-title {
            color: var(--primary-purple);
            font-weight: 700;
        }

        .icon-accent {
            color: var(--accent-pink);
        }

        /* Button Styling */
        .btn-theme {
            background-color: var(--primary-purple);
            border-color: var(--primary-purple);
            color: white;
            border-radius: 8px;
        }

        .btn-theme:hover {
            background-color: #3b1c47;
            border-color: #3b1c47;
            color: white;
        }

        .btn-update-status {
            background-color: var(--accent-pink);
            border-color: var(--accent-pink);
            border-radius: 6px;
        }

        .btn-update-status:hover {
            background-color: #d12e7b;
            border-color: #d12e7b;
        }

        .btn-update-shipping {
            background-color: #00b894; /* Green for shipping success */
            border-color: #00b894;
            border-radius: 6px;
        }

        /* Accordion Style */
        .accordion-item {
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid #f4c2c2;
        }

        .accordion-button {
            background-color: #ffffff;
            color: var(--primary-purple);
            font-weight: 600;
            border-radius: 10px 10px 0 0;
        }

        .accordion-button:not(.collapsed) {
            background-color: #fce4ec; /* Light pink background when open */
            color: var(--primary-purple);
        }

        .accordion-body {
            background-color: #ffffff;
            border-top: 1px solid #f4c2c2;
        }
        
        /* Badges for status */
        .status-badge-pending { background-color: #ffeaa7 !important; color: var(--dark-text) !important; }
        .status-badge-processing { background-color: #fdcb6e !important; color: var(--dark-text) !important; }
        .status-badge-shipped { background-color: #00b894 !important; color: white !important; }
        .status-badge-completed { background-color: var(--accent-pink) !important; color: white !important; }
        .status-badge-cancelled { background-color: #d63031 !important; color: white !important; }

        .list-group-item {
            border-color: #f4c2c2;
        }
    </style>
</head>

<body class="container mt-5">

    <div class="d-flex justify-content-between align-items-center page-header-container">
        <h2 class="fw-bold page-title">
            <i class="bi bi-journal-check me-2 icon-accent"></i> จัดการคำสั่งซื้อทั้งหมด
        </h2>
        <a href="index.php" class="btn btn-theme">
            <i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าผู้ดูแล
        </a>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i>
            <div><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="accordion" id="ordersAccordion">

        <?php if (empty($orders)): ?>
            <div class="alert alert-info text-center mt-3">
                <i class="bi bi-info-circle me-1"></i> ไม่มีคำสั่งซื้อในระบบขณะนี้
            </div>
        <?php endif; ?>
        
        <?php foreach ($orders as $index => $order): ?>

            <?php 
            // ✅ แก้ไข: ใช้ $pdo แทน $conn
            $shipping = getShippingInfo($pdo, $order['order_id']); 
            
            // กำหนดสี Badge ตามสถานะคำสั่งซื้อ
            $status_class = 'status-badge-' . strtolower($order['status']);
            ?>

            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $index ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                        <span class="fw-bold me-3">คำสั่งซื้อ #<?= $order['order_id'] ?></span> | 
                        <i class="bi bi-person-circle mx-2"></i> <?= htmlspecialchars($order['username']) ?> |
                        <i class="bi bi-calendar-event mx-2"></i> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?> | 
                        <span class="ms-3">สถานะ:</span> 
                        <span class="badge <?= $status_class ?> ms-2"><?= ucfirst($order['status']) ?></span>
                    </button>
                </h2>
                <div id="collapse<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>"
                    data-bs-parent="#ordersAccordion">
                    <div class="accordion-body">

                        <h5 class="fw-bold text-decoration-underline mb-3" style="color:var(--primary-purple);">รายการสินค้า</h5>
                        <ul class="list-group mb-4">
                            <?php 
                            // ✅ แก้ไข: ใช้ $pdo แทน $conn
                            foreach (getOrderItems($pdo, $order['order_id']) as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></span>
                                    <span class="text-muted">x <?= $item['quantity'] ?></span>
                                    <span class="fw-bold text-success"><?= number_format($item['quantity'] * $item['price'], 2) ?> บาท</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="text-end fs-5 fw-bold" style="color:var(--accent-pink);">
                            ยอดรวมสุทธิ: <?= number_format($order['total_amount'], 2) ?> บาท
                        </p>

                        <hr>
                        
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-3">
                                <h6 class="mb-0 fw-bold">อัปเดตสถานะคำสั่งซื้อ:</h6>
                            </div>
                            <div class="col-md-9">
                                <form method="post" class="row g-2">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <div class="col-md-6">
                                        <select name="status" class="form-select">
                                            <?php
                                            $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                                            foreach ($statuses as $status) {
                                                $selected = ($order['status'] === $status) ? 'selected' : '';
                                                echo "<option value=\"$status\" $selected>" . ucfirst($status) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" name="update_status" class="btn btn-update-status w-100">
                                            <i class="bi bi-arrow-clockwise"></i> อัปเดต
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <?php if ($shipping): ?>
                            <hr>
                            <h5 class="fw-bold text-decoration-underline mb-3" style="color:var(--primary-purple);">ข้อมูลจัดส่ง</h5>
                            <div class="mb-3 p-3 border rounded" style="background-color: #fff0f5;">
                                <p class="mb-1">
                                    <i class="bi bi-geo-alt-fill me-1" style="color:var(--accent-pink);"></i> 
                                    <strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-telephone-fill me-1" style="color:var(--accent-pink);"></i> 
                                    <strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?>
                                </p>
                            </div>
                            
                            <div class="row g-2 align-items-center">
                                <div class="col-md-3">
                                    <h6 class="mb-0 fw-bold">อัปเดตสถานะจัดส่ง:</h6>
                                </div>
                                <div class="col-md-9">
                                    <form method="post" class="row g-2">
                                        <input type="hidden" name="shipping_id" value="<?= $shipping['shipping_id'] ?>">
                                        <div class="col-md-6">
                                            <select name="shipping_status" class="form-select">
                                                <?php
                                                $s_statuses = ['not_shipped', 'shipped', 'delivered'];
                                                foreach ($s_statuses as $s) {
                                                    $selected = ($shipping['shipping_status'] === $s) ? 'selected' : '';
                                                    echo "<option value=\"$s\" $selected>" . ucfirst(str_replace('_', ' ', $s)) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" name="update_shipping" class="btn btn-update-shipping w-100">
                                                <i class="bi bi-truck"></i> อัปเดต
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <footer class="text-center mt-5 p-3" style="color:#95a5a6; font-size:14px; border-top: 1px solid #e1e1e1;">
        Copyright © 2025 - Developed by Computer Center. Powered by Nakhon Pathom Rajabhat University
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>