<?php
session_start();
require_once 'config.php';

$error = [];

// ตรวจสอบว่าใช้ MySQLi หรือ PDO ใน config.php
// โค้ดนี้เป็น MySQLi: $stmt->bind_param('ss', ...), $stmt->get_result()
// หาก config.php เป็น PDO ต้องแก้ไขโค้ดฐานข้อมูลให้เป็น PDO แทน!

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    // 🚩 หากใช้ PDO, บรรทัดนี้ควรเป็น: $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    
    // 🚩 หากใช้ PDO, บรรทัดนี้ควรเป็น: $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    
    // 🚩 หากใช้ PDO, บรรทัดนี้ควรเป็น: $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("location: admin/index.php");
        } else {
            header("location: index.php");
        }
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 🎨 Theme: Dark Purple & Black (Modern Bookstore) */
        :root {
            --color-primary: #3a004f; /* ม่วงเข้มมาก / ดำม่วง */
            --color-secondary: #e83e8c; /* ชมพู Accent */
            --color-text-light: #f7f7f7;
            --color-text-dark: #333333;
            --color-bg-dark: #1c1c1c; /* พื้นหลังดำ */
        }
        
        body {
            /* การไล่ระดับสีพื้นหลัง: ดำเข้ม -> ม่วงเข้ม */
            background: linear-gradient(135deg, var(--color-bg-dark) 0%, var(--color-primary) 100%);
            min-height: 100vh;
            color: var(--color-text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            min-height: 100vh;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
            background-color: #2a2a2a; /* การ์ดสีเทาเข้ม */
            border: 1px solid #555;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 10px var(--color-secondary); /* เงาเข้ม + ขอบชมพูอ่อน */
        }

        .card-title {
            color: var(--color-secondary); /* หัวข้อสีชมพู */
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }
        
        .form-label {
            color: var(--color-text-light); /* ข้อความ Label สีขาว */
            font-weight: 600;
        }
        
        /* สไตล์ Input Field */
        .form-control {
            background-color: #383838; /* พื้นหลัง Input มืด */
            border: 1px solid #555;
            color: var(--color-text-light);
        }
        .form-control:focus {
            background-color: #444;
            border-color: var(--color-secondary);
            box-shadow: 0 0 0 0.25rem rgba(232, 62, 140, 0.4); /* Shadow ชมพู */
            color: var(--color-text-light);
        }

        /* ปุ่มเข้าสู่ระบบ (Primary) */
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

        /* ปุ่มสมัครสมาชิก (Link) */
        .btn-link {
            color: var(--color-secondary); /* ลิงก์สีชมพู */
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        .btn-link:hover {
            color: #ffaaec; /* สีชมพูอ่อนลงเมื่อชี้ */
        }

        /* Alert Success (ปรับให้เข้ากับธีม) */
        .alert-success {
            background-color: #1e4d2b;
            color: #c3e6cb;
            border-color: #155724;
        }
        /* Alert Danger (ปรับให้เข้ากับธีม) */
        .alert-danger {
            background-color: #58151c;
            color: #f5c6cb;
            border-color: #721c24;
        }
    </style>
</head>

<body>
    <div class="d-flex justify-content-center align-items-center login-container">
        <div class="card p-4 login-card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">
                    <i class="fas fa-book-reader me-2"></i> ยินดีต้อนรับสู่โลกแห่งการอ่าน
                </h3>

                <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="post" class="row g-3">
                    <div class="col-12">
                        <label for="username_or_email" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
                        <input type="text" name="username_or_email" id="username_or_email" class="form-control"
                            required>
                    </div>
                    <div class="col-12">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="col-12 d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i> เข้าสู่ระบบ
                        </button>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <span style="color: #bbb;">ยังไม่มีบัญชี?</span> 
                        <a href="register.php" class="btn-link">สมัครสมาชิกที่นี่</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>