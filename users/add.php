<?php
// ====================================================================
// Add New User Form (users/add.php)
// ====================================================================

$pageTitle = 'เพิ่มผู้ใช้ใหม่';
$activePage = 'users';
$activeGroup = 'settings';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin']);

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPwd = trim($_POST['confirm_password'] ?? '');
    $role     = $_POST['role'] ?? 'student';
    $email    = trim($_POST['email'] ?? '');
    $status   = $_POST['status'] ?? 'active';

    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } elseif ($password !== $confirmPwd) {
        $error = 'รหัสผ่านไม่ตรงกัน กรุณากรอกใหม่';
    } elseif (strlen($password) < 6) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    } else {
        // Check duplicate username
        $check = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
        $check->execute([':u' => $username]);
        if ($check->fetchColumn() > 0) {
            $error = 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว';
        } else {
            try {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO users (username, password, role, email, status) VALUES (:u, :p, :r, :e, :s)");
                $stmt->execute([':u' => $username, ':p' => $hashed, ':r' => $role, ':e' => $email, ':s' => $status]);
                redirectUrl("index.php?msg=added");
            } catch (Exception $e) {
                $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-fill-add text-primary me-2"></i> เพิ่มผู้ใช้ใหม่</h3>
        <p class="text-muted small m-0">สร้างบัญชีผู้ใช้งานใหม่เข้าสู่ระบบ</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST" class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อผู้ใช้ (Username) *</label>
            <input type="text" name="username" class="form-control" placeholder="เช่น student001" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">อีเมล</label>
            <input type="email" name="email" class="form-control" placeholder="example@email.com">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">รหัสผ่าน * (อย่างน้อย 6 ตัวอักษร)</label>
            <input type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required minlength="6">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ยืนยันรหัสผ่าน *</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">บทบาท (Role) *</label>
            <select name="role" class="form-select" required>
                <option value="student">นักศึกษา (Student)</option>
                <option value="teacher">ครูที่ปรึกษา (Teacher)</option>
                <option value="admin">ผู้ดูแลระบบ (Admin)</option>
                <option value="company">สถานประกอบการ (Company)</option>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สถานะ</label>
            <select name="status" class="form-select">
                <option value="active">ใช้งาน (Active)</option>
                <option value="inactive">ปิดใช้งาน (Inactive)</option>
            </select>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> สร้างบัญชีผู้ใช้</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
