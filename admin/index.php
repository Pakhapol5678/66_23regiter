<?php
// ตรวจสอบ session_start() อาจถูกเรียกในไฟล์ auth_admin.php หรือ config.php แล้ว แต่เพิ่มไว้เพื่อความชัวร์
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';//เชื่อมฐานข้อมูล
require_once 'auth_admin.php';//เชื่อมฐานข้อมูล

// ตรวจสอบว่ามีข้อมูล user_name ใน session ก่อนใช้งาน
$userName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'ผู้ดูแล';

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* 🎨 Theme: Clean & Professional (Blue/Grey Accent) */
        body {
            background-color: #f4f7f9; /* Soft light background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            color: #2c3e50; /* Dark header text */
            font-weight: 700;
            padding-bottom: 10px;
            border-bottom: 3px solid #3498db; /* Blue accent line */
            margin-bottom: 25px;
        }

        .welcome-message {
            color: #7f8c8d; /* Subtle grey text */
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        /* Card/Button Link Styling */
        .admin-link-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            display: block;
            padding: 20px;
            text-align: center;
            border: 1px solid #e0e0e0;
            color: #333;
        }

        .admin-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Color Customization for Cards (Using Bootstrap Colors as a base) */
        .card-users .card-icon { color: #f39c12; } /* Warning/Yellow for Users */
        .card-category .card-icon { color: #34495e; } /* Dark/Black for Category */
        .card-orders .card-icon { color: #2ecc71; } /* Success/Green for Orders */
        .card-products .card-icon { color: #3498db; } /* Primary/Blue for Products */

        /* Logout Button */
        .btn-logout {
            background-color: #e74c3c;
            border-color: #e74c3c;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        .btn-logout:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
    </style>
</head>

<body class="container mt-5">
    <h2 class="dashboard-header">
        <i class="bi bi-speedometer2 me-2" style="color:#3498db;"></i> แผงควบคุมผู้ดูแลระบบ
    </h2>

    <p class="welcome-message">
        <i class="bi bi-person-circle me-1"></i> ยินดีต้อนรับ, **<?= $userName ?>**
    </p>

    <div class="row g-4">
        <div class="col-md-3 mb-3">
            <a href="users.php" class="admin-link-card card-users">
                <i class="bi bi-people-fill card-icon"></i>
                <div class="card-title">จัดการสมาชิก</div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="category.php" class="admin-link-card card-category">
                <i class="bi bi-tags-fill card-icon"></i>
                <div class="card-title">จัดการหมวดหมู่</div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="orders.php" class="admin-link-card card-orders">
                <i class="bi bi-receipt-cutoff card-icon"></i>
                <div class="card-title">จัดการคำสั่งซื้อ</div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="products.php" class="admin-link-card card-products">
                <i class="bi bi-box-seam-fill card-icon"></i>
                <div class="card-title">จัดการสินค้า</div>
            </a>
        </div>
    </div>

    <a href="../logout.php" class="btn btn-logout mt-5">
        <i class="bi bi-power me-1"></i> ออกจากระบบ
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>