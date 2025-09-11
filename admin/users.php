<?php
require_once '../config.php';//เชื่อมฐานข้อมูล
require_once 'auth_admin.php';//เชื่อมฐานข้อมูล
// ลบสมำชกิ
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
// ดงึขอ้ มลู สมำชกิ
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดก่รสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


</head>

<body class="container mt-4">
    <h2>จัดการสมาชิก</h2>
    <a href="index.php" class="btn btn-secondary mb-3">←กลับหน้าผู้ดูแล</a>
    <?php if (count($users) === 0): ?>
        <div class="alert alert-warning">ยังไม่มีสมาชิกในระบบ</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ชื่อผู้ใช้</th>
                    <th>ชื่อ-นำมสกุล</th>
                    <th>อีเมล</th>
                    <th>วันที่สมัคร</th>
                    <th>จัดกำร</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= $user['created_at'] ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">แก ้ไข
                            </a>
                            <!-- <a href="users.php?delete=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('คุณต้องการลบสมาชิกหรือไม่?' ่ )">ลบ</a> -->
                            <form action="delUser_sweet.php" method="POST" style="display:inline;">
                                <input type="hidden" name="u_id" value="<?php echo $user['user_id']; ?>">
                                <button type="button" class="delete-button btn btn-danger btn-sm " data-user-id="<?php echo
                                    $user['user_id']; ?>">ลบ</button>
                            </form>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ฟังกช์ นั ส ำหรับแสดงกลอ่ งยนื ยัน SweetAlert2
    function showDeleteConfirmation(userId) {
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: 'คุณจะไม่สำมำรถเรียกคืนข ้อมูลกลับได ้!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
        }).then((result) => {
            if (result.isConfirmed) {
                // หำกผใู้ชย้นื ยัน ใหส้ ง่ คำ่ ฟอรม์ ไปยัง delete.php เพื่อลบข ้อมูล
                const form = document.createElement('form');
                form.method = 'POST';
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
    // แนบตัวตรวจจับเหตุกำรณ์คลิกกับองค์ปุ ่่มลบทั ่ ้งหมดที่มีคลำส delete-button
    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const userId = button.getAttribute('data-user-id');
            showDeleteConfirmation(userId);
        });
    });
</script>


</html>