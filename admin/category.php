<?php
require '../config.php'; // TODO: เชอื่ มตอ่ ฐำนขอ้ มลู ดว้ย PDO
require 'auth_admin.php';// TODO: กำรด์ สทิ ธิ์(Admin Guard)
// แนวทำง: ถ ้ำ !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' -> redirect ไป ../login.php แล้ว exit;
// เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$category_name]);
        header("Location: category.php");
        exit;
    }
}
// ลบหมวดหมู่ (แบบไมม่ กี ำรตรวจสอบวำ่ ยังมสี นิ คำ้ในหมวดหมนู่ หี้ รอื ไม)่
// if (isset($_GET['delete'])) {
//     $category_id = $_GET['delete'];
//     $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
//     $stmt->execute([$category_id]);
//     header("Location: category.php");
//     exit;
// }
// ลบหมวดหมู่
// ตรวจสอบวำ่ หมวดหมนู่ ี้ยังถกู ใชอ้ยหู่ รอื ไม่
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    // ตรวจสอบวำ่ หมวดหมนู่ ยี้ ังถูกใชอ้ยหู่ รอื ไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $productCount = $stmt->fetchColumn();
    if ($productCount > 0) {
        // ถำ้มสี นิ คำ้อยใู่ นหมวดหมนู่ ี้
        $_SESSION['error'] = "ไม่สำมำรถลบหมวดหมนู่ ไี้ ด ้เนอื่ งจำกยังมสี นิ คำ้ทใี่ ชง้ำนหมวดหมนู่";
    } else {
        // ถำ้ไมม่ สี นิ คำ้ ใหล้ บได ้
        $stmt = $pdo->prepare("DELETE FROM ตำรำงที่ต ้องกำรลบ WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success'] = "ลบหมวดหมู่เรียบร ้อยแล้ว";
    }
    header("Location: category.php");
    exit;
}

// แก ้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['new_name']);
    if ($category_name) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id =
?");
        $stmt->execute([$category_name, $category_id]);
        header("Location: category.php");
        exit;
    }
}
// ดึงหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);
// โคด้ นเี้ขยีนตอ่ กันยำวบรรทัดเดยี วไดเ้พรำะ ผลลัพธจ์ ำกเมธอดหนงึ่ สำมำรถสง่ ตอ่ (chaining) ให้เมธอดถัดไปทันที โดยไม่ต ้องแยกตัวแปรเก็บไว้ก่อน
// $pdo->query("...")->fetchAll(...);
// หำกเขียนแยกเป็นหลำยบรรทัดจะเป็นแบบนี้:
// $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_id ASC");
// $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ควรเขยีนแยกบรรทัดเมอื่ จะ ใช ้$stmt ซ ้ำหลำยครัง้ (เชน่ fetch ทีละ row, ตรวจจ ำนวนแถว)
// หรือเขียนแบบ prepare , execute
// $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY category_id ASC");
// $stmt->execute();
// $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดกำรหมวดหมู่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>จัดกำรหมวดหมสินค้า</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mb-3">←กลับหน้ำผู้ดูแล</a>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่" ่ required>
        </div>
        <div class="col-md-2">
            <button type="submit" name="add_category" class="btn btn-primary">เพิ่มหมวดหมู่</button>
        </div>
    </form>
    <h5>รายการหมวดหมู่</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ชื่อหมวดหมู่</th>
                <th>แก้ไขชื่อ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                    <td>
                        <form method="post" class="d-flex">
                            <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                            <input type="text" name="new_name" class="form-control me-2" placeholder="ชื่อใหม" ่ required>
                            <button type="submit" name="update_category" class="btn btn-sm btn-warning">แก้ไข
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="category.php?delete=<?= $cat['category_id'] ?>" class=" btn btn-sm btn-danger"
                            onclick="return confirm('คุณต้องกำรลบหมวดหมู่นี้หรือไม่?')">ลบ</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>