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
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuT\usPj697FH4R/5mcr" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-5">
        <h2>สมัครสมาชิก</h2>
        <?php if (!empty($error)): // ถ ้ำมีข ้อผิดพลำด ให้แสดงข ้อควำม ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($error as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                        <!-- ใช ้ htmlspecialchars เพื่อป้องกัน XSS -->
                        <!-- < ?=คือ short echo tag ?> -->
                        <!-- ถ ้ำเขียนเต็ม จะได ้แบบด ้ำนล่ำง -->
                        <?php // echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="row">
                <div class="col-md-6">
                    <label for="username" class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="ชื่อผู้ใช้"
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>

                </div>
                <div class=" col-md-6">
                    <label for="fullname" class="form-label">ชื่อ-สกุล</label>
                    <input type="text" id="fullname" name="fullname" class="form-control" placeholder="ชื่อ-สกุล"
                        value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Email"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="รหัสผ่าน"
                        required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                        placeholder="ยืนยันรหัสผ่าน" required>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
                <a href="login.php" class="btn btn-link">เข้าสู่ระบบ</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>