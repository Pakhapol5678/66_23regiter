<?php
// session_start(); 
require_once 'config.php'; // สมมติว่าไฟล์นี้เชื่อมต่อและกำหนด $pdo (อ็อบเจกต์ PDO)

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์มและทำความสะอาด
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    // ตรวจสอบข้อมูล
    if (empty($username) || empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "กรุณากรอกอีเมลให้ถูกต้อง";
    } elseif (strlen($password) < 6) {
        $error[] = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    } elseif ($password !== $confirm_password) {
        $error[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        // ตรวจสอบว่ามีชื่อผู้ใช้หรืออีเมลถูกใช้ไปแล้วหรือไม่ (ใช้ PDO)
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
            // 💡 แก้ไขการใช้ตัวแปร: ใช้ $pdo ในการเตรียมคำสั่ง
            $stmt = $pdo->prepare($sql); 
            $stmt->execute([$username, $email]); // ส่งค่าแบบอาร์เรย์เข้าไปใน execute
            $count = $stmt->fetchColumn(); // นับจำนวนแถวที่ซ้ำ

            if ($count > 0) {
                $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว";
            }
        } catch (PDOException $e) {
            // ควรมีการจัดการข้อผิดพลาดของฐานข้อมูลอย่างเหมาะสม
            error_log("Database error: " . $e->getMessage()); 
            $error[] = "เกิดข้อผิดพลาดในการตรวจสอบข้อมูล";
        }
    }

    if (empty($error)) {
        // นำข้อมูลลงฐานข้อมูล (ใช้ PDO)
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users(username, full_name, email, password, role) VALUES (?, ?, ?, ?, 'member')";
            // 💡 แก้ไขการใช้ตัวแปร: ใช้ $pdo ในการเตรียมคำสั่ง
            $stmt = $pdo->prepare($sql);
            
            // 💡 แก้ไขการผูกค่า: ใช้ execute พร้อมอาร์เรย์ของค่า
            // ตรวจสอบให้แน่ใจว่าคอลัมน์ใน DB ชื่อ full_name 
            $stmt->execute([$username, $fullname, $email, $hashedPassword]); 

            // ถ้าบันทึกสำเร็จ ให้เปลี่ยนเส้นทางไปหน้า login
            header("Location: login.php?register=success");
            exit(); // หยุดการทำงานหลังจากเปลี่ยนเส้นทาง
        } catch (PDOException $e) {
            // ควรมีการจัดการข้อผิดพลาดของฐานข้อมูลอย่างเหมาะสม
            error_log("Database error: " . $e->getMessage()); 
            $error[] = "ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง";
        }
    }

}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 🎨 Theme: Dark Purple & Black (Modern Bookstore) */
        :root {
            --color-primary: #3a004f; /* ม่วงเข้มมาก / ดำม่วง */
            --color-secondary: #e83e8c; /* ชมพู Accent */
            --color-text-light: #f7f7f7;
            --color-bg-dark: #1c1c1c; /* พื้นหลังดำ */
            --color-input-bg: #383838;
        }

        body {
            /* การไล่ระดับสีพื้นหลัง: ดำเข้ม -> ม่วงเข้ม */
            background: linear-gradient(135deg, var(--color-bg-dark) 0%, var(--color-primary) 100%);
            min-height: 100vh;
            color: var(--color-text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-fluid {
            min-height: 100vh;
        }

        .register-card {
            max-width: 700px;
            width: 100%;
            background-color: #2a2a2a; /* การ์ดสีเทาเข้ม */
            padding: 3rem;
            border: 1px solid #555;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 10px var(--color-secondary); /* เงาเข้ม + ขอบชมพูอ่อน */
        }

        h2 {
            color: var(--color-secondary); /* หัวข้อสีชมพู */
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }

        .form-label {
            color: var(--color-text-light); /* ข้อความ Label สีขาว */
            font-weight: 600;
        }

        /* สไตล์ Input Field */
        .form-control {
            background-color: var(--color-input-bg); /* พื้นหลัง Input มืด */
            border: 1px solid #555;
            color: var(--color-text-light);
        }
        .form-control:focus {
            background-color: #444;
            border-color: var(--color-secondary);
            box-shadow: 0 0 0 0.25rem rgba(232, 62, 140, 0.4); /* Shadow ชมพู */
            color: var(--color-text-light);
        }

        /* ปุ่มสมัครสมาชิก (Primary) */
        .btn-primary {
            background: linear-gradient(90deg, #6a1b9a 0%, #b34ddb 100%); /* ม่วงสดใสไล่ระดับ */
            border: none;
            font-weight: bold;
            color: #fff;
            box-shadow: 0 4px 10px rgba(106, 27, 154, 0.4);
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #b34ddb 0%, #6a1b9a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(106, 27, 154, 0.6);
        }

        /* ปุ่มเข้าสู่ระบบ (Link) */
        .btn-link {
            color: var(--color-secondary); /* ลิงก์สีชมพู */
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .btn-link:hover {
            color: #ffaaec; /* สีชมพูอ่อนลงเมื่อชี้ */
        }

        /* Alert Danger (ปรับให้เข้ากับธีม) */
        .alert-danger {
            background-color: #58151c;
            color: #f5c6cb;
            border-color: #721c24;
        }
        .alert-danger ul {
            margin-bottom: 0;
            list-style: none;
            padding-left: 0;
        }
        .alert-danger .btn-close {
            filter: invert(1); /* ทำให้ปุ่มปิดเป็นสีขาว/สว่าง */
        }
    </style>
</head>

<body>
    <div class="container-fluid d-flex justify-content-center align-items-center">
        <div class="register-card">
            <h2 class="text-center">
                <i class="fas fa-user-plus me-2"></i> สมัครสมาชิกใหม่
            </h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul>
                        <?php foreach ($error as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="ชื่อผู้ใช้"
                            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fullname" class="form-label">ชื่อ-สกุล</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" placeholder="ชื่อ-สกุล"
                            value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Email"
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">รหัสผ่าน (อย่างน้อย 6 ตัว)</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="รหัสผ่าน"
                            required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                            placeholder="ยืนยันรหัสผ่าน" required>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check-circle me-2"></i> สมัครสมาชิก
                    </button>
                    <div class="mt-3">
                        <span style="color: #bbb;">เป็นสมาชิกอยู่แล้ว?</span>
                        <a href="login.php" class="btn-link">เข้าสู่ระบบ</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>