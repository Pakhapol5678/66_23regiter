<?php
session_start();
require_once 'config.php';

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("location: admin/index.php");
            // ยังไม่ได้กำหนดการเปลี่ยนเส้นทางสำหรับผู้ดูแลระบบ
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            /* การไล่ระดับสีจากน้ำเงินเข้มไปหาน้ำเงินอ่อน */
            background: linear-gradient(to right, #007bff, #4db8ff);
            min-height: 100vh;
        }

        .login-container {
            min-height: 100vh;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
            background-color: #ffffff;
            /* การ์ดสีขาว */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-label,
        .btn-link {
            color: #007bff;
            /* ข้อความและลิงก์สีน้ำเงินเข้ม */
        }

        .btn-primary {
            background-color: #4db8ff;
            /* ปุ่มสีฟ้าอ่อน */
            border-color: #4db8ff;
            font-weight: bold;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #007bff;
            /* เมื่อเมาส์ชี้ ปุ่มจะเปลี่ยนเป็นสีน้ำเงินเข้ม */
            border-color: #007bff;
        }
    </style>
</head>

<body>
    <div class="d-flex justify-content-center align-items-center login-container">
        <div class="card p-4 login-card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4" style="color: #007bff;">
                    <i class="fas fa-lock me-2"></i> เข้าสู่ระบบ
                </h3>

                <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                    <div class="col-12 d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
                    </div>
                    <div class="col-12 text-center mt-2">
                        <a href="register.php" class="btn btn-link">สมัครสมาชิก</a>
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