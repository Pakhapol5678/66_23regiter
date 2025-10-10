<?php
// ตรวจสอบ session_start() อาจถูกเรียกในไฟล์ auth_admin.php หรือ config.php แล้ว
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config.php'; // ✅ เชื่อมต่อฐานข้อมูลด้วย PDO
require 'auth_admin.php'; // ✅ การ์ดสิทธิ์ (Admin Guard)

// ตรวจสอบว่าได้ส่ง id สินค้ามาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}
$product_id = (int)$_GET['id'];

// ดึงข้อมูลสินค้า
// ใช้ $pdo แทน $conn
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // เพิ่มการแจ้งเตือน session error ก่อน redirect
    $_SESSION['error_message'] = "ไม่พบข้อมูลสินค้าที่ต้องการแก้ไข (ID: $product_id)";
    header("Location: products.php");
    exit;
}

// ดึงหมวดหมู่ทั้งหมด
// ใช้ $pdo แทน $conn
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// เมื่อมีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name         = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price'];
    $stock       = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];

    // ค่ารูปเดิมจากฟอร์ม
    $oldImage     = $_POST['old_image'] ?? null;
    $removeImage = isset($_POST['remove_image']); // true/false

    if ($name && $price > 0) {
        
        // เตรียมตัวแปรรูปที่จะบันทึก
        $newImageName = $oldImage; // default: คงรูปเดิมไว้

        // 1) ถ้ามีติ๊ก "ลบรูปเดิม" → ตั้งให้เป็น null
        if ($removeImage) {
            $newImageName = null;
        }

        // 2) ถ้ามีอัปโหลดไฟล์ใหม่ → ตรวจแล้วเซฟไฟล์และตั้งชื่อใหม่ทับค่า
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];

            // ตรวจชนิดไฟล์แบบง่าย
            $allowed = ['image/jpeg', 'image/png'];

            if (in_array($file['type'], $allowed, true) && $file['error'] === UPLOAD_ERR_OK) {
                // สร้างชื่อไฟล์ใหม่
                $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newImageName = 'product_' . time() . '.' . $ext;
                // ✅ แก้ไข: ใช้ __DIR__ แทน _DIR_
                $uploadDir    = realpath(__DIR__ . '/../product_images');
                $destPath     = $uploadDir . DIRECTORY_SEPARATOR . $newImageName;

                // ย้ายไฟล์อัปโหลด
                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    // หากย้ายไม่ได้ ให้แจ้ง error และใช้รูปเดิม
                    $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพใหม่";
                    $newImageName = $oldImage;
                }
            } else {
                $_SESSION['error_message'] = "ประเภทไฟล์รูปภาพไม่ถูกต้อง หรือเกิดข้อผิดพลาดในการอัปโหลด";
                $newImageName = $oldImage;
            }
        }

        // อัปเดต DB
        $sql  = "UPDATE products
                SET product_name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?
                WHERE product_id = ?";
        $args = [$name, $description, $price, $stock, $category_id, $newImageName, $product_id];

        // ใช้ $pdo แทน $conn
        $stmt = $pdo->prepare($sql);
        $stmt->execute($args);

        // ลบไฟล์เก่าในดิสก์
        if (!empty($oldImage) && $oldImage !== $newImageName) {
            // ✅ แก้ไข: ใช้ __DIR__ แทน _DIR_
            $baseDir  = realpath(__DIR__ . '/../product_images');
            $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $oldImage);

            if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $_SESSION['success_message'] = "บันทึกการแก้ไขสินค้า **" . htmlspecialchars($name) . "** เรียบร้อยแล้ว";
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['error_message'] = "กรุณากรอกข้อมูลสินค้าให้ครบถ้วนและราคาต้องมากกว่า 0";
    }
}
// หากมีการส่งฟอร์มแล้วเกิด error/success จะถูก redirect ไปแล้ว
// ถ้าไม่มีการส่งฟอร์ม หรือมีการส่งแต่มี error message จะถูกแสดงผลใน HTML ด้านล่าง
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า: <?= htmlspecialchars($product['product_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* 🎨 Theme: Admin Professional (Blue/Purple Accent) */
        body {
            background-color: #f0f2f5; /* Light grey/blue background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #34495e;
        }

        /* Header and Title Style */
        .page-header {
            color: #2c3e50;
            font-weight: 700;
            padding-bottom: 10px;
            border-bottom: 3px solid #6c63ff; /* Accent border */
            margin-bottom: 30px;
        }

        .icon-accent {
            color: #6c63ff; /* Vivid blue-purple accent color */
        }

        /* Form Card Style */
        .edit-form-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border: 1px solid #e1e4e8;
        }
        
        /* Input/Select/Textarea Focus Style */
        .form-control:focus, .form-select:focus {
            border-color: #6c63ff;
            box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.2);
        }
        
        /* Button Styling */
        .btn-primary {
            background-color: #6c63ff;
            border-color: #6c63ff;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #5752d5;
            border-color: #5752d5;
        }
        .btn-secondary {
            background-color: #95a5a6;
            border-color: #95a5a6;
            border-radius: 8px;
        }

        .image-preview-box {
            border: 1px dashed #ced4da;
            padding: 10px;
            border-radius: 6px;
            display: inline-block;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body class="container mt-5">
    <h2 class="page-header">
        <i class="bi bi-pencil-square me-2 icon-accent"></i> แก้ไขสินค้า: **<?= htmlspecialchars($product['product_name']) ?>**
    </h2>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i>
            <div><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <a href="products.php" class="btn btn-secondary mb-4">
        <i class="bi bi-arrow-left-circle me-1"></i> กลับไปยังรายการสินค้า
    </a>

    <form method="post" enctype="multipart/form-data" class="row g-4 edit-form-card">

        <div class="col-md-6">
            <label class="form-label fw-bold">ชื่อสินค้า</label>
            <input type="text" name="product_name" class="form-control"
                value="<?= htmlspecialchars($product['product_name']) ?>" required>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold">ราคา (บาท)</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>"
                required>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold">จำนวนในคลัง</label>
            <input type="number" name="stock" class="form-control" value="<?= $product['stock']?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">หมวดหมู่</label>
            <select name="category_id" class="form-select" required>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"
                    <?= (int)$product['category_id'] === (int)$cat['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">รายละเอียดสินค้า</label>
            <textarea name="description" class="form-control"
                rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold d-block">รูปภาพปัจจุบัน</label>
            <?php if (!empty($product['image'])): ?>
            <div class="image-preview-box">
                <img src="../product_images/<?= htmlspecialchars($product['image']) ?>" alt="รูปปัจจุบัน" width="120" height="120"
                    class="rounded">
            </div>
            <?php else: ?>
            <span class="text-danger d-block fst-italic">ไม่มีรูปภาพสินค้า</span>
            <?php endif; ?>

            <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">
            
            <?php if (!empty($product['image'])): ?>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                <label class="form-check-label text-danger" for="remove_image">
                    <i class="bi bi-trash me-1"></i> ติ๊กเพื่อ **ลบรูปเดิม**
                </label>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">อัปโหลดรูปใหม่ (เลือกได้ 1 ไฟล์)</label>
            <input type="file" name="product_image" class="form-control">
            <div class="form-text mt-2">
                หากอัปโหลดรูปใหม่ รูปเดิมจะถูกแทนที่โดยอัตโนมัติ
            </div>
        </div>

        <div class="col-12 mt-5 text-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save me-2"></i> บันทึกการแก้ไข
            </button>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>