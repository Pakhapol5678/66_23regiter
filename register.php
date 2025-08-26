<?php
require_once 'config.php';
$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    // ตรวจสอบข้อมูลมาครบหรือไม่ (empty)
    if (empty($username) || empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // ตรวจสอบว่ำอีเมลถูกต ้องหรือไม่ (filter_var)
        $error[] = "กรุณากรอกอีเมลให้ถูกต้อง";
    } elseif ($password !== $confirm_password) {
        // ตรวจสอบว่ำรหัสผ่ำนและกำรยืนยันตรงกันหรือไม
        $error[] = "รหัสผ่ำนและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        // ตรวจสอบว่ามีชื่อผู้ใช้หรืออีเมลถูกใช้ไปเเล้วหรือไม่
        $sql = "SELECT * FROM users  WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปเเล้ว";
        }
    }

    if (empty($error)) {
        //นำข้อมูลลงฐานข้อมูล
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users(username,full_name,email,password,role) VALUES (?, ?, ?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);

        // ถ้าบันทึกสำเร็จ ให้เปลี่ยนเส้นทางไปหน้า login
        header("Location: login.php?register=success");
        exit();//หยุดการทำงานหลังจากเปลี่ยนเส้นทาง
    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            /* Green to Yellow Gradient Background */
            background: linear-gradient(to right, #4CAF50, #FFEB3B); 
            min-height: 100vh;
        }
        .container-fluid {
            min-height: 100vh;
        }
        .register-card {
            background-color: #fff;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #4CAF50;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
        .form-label {
            color: #4CAF50;
        }
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #388E3C;
            border-color: #388E3C;
        }
        .btn-link {
            color: #FFC107;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .btn-link:hover {
            color: #FF9800;
        }
        .alert-danger {
            background-color: #FFCDD2;
            color: #D32F2F;
            border-color: #D32F2F;
        }
        .alert-danger ul {
            margin-bottom: 0;
            list-style: none;
            padding-left: 0;
        }
    </style>
</head>

<body>
    <div class="container-fluid d-flex justify-content-center align-items-center">
        <div class="col-md-8 col-lg-6 register-card">
            <h2 class="text-center">สมัครสมาชิก</h2>
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
                        <label for="password" class="form-label">รหัสผ่าน</label>
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
                <div class="mt-3 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">สมัครสมาชิก</button>
                    <a href="login.php" class="btn btn-link">เข้าสู่ระบบ</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>