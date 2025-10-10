<?php
session_start();
// ตรวจสอบว่าไฟล์ config.php มีการสร้าง $conn เป็น PDO object 
// ถ้าไฟล์ config.php ใช้ MySQLi ให้เปลี่ยนเป็น PDO เพื่อให้โค้ดส่วนนี้ทำงานได้
require 'config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";
$new_hashed = null;

// ดึงข้อมูลสมาชิก (ใช้ PDO)
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
// ✅ แก้ไข: ใช้ fetch(PDO::FETCH_ASSOC) ได้ตามปกติเมื่อ $stmt เป็น PDOStatement
$user = $stmt->fetch(PDO::FETCH_ASSOC); 

// ตรวจสอบว่าดึงข้อมูลผู้ใช้ได้จริงหรือไม่
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// กำหนดค่าเริ่มต้นสำหรับฟอร์ม (ใช้ค่าจาก DB)
$full_name = $user['full_name'];
$email = $user['email'];

// เมื่อมีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ⚠️ รับค่าจาก POST และกำหนดกลับไปที่ตัวแปร เพื่อให้แสดงในฟอร์มได้หากมี error
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ตรวจสอบชื่อและอีเมลไม่ว่าง
    if (empty($full_name) || empty($email)) {
        $errors[] = "กรุณากรอกชื่อ-นามสกุลและอีเมล";
    }

    // ตรวจสอบอีเมลซ้ำ (ถ้ามีการเปลี่ยนอีเมล)
    if ($email !== $user['email']) {
        $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt_check->execute([$email, $user_id]);
        
        // ✅ ใช้ fetch() ของ PDOStatement ตรวจสอบผลลัพธ์
        if ($stmt_check->fetch()) { 
            $errors[] = "อีเมลนี้ถูกใช้งานแล้วโดยบัญชีอื่น";
        }
    }

    // ตรวจสอบการเปลี่ยนรหัสผ่าน (ถ้ามีการกรอกช่องรหัสผ่านใดๆ)
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "รหัสผ่านเดิมไม่ถูกต้อง";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "รหัสผ่านใหม่และการยืนยันไม่ตรงกัน";
        } else {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    // อัปเดตข้อมูลหากไม่มี error
    if (empty($errors)) {
        if (!empty($new_hashed)) {
            $stmt_update = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?");
            $stmt_update->execute([$full_name, $email, $new_hashed, $user_id]);
        } else {
            $stmt_update = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
            $stmt_update->execute([$full_name, $email, $user_id]);
        }
        
        $success = "บันทึกข้อมูลเรียบร้อยแล้ว";
        
        // อัปเดตข้อมูลผู้ใช้ใน Session
        $user['full_name'] = $full_name;
        $user['email'] = $email;
        $_SESSION['full_name'] = $full_name; 
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์สมาชิก - My Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
        }

        /* Header and Title Style */
        .page-header-container {
            border-bottom: 3px solid var(--accent-pink);
            padding-bottom: 10px;
            margin-bottom: 30px;
            background-color: #fff; 
            padding: 20px 0;
        }

        .page-title {
            color: var(--primary-purple);
            font-weight: 700;
        }

        .icon-accent {
            color: var(--accent-pink);
        }
        
        /* Form Card */
        .profile-card {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(74, 35, 90, 0.15); 
            padding: 35px;
            border-top: 8px solid var(--primary-purple); 
        }

        /* Input/Label Style */
        .form-label {
            font-weight: 600;
            color: var(--primary-purple); 
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .form-control:focus {
            border-color: var(--accent-pink);
            box-shadow: 0 0 0 0.25rem rgba(232, 62, 140, 0.25);
        }

        /* Password Section Divider */
        .password-section-divider {
            margin-top: 30px;
            margin-bottom: 25px;
            border-top: 2px dashed var(--accent-pink); 
            padding-top: 10px;
        }
        .password-section-divider h5 {
            color: var(--primary-purple);
            font-weight: 600;
            margin-bottom: 0;
        }

        /* Button Styling */
        .btn-submit {
            background-color: var(--accent-pink);
            border-color: var(--accent-pink);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(232, 62, 140, 0.3);
        }

        .btn-submit:hover {
            background-color: #d12e7b;
            border-color: #d12e7b;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(232, 62, 140, 0.4);
        }
        
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
        
        /* Alert Styling */
        .alert-success {
            color: #2b774b;
            background-color: #d4edda;
            border-color: #c3e6cb;
            border-radius: 8px;
        }
        .alert-danger {
            color: #7d2a33;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            border-radius: 8px;
        }
    </style>
</head>

<body class="container mt-5">

    <div class="d-flex justify-content-between align-items-center page-header-container sticky-top" style="z-index: 1020;">
        <h2 class="fw-bold page-title">
            <i class="bi bi-person-circle me-2 icon-accent"></i> โปรไฟล์ของคุณ
        </h2>
        <a href="index.php" class="btn btn-secondary-theme py-2 px-3">
            <i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าหลัก
        </a>
    </div>

    <div style="max-width: 800px; margin: 0 auto;">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>
                <span class="fw-bold">พบข้อผิดพลาด:</span>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success mb-4 d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <span class="fw-bold"><?= $success ?></span>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <form method="post" class="row g-4">
                
                <h5 class="fw-bold mb-3" style="color:var(--primary-purple);"><i class="bi bi-person-vcard me-2"></i> ข้อมูลส่วนตัว</h5>
                
                <div class="col-md-6">
                    <label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label>
                    <input type="text" class="form-control" id="username" disabled value="<?= htmlspecialchars($user['username']) ?>">
                    <div class="form-text text-muted">ชื่อผู้ใช้ไม่สามารถแก้ไขได้</div>
                </div>
                
                <div class="col-md-6">
                    <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="full_name" id="full_name" class="form-control" required value="<?= htmlspecialchars($full_name) ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="email" class="form-label">อีเมล</label>
                    <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                </div>
                
                <div class="col-md-6">
                    </div>

                <div class="col-12">
                    <div class="password-section-divider">
                        <h5><i class="bi bi-key me-2"></i> เปลี่ยนรหัสผ่าน (ไม่จำเป็น)</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="current_password" class="form-label">รหัสผ่านเดิม</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" autocomplete="off" value="">
                </div>
                <div class="col-md-6">
                    </div>
                <div class="col-md-6">
                    <label for="new_password" class="form-label">รหัสผ่านใหม่ (≥ 6 ตัวอักษร)</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" autocomplete="new-password" value="">
                </div>
                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" autocomplete="new-password" value="">
                </div>
                
                <div class="col-12 mt-4 text-center">
                    <button type="submit" class="btn btn-submit btn-lg px-5">
                        <i class="bi bi-save me-2"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <footer class="text-center mt-5 mb-3" style="color:#95a5a6; font-size:14px;">
        © <?= date('Y') ?> ระบบ E-Commerce | User Profile
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>