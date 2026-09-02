<?php
// ====================================================================
// Edit User Form (users/edit.php)
// ====================================================================

$pageTitle = 'แก้ไขข้อมูลผู้ใช้';
$activePage = 'users';
$activeGroup = 'settings';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลผู้ใช้นี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $role       = $_POST['role'] ?? $user['role'];
    $status     = $_POST['status'] ?? $user['status'];
    $newPwd     = trim($_POST['password'] ?? '');
    $confirmPwd = trim($_POST['confirm_password'] ?? '');

    if (empty($username)) {
        $error = 'กรุณากรอกชื่อผู้ใช้';
    } elseif ($newPwd && $newPwd !== $confirmPwd) {
        $error = 'รหัสผ่านไม่ตรงกัน';
    } elseif ($newPwd && strlen($newPwd) < 6) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    } else {
        // Check duplicate username (exclude self)
        $check = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :u AND id != :id");
        $check->execute([':u' => $username, ':id' => $id]);
        if ($check->fetchColumn() > 0) {
            $error = 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว';
        } else {
            try {
                if ($newPwd) {
                    $hashed = password_hash($newPwd, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE users SET username = :u, password = :p, role = :r, email = :e, status = :s WHERE id = :id");
                    $stmt->execute([':u' => $username, ':p' => $hashed, ':r' => $role, ':e' => $email, ':s' => $status, ':id' => $id]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET username = :u, role = :r, email = :e, status = :s WHERE id = :id");
                    $stmt->execute([':u' => $username, ':r' => $role, ':e' => $email, ':s' => $status, ':id' => $id]);
                }
                redirectUrl("index.php?msg=updated");
            } catch (Exception $e) {
                $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขข้อมูลผู้ใช้</h3>
        <p class="text-muted small m-0">แก้ไขข้อมูลบัญชี: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="edit.php?id=<?=$id?>" method="POST" class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อผู้ใช้ (Username) *</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">อีเมล</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">รหัสผ่านใหม่ <span class="text-muted">(เว้นว่างถ้าไม่ต้องการเปลี่ยน)</span></label>
            <input type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่านใหม่" minlength="6">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="กรอกรหัสผ่านอีกครั้ง">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">บทบาท (Role)</label>
            <select name="role" class="form-select">
                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>นักศึกษา (Student)</option>
                <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>ครูที่ปรึกษา (Teacher)</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>ผู้ดูแลระบบ (Admin)</option>
                <option value="company" <?= $user['role'] === 'company' ? 'selected' : '' ?>>สถานประกอบการ (Company)</option>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สถานะ</label>
            <select name="status" class="form-select">
                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>ใช้งาน (Active)</option>
                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>ปิดใช้งาน (Inactive)</option>
            </select>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning px-4"><i class="bi bi-save me-1"></i> บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
