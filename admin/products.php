<?php
session_start();
require '../config.php'; // TODO: เชอื่ มตอ่ ฐำนขอ้ มลู ดว้ย PDO
require 'auth_admin.php '; // TODO: เชอื่ มตอ่ ฐำนขอ้ มลู ดว้ย PDO
// TODO: กำรด์ สทิ ธิ์(Admin Guard)
// แนวทำง: ถ ้ำ !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' -> redirect ไป ../login.php แล้ว exit;
// เพมิ่ สนิคำ้ใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']); // floatval() ใชแปลงเป็น ้ float
    $stock = intval($_POST['stock']); // intval() ใชแ้ปลงเป็น integer
    $category_id = intval($_POST['category_id']);
    // ค่ำที่ได ้จำกฟอร์มเป็น string เสมอ
    if ($name && $price > 0) { // ตรวจสอบชอื่ และรำคำสนิ คำ้
        $stmt = $pdo->prepare("INSERT INTO ตำรำง (ฟิลด์ที่ต ้องกำรเพิ่ม) VALUES (product_name,description,price,stock,category_id)");
        $stmt->execute([$name, $description, $price, $stock, $category_id]);
        header("Location: products.php");
        exit;
    }
    // ถ ้ำเขียนให ้อ่ำนง่ำยขึ้น สำมำรถเขียนแบบด ้ำนล่ำง
// if (!empty($name) && $price > 0) {
// // ผำ่ นเงอื่ นไข: มชี อื่ สนิคำ้ และ รำคำมำกกวำ่ 0
// }
}
// ลบสนิ คำ้
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM ตำรำง WHERE product_id = ?");
    $stmt->execute([$ตัวแปรค่ำที่ตองกำรbindparam]);
    header("Location: products.php");
    exit;
}
// ดงึรำยกำรสนิคำ้
$stmt = $pdo->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON
p.category_id = c.category_id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ดึงหมวดหมู่
$categories = $pdo->query("SELECT * FROM ตำรำง")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดกำรสนิคำ้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>จัดกำรสนิ คำ้</h2>
    <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้ำผู้ดูแล</a>
    <!-- ฟอรม์ เพมิ่ สนิคำ้ใหม่ -->
    <form method="post" class="row g-3 mb-4">
        <h5>เพมิ่ สนิคำ้ใหม</h ่ 5>
            <div class="col-md-4">
                <input type="text" name="product_name" class="form-control" placeholder="ชอื่ สนิคำ้" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" name="price" class="form-control" placeholder="รำคำ" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="stock" class="form-control" placeholder="จ ำนวน" required>
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select" required>
                    <option value="">เลือกหมวดหมู่</option>
                    <?php foreach ($ตัวแปรที่เก็บหมวดหมู่ as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>">
                            <?= htmlspecialchars($cat['ชอื่ หมวดหม่'])
                                ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <textarea name="description" class="form-control" placeholder="รำยละเอยีดสนิคำ้" rows="2"></textarea>
            </div>
            <div class="col-12">
                <button type="submit" name="add_product" class="btn btn-primary">เพมิ่ สนิคำ้</button>
            </div>
    </form>
    <!-- แสดงรำยกำรสนิคำ้ , แก ้ไข , ลบ -->
    <h5>รำยกำรสนิ คำ้</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ชอื่ สนิคำ้</th>
                <th>หมวดหมู่</th>
                <th>รำคำ</th>
                <th>คงเหลือ</th>
                <th>จัดกำร</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ตัวแปรเก็บสนิคำ้ as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['ค่ำที่ต ้องกำรแสดง']) ?></td>
                    <td><?= htmlspecialchars($p['ค่ำที่ต ้องกำรแสดง']) ?></td>
                    <td><?= number_format($p['ค่ำที่ต ้องกำรแสดง'], 2) ?> บำท</td>
                    <td><?= $p['ค่ำที่ต ้องกำรแสดง'] ?></td>
                    <td>
                        <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('ยนื ยันกำรลบสนิคำ้นี้?')">ลบ</a>
                        <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btnwarning">แก ้ไข</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>