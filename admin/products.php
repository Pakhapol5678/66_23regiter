<?php
session_start();
require '../config.php'; // ✅ เชื่อมต่อฐานข้อมูลด้วย PDO
require 'auth_admin.php';

// ✅ Admin Guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ เพิ่มสินค้าใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name         = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price       = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $stock       = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

    $imageName = null;
    if (isset($_FILES['product_image']) && !empty($_FILES['product_image']['name'])) {
        $file = $_FILES['product_image'];
        $allowed = ['image/jpeg', 'image/png'];
        if (in_array($file['type'], $allowed)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageName = 'product_' . time() . '.' . $ext;
            $baseDir = realpath(__DIR__ . '/../product_images');
            if ($baseDir) {
                $path = $baseDir . DIRECTORY_SEPARATOR . $imageName;
                move_uploaded_file($file['tmp_name'], $path);
            }
        }
    }
    // ตรวจสอบข้อมูลที่จำเป็นครบถ้วน
    if (!empty($name) && $price > 0 && $stock >= 0 && $category_id > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);
            $_SESSION['success_message'] = "เพิ่มสินค้า **" . htmlspecialchars($name) . "** เรียบร้อยแล้ว";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการเพิ่มสินค้า: " . $e->getMessage();
        }
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['error_message'] = "กรุณากรอกข้อมูลสินค้าให้ครบถ้วนและถูกต้อง";
        header("Location: products.php");
        exit;
    }
}

// ✅ ลบสินค้า (ลบไฟล์รูปด้วย)
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    // 1) ดึงไฟล์รูปและชื่อจาก DB ก่อน
    $stmt = $pdo->prepare("SELECT image, product_name FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $imageName = $result['image'] ?? null;
    $productName = $result['product_name'] ?? 'สินค้า';

    // 2) ลบใน DB ด้วย Transaction
    try {
        $pdo->beginTransaction();
        $del = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);
        $pdo->commit();
        
        // 3) ลบไฟล์รูปหลัง DB ลบสำเร็จ
        if ($imageName) {
            $baseDir = realpath(__DIR__ . '/../product_images');
            $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $imageName);
            if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
                @unlink($filePath);
            }
        }
        $_SESSION['success_message'] = "ลบสินค้า **" . htmlspecialchars($productName) . "** เรียบร้อยแล้ว";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการลบสินค้า: " . $e->getMessage();
    }
    header("Location: products.php");
    exit;
}

// ✅ ดึงรายการสินค้า (join categories)
$stmt = $pdo->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ ดึงหมวดหมู่ทั้งหมด
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* 🎨 Theme: Blue-Cyan Accent */
        :root {
            --primary-blue: #920be0ff;
            --accent-cyan: #c258ffff;
            --dark-text: #2c3e50;
            --light-bg: #f4f7f9;
        }

        body {
            background-color: var(--light-bg); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
        }

        /* Header and Title Style */
        .page-header {
            color: var(--dark-text);
            font-weight: 700;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--primary-blue); /* Blue accent border */
            margin-bottom: 30px;
        }

        .icon-accent {
            color: var(--accent-cyan); /* Cyan Accent color */
        }

        /* Form Card Style */
        .add-form-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1); /* Soft blue shadow */
            padding: 25px;
            border-left: 5px solid var(--primary-blue); /* Blue stripe */
            margin-bottom: 40px;
        }
        
        /* Input/Select/Textarea Focus Style */
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-cyan);
            box-shadow: 0 0 0 0.25rem rgba(23, 162, 184, 0.2);
        }
        
        /* Button Styling */
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #6c0f91ff;
        }
        .btn-secondary {
            background-color: #bdc3c7; /* Light grey for secondary actions */
            border-color: #bdc3c7;
            color: var(--dark-text);
            border-radius: 8px;
        }
        .btn-warning {
            background-color: #f39c12; /* Kept standard warning color */
            border-color: #f39c12;
            color: #fff;
        }
        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }

        /* Table Style */
        .product-table-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
        .table-dark th {
            background-color: var(--primary-blue); /* Blue header */
            border-color: var(--primary-blue);
            color: #ffffff;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: #e8f4fa; /* Very light blue hover */
        }
        .table td {
            vertical-align: middle;
        }

        /* Stock Badge */
        .stock-badge {
            background-color: #1749d4ff; /* Cyan */
            color: #ffffff;
            font-weight: 500;
            padding: .4em .6em;
            border-radius: 5px;
        }

        /* Alert styling */
        .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body class="container mt-5">

    <div class="d-flex justify-content-between align-items-center page-header">
        <h2 class="fw-bold" style="color:var(--dark-text);">
            <i class="bi bi-box-seam me-2 icon-accent"></i> จัดการสินค้า
        </h2>
        <a href="index.php" class="btn btn-secondary">
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

    <form method="post" enctype="multipart/form-data" class="row g-3 add-form-card">
        <h5 class="mb-3" style="color:var(--dark-text); font-weight:600;"><i class="bi bi-plus-lg me-1"></i> เพิ่มสินค้าใหม่</h5>
        
        <div class="col-md-5">
            <input type="text" name="product_name" class="form-control" placeholder="ชื่อสินค้า" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="price" class="form-control" placeholder="ราคา (บาท)" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="stock" class="form-control" placeholder="จำนวนคงคลัง" required>
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select" required>
                <option value="">--- เลือกหมวดหมู่ ---</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>">
                    <?= htmlspecialchars($cat['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-12">
            <textarea name="description" class="form-control" placeholder="รายละเอียดสินค้า (ไม่จำเป็น)" rows="2"></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label text-muted">รูปภาพสินค้า (jpg, png)</label>
            <input type="file" name="product_image" class="form-control">
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" name="add_product" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้า
            </button>
        </div>
    </form>

    <h5 class="mb-3" style="color:var(--dark-text); font-weight:600;">รายการสินค้า</h5>
    <div class="table-responsive product-table-container">
        <table class="table table-bordered table-hover m-0 text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 30%;">ชื่อสินค้า</th>
                    <th style="width: 15%;">หมวดหมู่</th>
                    <th style="width: 15%;">ราคา</th>
                    <th style="width: 10%;">คงเหลือ</th>
                    <th style="width: 30%;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">
                            <i class="bi bi-info-circle me-1"></i> ไม่มีสินค้าในระบบขณะนี้
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="text-start ps-3"><?= htmlspecialchars($p['product_name']) ?></td>
                        <td><?= htmlspecialchars($p['category_name']) ?></td>
                        <td class="text-end pe-4 text-success fw-bold"><?= number_format($p['price'], 2) ?> บาท </td>
                        <td>
                            <span class="stock-badge">
                                <?= $p['stock'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_products.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-warning me-2" style="border-radius:6px;">
                                <i class="bi bi-pencil-square"></i> แก้ไข </a>
                            <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-danger"
                                style="border-radius:6px;" onclick="return confirm('ยืนยันการลบสินค้า: <?= htmlspecialchars($p['product_name']) ?>?')">
                                <i class="bi bi-trash"></i> ลบ </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <footer class="text-center mt-5 mb-3" style="color:#95a5a6; font-size:14px;">
        © ระบบผู้ดูแล
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>