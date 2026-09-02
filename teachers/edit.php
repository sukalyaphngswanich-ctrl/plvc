<?php
// ====================================================================
// Edit Teacher Interface (teachers/edit.php)
// ====================================================================

$pageTitle = 'แก้ไขข้อมูลครูที่ปรึกษา';
$activePage = 'teachers_list';
$activeGroup = 'teachers';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT t.*, u.username FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    echo "<div class='alert alert-danger'>ไม่พบข้อมูลครูที่ปรึกษา</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_code = trim($_POST['teacher_code'] ?? '');
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $department   = trim($_POST['department'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');

    if (empty($teacher_code) || empty($first_name) || empty($last_name)) {
        $error = 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน';
    } else {
        try {
            $uStmt = $db->prepare("UPDATE teachers SET teacher_code = :code, first_name = :fn, last_name = :ln, phone = :phone, email = :email, department = :dept WHERE id = :id");
            $uStmt->execute([
                ':code'  => $teacher_code,
                ':fn'    => $first_name,
                ':ln'    => $last_name,
                ':phone' => $phone,
                ':email' => $email,
                ':dept'  => $department,
                ':id'    => $id
            ]);

            if (!empty($_POST['new_password'])) {
                $hPass = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET password = :p WHERE id = :uid")->execute([':p' => $hPass, ':uid' => $teacher['user_id']]);
            }

            redirectUrl("index.php?msg=updated");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขข้อมูลครูที่ปรึกษา</h3>
        <p class="text-muted small m-0">ปรับปรุงข้อมูลอาจารย์/ครูผู้สอน</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="edit.php?id=<?=$id?>" method="POST" class="row g-3">
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">รหัสครู *</label>
            <input type="text" name="teacher_code" class="form-control" value="<?= htmlspecialchars($teacher['teacher_code']) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">ชื่อ *</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($teacher['first_name']) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">นามสกุล *</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($teacher['last_name']) ?>" required>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">แผนกวิชา *</label>
            <select name="department" class="form-select" required>
                <?php
                $depts = ['เทคโนโลยีสารสนเทศ', 'ดิจิทัลกราฟิก', 'การบัญชี', 'การตลาด', 'คอมพิวเตอร์ธุรกิจ', 'การจัดการสำนักงาน', 'คหกรรมศาสตร์', 'การโรงแรมและการท่องเที่ยว', 'อาหารและโภชนาการ', 'ดีไซน์แฟชั่นและสิ่งทอ'];
                foreach ($depts as $d):
                ?>
                    <option value="<?=$d?>" <?= $teacher['department'] === $d ? 'selected' : '' ?>><?=$d?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">เบอร์โทรศัพท์</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($teacher['phone']) ?>">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">อีเมล</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($teacher['email']) ?>">
        </div>

        <div class="col-12 col-md-6 mt-3">
            <label class="form-label fw-medium small text-secondary">เปลี่ยนรหัสผ่านใหม่ (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
            <input type="password" name="new_password" class="form-control" placeholder="กรอกรหัสผ่านใหม่...">
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning px-4"><i class="bi bi-save me-1"></i> บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
