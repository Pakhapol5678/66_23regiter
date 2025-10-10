<?php
// ตรวจสอบ session_start() อาจถูกเรียกในไฟล์ auth_admin.php หรือ config.php แล้ว
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config.php'; // ✅ เชื่อมต่อฐานข้อมูลด้วย PDO
require 'auth_admin.php';// ✅ การ์ดสิทธิ์(Admin Guard)

/**
 * 📚 ฟังก์ชันช่วยเพิ่มหมวดหมู่ Light Novel และ Manga (ถ้ายังไม่มี) 📚
 * เพื่อตอบโจทย์ของผู้ใช้
 */
function ensure_default_categories($pdo) {
    $defaultCategories = ['ไลท์โนเวล', 'มังงะ'];
    foreach ($defaultCategories as $name) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->execute([$name]);
        }
    }
}
// เรียกใช้ฟังก์ชันเมื่อโหลดหน้า
ensure_default_categories($pdo);


// ✅ เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name) {
        // ใช้ $pdo แทน $conn
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$category_name]);
        $_SESSION['success'] = "เพิ่มหมวดหมู่ **" . htmlspecialchars($category_name) . "** เรียบร้อยแล้ว";
        header("Location: category.php");
        exit;
    }
}

// ✅ ลบหมวดหมู่
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    try {
        // ตรวจสอบว่าหมวดหมู่นี้ยังถูกใช้โดยสินค้าหรือไม่
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $productCount = $stmt->fetchColumn();

        if ($productCount > 0) {
            // ถ้ามีสินค้าอยู่ในหมวดหมู่นี้
            $_SESSION['error'] = "ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากยังมีสินค้าที่ใช้งานหมวดหมู่นี้อยู่ ($productCount รายการ)";
        } else {
            // ถ้าไม่มีสินค้า ให้ลบได้
            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?"); // ✅ แก้ไขเป็นตาราง categories
            $stmt->execute([$category_id]);
            $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบ: " . $e->getMessage();
    }
    header("Location: category.php");
    exit;
}

// ✅ แก้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = (int)$_POST['category_id'];
    $category_name = trim($_POST['new_name']);
    if ($category_name) {
        // ใช้ $pdo แทน $conn
        $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$category_name, $category_id]);
        $_SESSION['success'] = "แก้ไขหมวดหมู่ ID:$category_id เป็น **" . htmlspecialchars($category_name) . "** เรียบร้อยแล้ว";
        header("Location: category.php");
        exit;
    }
}

// ✅ ดึงหมวดหมู่ทั้งหมด
// ใช้ $pdo แทน $conn
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการหมวดหมู่สินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* 🎨 Theme: Clean & Professional (Blue/Grey Accent) */
        body {
            background-color: #f0f2f5; /* Soft light background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #34495e;
        }

        .page-header {
            color: #2c3e50;
            font-weight: 700;
            padding-bottom: 10px;
            border-bottom: 3px solid #3498db; /* Blue accent line */
            margin-bottom: 25px;
        }

        /* Add Form Style */
        .add-form-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            padding: 20px;
            border-left: 5px solid #3498db;
        }

        /* Table Style */
        .category-table-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        .table-custom thead th {
            background-color: #3498db; /* Primary Blue header */
            color: #ffffff;
            font-weight: 600;
        }
        .table-custom tbody tr:hover {
            background-color: #f5f6f7;
        }

        /* Button Focus/Style */
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
            font-weight: 600;
            border-radius: 6px;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        .btn-warning {
            background-color: #f39c12;
            border-color: #f39c12;
            color: #fff;
        }
        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
    </style>
</head>

<body class="container mt-5">
    <h2 class="page-header">
        <i class="bi bi-tags-fill me-2" style="color:#3498db;"></i> จัดการหมวดหมู่สินค้า
    </h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i>
            <div><?= htmlspecialchars($_SESSION['error']) ?></div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div><?= htmlspecialchars($_SESSION['success']) ?></div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary mb-4" style="border-radius:6px;">
        <i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าผู้ดูแล
    </a>

    <form method="post" class="row g-3 mb-5 add-form-card">
        <h5 style="font-weight: 600; color:#2c3e50;"><i class="bi bi-plus-circle me-1"></i> เพิ่มหมวดหมู่ใหม่</h5>
        <div class="col-md-6">
            <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่ (เช่น: มังงะ, นิยาย)" required>
        </div>
        <div class="col-md-3">
            <button type="submit" name="add_category" class="btn btn-primary w-100">
                <i class="bi bi-check-lg me-1"></i> ยืนยันเพิ่ม
            </button>
        </div>
    </form>

    <h5 class="mb-3" style="font-weight: 600; color:#2c3e50;">รายการหมวดหมู่ที่มีอยู่</h5>
    <div class="table-responsive category-table-container">
        <table class="table table-bordered table-hover table-custom m-0 align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 25%;">ชื่อหมวดหมู่</th>
                    <th style="width: 45%;">แก้ไขชื่อ</th>
                    <th style="width: 25%;" class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted p-3">
                            <i class="bi bi-info-circle me-1"></i> ยังไม่มีหมวดหมู่ในระบบ
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="text-center text-muted"><?= htmlspecialchars($cat['category_id']) ?></td>
                            <td><?= htmlspecialchars($cat['category_name']) ?></td>
                            <td>
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                    <input type="text" name="new_name" class="form-control me-2" placeholder="ชื่อใหม่สำหรับ <?= htmlspecialchars($cat['category_name']) ?>" required>
                                    <button type="submit" name="update_category" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i> แก้ไข
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <a href="category.php?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('ยืนยันการลบหมวดหมู่: <?= htmlspecialchars($cat['category_name']) ?> ?')">
                                    <i class="bi bi-trash"></i> ลบ
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>