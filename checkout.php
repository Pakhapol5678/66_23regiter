<?php
session_start();
require 'config.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit;
}
$user_id = $_SESSION['user_id']; 

// ดึงรายการสินค้าในตะกร้า
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, cart.product_id, products.product_name, products.price FROM cart JOIN products ON cart.product_id = products.product_id WHERE cart.user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

// ค ำนวณรำคำรวม
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price']; 
}

$errors = [];

// กำหนดค่าเริ่มต้นสำหรับฟอร์ม (Sticky Form)
$address = ''; 
$city = ''; 
$postal_code = ''; 
$phone = ''; 


// เมอื่ ผใู้ชก้ดยนื ยันค ำสั่งซอื้ (method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $address = trim($_POST['address']); 
    $city = trim($_POST['city']); 
    $postal_code = trim($_POST['postal_code']); 
    $phone = trim($_POST['phone']); 
    
    // ตรวจสอบการกรอกข้อมูล
    if (empty($address) || empty($city) || empty($postal_code) || empty($phone)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบถ้วน"; 
    }
    
    // ตรวจสอบเบอร์โทรศัพท์ (เพิ่มการตรวจสอบเบื้องต้น)
    if (!empty($phone) && !preg_match("/^[0-9]{9,10}$/", $phone)) {
        $errors[] = "รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง";
    }

    if (empty($items)) {
        $errors[] = "ตะกร้าสินค้าว่างเปล่า ไม่สามารถดำเนินการต่อได้";
    }


    if (empty($errors)) {
        // เริ่ม transaction
        $conn->begin_transaction();
        try {
            // บันทึกข้อมูลการสั่งซื้อ
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param('id', $user_id, $total);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // บันทึกรายการสินค้าใน order_items
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                // ต้องใช้ $item['price'] เป็นราคาต่อหน่วย ณ ขณะนั้น
                $stmtItem->bind_param('iiid', $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmtItem->execute();
            }
            
            // บันทึกข้อมูลการจัดส่ง
            $stmt = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $order_id, $address, $city, $postal_code, $phone);
            $stmt->execute();
            
            // ล้างตะกร้าสินค้า
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            
            // ยืนยันการบันทึก
            $conn->commit();
            header("Location: orders.php?success=1"); 
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "เกิดข้อผิดพลาดในการทำรายการ: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการสั่งซื้อ</title>
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

        .checkout-card {
            background-color: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            border-top: 5px solid var(--color-primary);
        }

        h2 {
            color: var(--color-primary);
            font-weight: 700;
            margin-bottom: 2rem;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }

        h5 {
            color: var(--color-secondary);
            font-weight: 600;
            margin-top: 1.5rem;
        }

        .list-group-item {
            border-left: 5px solid var(--color-secondary);
            border-radius: 0.5rem !important;
            margin-bottom: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            color: var(--color-text-dark);
        }

        .list-group-item.text-end {
            background-color: var(--color-primary);
            color: var(--color-bg-light);
            border: none;
            font-size: 1.15rem;
            margin-top: 10px;
        }

        .list-group-item.text-end strong {
            color: #fff;
            text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.2);
        }
        
        .form-label {
            color: var(--color-primary);
            font-weight: 600;
        }

        .form-control:focus {
            border-color: var(--color-secondary);
            box-shadow: 0 0 0 0.25rem rgba(232, 62, 140, 0.25);
        }

        .btn-success {
            background-color: var(--color-secondary); /* ปุ่มหลักเป็นสีชมพู */
            border-color: var(--color-secondary);
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-success:hover {
            background-color: #c82365;
            border-color: #c82365;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #777;
            border-color: #777;
            color: #fff;
            font-weight: 500;
        }
        .btn-secondary:hover {
            background-color: #555;
            border-color: #555;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        /* Layout adjustment for form */
        .address-form-section {
            padding-top: 2rem;
            border-top: 1px dashed #ddd;
            margin-top: 2rem;
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center p-4">
    <div class="checkout-card col-md-10 col-lg-8">
        <h2 class="text-center">
            <i class="fas fa-shopping-basket me-2"></i> ยืนยันการสั่งซื้อ
        </h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <h5><i class="fas fa-list-alt me-2"></i> รายการสินค้า</h5>
                <ul class="list-group">
                    <?php if (empty($items)): ?>
                        <li class="list-group-item text-center">ตะกร้าสินค้าว่างเปล่า</li>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    **<?= htmlspecialchars($item['product_name']) ?>** <span class="badge text-bg-secondary ms-2">x<?= $item['quantity'] ?></span>
                                </div>
                                <span class="fw-bold text-success" style="color: var(--color-secondary) !important;">
                                    <?= number_format($item['price'] * $item['quantity'], 2) ?> บ.
                                </span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item text-end">
                            <strong>ยอดรวมทั้งหมด: <?= number_format($total, 2) ?> บาท</strong>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="col-lg-7 address-form-section">
                <h5><i class="fas fa-map-marker-alt me-2"></i> ข้อมูลการจัดส่ง</h5>
                <form method="post" class="row g-3">
                    <div class="col-12">
                        <label for="address" class="form-label">ที่อยู่ (บ้านเลขที่, ถนน, ซอย)</label>
                        <input type="text" name="address" id="address" class="form-control" 
                            value="<?= htmlspecialchars($address) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="city" class="form-label">จังหวัด</label>
                        <input type="text" name="city" id="city" class="form-control" 
                            value="<?= htmlspecialchars($city) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control" 
                            value="<?= htmlspecialchars($postal_code) ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="phone" class="form-label">เบอร์โทรศัพท์</label> 
                        <input type="text" name="phone" id="phone" class="form-control" 
                            value="<?= htmlspecialchars($phone) ?>" required>
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-4">
                        <a href="cart.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> กลับตะกร้า
                        </a> 
                        <button type="submit" class="btn btn-success btn-lg" 
                                <?= empty($items) ? 'disabled' : '' ?>>
                            <i class="fas fa-check-circle me-2"></i> ยืนยันคำสั่งซื้อ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>