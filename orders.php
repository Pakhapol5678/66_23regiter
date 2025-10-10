<?php
session_start();
require 'config.php';
require 'function.php'; // 🚩 ต้องแน่ใจว่าไฟล์นี้มีฟังก์ชัน getOrderItems(), getShippingInfo(), getStatusText(), และ getStatusBadgeClass()

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit;
}
$user_id = $_SESSION['user_id']; 

// ดึงคำสั่งซื้อของผู้ใช้ (ใช้ PDO)
try {
    // 🚩 แก้ไข: ใช้ $pdo แทน $conn เพื่อให้สอดคล้องกับตัวอย่างโค้ด PDO
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // จัดการข้อผิดพลาดถ้าดึงข้อมูลไม่ได้
    $orders = [];
    // ควรจัดการ error อย่างเหมาะสม ไม่ควรแสดงให้ผู้ใช้เห็น
    // echo "Database Error: " . $e->getMessage(); 
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --color-primary: #6a1b9a; /* ม่วงหลัก */
            --color-secondary: #e83e8c; /* ชมพู Accent */
            --color-bg-light: #f7f7f7;
            --color-text-dark: #333333;
        }

        body {
            background: linear-gradient(135deg, #f0f0f0 0%, #ffe0f5 50%, #f0f0f0 100%);
            color: var(--color-text-dark);
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
        }

        h2 {
            color: var(--color-primary);
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
        }

        .btn-secondary {
            background-color: #9c27b0; /* ม่วงเข้ม */
            border-color: #9c27b0;
            transition: all 0.3s;
        }
        .btn-secondary:hover {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }

        /* Card Style */
        .card {
            border: 1px solid #ddd;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s;
        }
        .card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        /* Card Header */
        .card-header {
            background-color: #fce4ec !important; /* ชมพูอ่อนมาก */
            border-bottom: 2px solid var(--color-secondary);
            color: var(--color-text-dark);
            font-size: 1.05rem;
            padding: 1rem 1.5rem;
        }
        .card-header strong {
            color: var(--color-primary);
        }

        /* Card Body - Item List */
        .list-group-item {
            border: none;
            padding: 0.5rem 0;
            border-left: 3px solid var(--color-secondary); /* เส้นชมพู */
            margin-left: -1.5rem;
            padding-left: 1.25rem;
            background: transparent;
        }
        
        .card-body p {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .card-body p strong {
            color: var(--color-primary);
        }

        /* Alert Styling */
        .alert-success {
            background-color: #e8f5e9;
            color: #388e3c;
            border-left: 5px solid #4CAF50;
        }
        .alert-warning {
            background-color: #fff3e0;
            color: #f57c00;
            border-left: 5px solid #FF9800;
        }
    </style>
</head>

<body class="container mt-4">
    <h2 class="text-center">
        <i class="fas fa-history me-2"></i> ประวัติการสั่งซื้อของคุณ
    </h2>
    <a href="index.php" class="btn btn-secondary mb-4">
        <i class="fas fa-arrow-left me-2"></i> กลับหน้าหลัก
    </a>
    
    <?php if (isset($_GET['success'])): ?> 
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> ทำรายการสั่งซื้อเรียบร้อยแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (count($orders) === 0): ?>
        <div class="alert alert-warning p-4 text-center">
            <i class="fas fa-box-open me-2 fa-2x d-block mb-2"></i>
            <h4>คุณยังไม่เคยสั่งซื้อสินค้า</h4>
            <p>เลือกซื้อสินค้าที่เราแนะนำได้เลย!</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between flex-wrap">
                    <div>
                        <strong>รหัสคำสั่งซื้อ:</strong> <span class="text-decoration-underline">#<?= $order['order_id'] ?></span>
                    </div>
                    <div>
                        <strong>วันที่:</strong> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?>
                    </div>
                    <div>
                        <strong>สถานะ:</strong> 
                        <span class="badge rounded-pill <?= getStatusBadgeClass($order['status']) ?>">
                            <?= getStatusText($order['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="text-muted mb-3" style="font-weight: 600;">รายการสินค้าในคำสั่งซื้อ:</h6>
                    <ul class="list-group mb-3">
                        <?php 
                        // 🚩 แก้ไข: ใช้ $pdo แทน $conn เพื่อให้สอดคล้องกับการเรียกใช้ฟังก์ชัน PDO
                        foreach (getOrderItems($pdo, $order['order_id']) as $item): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>
                                    <?= htmlspecialchars($item['product_name']) ?> <small class="text-muted">(x<?= $item['quantity'] ?>)</small>
                                </span>
                                <strong>
                                    <?= number_format($item['price'] * $item['quantity'], 2) ?> บาท
                                </strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <p class="text-end fw-bold fs-5 pt-2 border-top">
                        <strong>ยอดรวมทั้งสิ้น:</strong> <span style="color: var(--color-secondary);"><?= number_format($order['total_amount'], 2) ?> บาท</span>
                    </p>
                    
                    <?php 
                    // 🚩 แก้ไข: ใช้ $pdo แทน $conn เพื่อให้สอดคล้องกับการเรียกใช้ฟังก์ชัน PDO
                    $shipping = getShippingInfo($pdo, $order['order_id']); ?>
                    <?php if ($shipping): ?>
                        <div class="p-3 mt-3" style="border: 1px dashed #ddd; border-radius: 0.5rem; background-color: #fafafa;">
                            <p class="mb-1">
                                <i class="fas fa-map-marker-alt me-2" style="color: var(--color-primary);"></i>
                                <strong>ที่อยู่จัดส่ง:</strong> <?= htmlspecialchars($shipping['address']) ?>,
                                <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?>
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-phone-alt me-2" style="color: var(--color-primary);"></i>
                                <strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-shipping-fast me-2" style="color: var(--color-primary);"></i>
                                <strong>สถานะการจัดส่ง:</strong> 
                                <span class="badge rounded-pill text-bg-info">
                                    <?= ucfirst($shipping['shipping_status']) ?>
                                </span>
                            </p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php 
// 🚨 โค้ดฟังก์ชันที่ซ้ำซ้อนถูกลบออกแล้ว
// โปรดตรวจสอบว่าฟังก์ชันเหล่านี้อยู่ในไฟล์ function.php แทน
?>