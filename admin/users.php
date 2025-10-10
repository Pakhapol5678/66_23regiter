<?php
session_start();
require_once '../config.php'; // ✅ เชื่อมฐานข้อมูล (ใช้ $pdo)
require_once 'auth_admin.php'; // ✅ Admin Guard

// ✅ ลบสมาชิก: ส่วนนี้ถูกย้ายไปจัดการใน delUser_sweet.php
// จึงคอมเมนต์ส่วน PHP ที่จัดการการลบโดยตรงในหน้านี้ออก
/*
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    // ป้องกันลบตัวเอง
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
    }
    header("Location: users.php");
    exit;
}
*/

// ✅ ดึงข้อมูลสมาชิก (แก้ไข: ใช้ $pdo แทน $conn)
try {
    $stmt = $pdo->prepare("SELECT user_id, username, full_name, email, created_at FROM users WHERE role = 'member' ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // จัดการข้อผิดพลาดในการดึงข้อมูล
    $users = [];
    $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage();
}

// ฟังก์ชันสำหรับจัดรูปแบบวันที่
function formatThaiDate($dateString) {
    if (empty($dateString)) return '-';
    $timestamp = strtotime($dateString);
    return date('d/m/Y H:i', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสมาชิก - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* 🎨 Theme: Purple-Black-Pink Accent */
        :root {
            --primary-purple: #4a235a; /* Dark Purple (Black substitute) */
            --accent-pink: #e83e8c; /* Vivid Pink */
            --light-bg: #fcf4f8; /* Light Pink/Purple background */
            --dark-text: #2c3e50;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
        }

        /* Header and Title Style */
        .page-header-container {
            border-bottom: 3px solid var(--accent-pink); /* Pink Accent border */
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .page-title {
            color: var(--primary-purple);
            font-weight: 700;
        }
        .icon-accent {
            color: var(--accent-pink); /* Pink Accent color */
        }

        /* Button Styling */
        .btn-primary-theme {
            background-color: var(--accent-pink);
            border-color: var(--accent-pink);
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-primary-theme:hover {
            background-color: #d12e7b;
            border-color: #d12e7b;
        }
        .btn-secondary {
            background-color: #c9c9e8; /* Soft Lavender */
            border-color: #c9c9e8;
            color: var(--primary-purple);
            border-radius: 8px;
        }

        /* Table Style */
        .user-table-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(74, 35, 90, 0.1); /* Dark Purple shadow */
            background-color: #ffffff;
        }
        .table-header-dark {
            background-color: var(--primary-purple); /* Dark Purple header */
            border-color: var(--primary-purple);
            color: #ffffff;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: #fce4ec; /* Light pink hover */
        }
        .table td {
            vertical-align: middle;
        }
        
        /* Action Buttons */
        .btn-warning {
            background-color: #f39c12; 
            border-color: #f39c12;
            color: #fff;
            border-radius: 6px;
        }
        .btn-danger {
            background-color: var(--accent-pink); /* Use Pink for delete */
            border-color: var(--accent-pink);
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        .btn-danger:hover {
            background-color: #d12e7b;
            border-color: #d12e7b;
        }
    </style>
</head>

<body class="container mt-5">
    
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <h2 class="fw-bold page-title">
            <i class="bi bi-person-badge me-2 icon-accent"></i> จัดการสมาชิก
        </h2>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าผู้ดูแล
        </a>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i>
            <div><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <h5 class="mb-3" style="color:var(--primary-purple); font-weight:600;">รายการสมาชิกในระบบ</h5>
    <div class="table-responsive user-table-container">
        <?php if (count($users) === 0): ?>
            <div class="alert alert-warning m-0 p-4 text-center" style="border-radius: 0 0 12px 12px;">
                <i class="bi bi-info-circle me-1"></i> ยังไม่มีสมาชิกในระบบ
            </div>
        <?php else: ?>
            <table class="table table-hover m-0 text-center align-middle">
                <thead>
                    <tr class="table-header-dark">
                        <th>ชื่อผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>อีเมล</th>
                        <th>วันที่สมัคร</th>
                        <th style="width: 20%;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary" style="background-color: #e83e8c!important;"><?= htmlspecialchars($user['username']) ?></span>
                            </td>
                            <td class="text-start ps-3"><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= formatThaiDate($user['created_at']) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning me-2">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </a>
                                <button type="button" class="delete-button btn btn-danger btn-sm" data-user-id="<?= $user['user_id']; ?>">
                                    <i class="bi bi-trash"></i> ลบ
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <footer class="text-center mt-5 mb-3" style="color:#95a5a6; font-size:14px;">
        © 2025 ระบบผู้ดูแล | Nawapath
    </footer>

    <script>
        // ฟังก์ชันสำหรับแสดงกล่องยืนยัน SweetAlert2
        function showDeleteConfirmation(userId) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: 'คุณกำลังจะลบสมาชิกออกจากระบบ!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '<?= $accent_pink; ?>', // Use CSS variable or literal pink
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก',
            }).then((result) => {
                if (result.isConfirmed) {
                    // หากผู้ใช้ยืนยัน ให้สร้างฟอร์มแล้วส่งไปยัง delUser_sweet.php เพื่อลบข้อมูล
                    const form = document.createElement('form');
                    form.method = 'POST';
                    // ✅ ใช้ action ตามที่คุณกำหนด
                    form.action = 'delUser_sweet.php'; 
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'u_id';
                    input.value = userId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        
        // แนบตัวตรวจจับเหตุการณ์คลิกกับองค์ปุ่มลบทั้งหมด
        document.querySelectorAll('.delete-button').forEach((button) => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                showDeleteConfirmation(userId);
            });
        });
    </script>
</body>

</html>