<?php
// ====================================================================
// Add Teacher Interface (teachers/add.php)
// ====================================================================

$pageTitle = 'เพิ่มครูที่ปรึกษาใหม่';
$activePage = 'teachers_add';
$activeGroup = 'teachers';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username'] ?? '');
    $password     = trim($_POST['password'] ?? '');
    $teacher_code = trim($_POST['teacher_code'] ?? '');
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $department   = trim($_POST['department'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');

    if (empty($username) || empty($password) || empty($teacher_code) || empty($first_name) || empty($last_name)) {
        $error = 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน';
    } else {
        try {
            $db->beginTransaction();

            $uStmt = $db->prepare("INSERT INTO users (username, password, role, status) VALUES (:u, :p, 'teacher', 'active')");
            $uStmt->execute([':u' => $username, ':p' => password_hash($password, PASSWORD_DEFAULT)]);
            $userId = $db->lastInsertId();

            $tStmt = $db->prepare("INSERT INTO teachers (user_id, teacher_code, first_name, last_name, phone, email, department) VALUES (:uid, :code, :fn, :ln, :phone, :email, :dept)");
            $tStmt->execute([
                ':uid'   => $userId,
                ':code'  => $teacher_code,
                ':fn'    => $first_name,
                ':ln'    => $last_name,
                ':phone' => $phone,
                ':email' => $email,
                ':dept'  => $department
            ]);

            $db->commit();
            redirectUrl("index.php?msg=added");
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary me-2"></i> เพิ่มครูที่ปรึกษาใหม่</h3>
        <p class="text-muted small m-0">กรอกข้อมูลส่วนตัวและสร้างบัญชีสำหรับอาจารย์/ครูผู้สอน</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST" class="row g-3">
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-key-fill me-1"></i> 1. บัญชีผู้ใช้งานระบบ</h6>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อผู้ใช้ (Username) *</label>
            <input type="text" name="username" class="form-control" placeholder="เช่น teacher03" required>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">รหัสผ่าน (Password) *</label>
            <input type="password" name="password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร" required>
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-person-workspace me-1"></i> 2. ข้อมูลส่วนตัวและแผนกวิชา</h6>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">รหัสครู *</label>
            <input type="text" name="teacher_code" class="form-control" placeholder="เช่น T003" required>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">ชื่อ *</label>
            <input type="text" name="first_name" class="form-control" placeholder="ชื่อภาษาไทย" required>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">นามสกุล *</label>
            <input type="text" name="last_name" class="form-control" placeholder="นามสกุลภาษาไทย" required>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">แผนกวิชา/สาขา *</label>
            <select name="department" class="form-select" required>
                <option value="เทคโนโลยีสารสนเทศ" selected>เทคโนโลยีสารสนเทศ</option>
                <option value="ดิจิทัลกราฟิก">ดิจิทัลกราฟิก</option>
                <option value="การบัญชี">การบัญชี</option>
                <option value="การตลาด">การตลาด</option>
                <option value="คอมพิวเตอร์ธุรกิจ">คอมพิวเตอร์ธุรกิจ</option>
                <option value="การจัดการสำนักงาน">การจัดการสำนักงาน</option>
                <option value="คหกรรมศาสตร์">คหกรรมศาสตร์</option>
                <option value="การโรงแรมและการท่องเที่ยว">การโรงแรมและการท่องเที่ยว</option>
                <option value="อาหารและโภชนาการ">อาหารและโภชนาการ</option>
                <option value="ดีไซน์แฟชั่นและสิ่งทอ">ดีไซน์แฟชั่นและสิ่งทอ</option>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">เบอร์โทรศัพท์</label>
            <input type="text" name="phone" class="form-control" placeholder="08XXXXXXXX">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">อีเมล</label>
            <input type="email" name="email" class="form-control" placeholder="teacher@plvc.ac.th">
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> บันทึกข้อมูลครู</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
