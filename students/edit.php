<?php
// ====================================================================
// Edit Student Interface (students/edit.php)
// ====================================================================

$pageTitle = 'แก้ไขข้อมูลนักศึกษา';
$activePage = 'students_list';
$activeGroup = 'students';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    echo "<div class='alert alert-danger'>ไม่พบข้อมูลนักศึกษา</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_code  = trim($_POST['student_code'] ?? '');
    $first_name    = trim($_POST['first_name'] ?? '');
    $last_name     = trim($_POST['last_name'] ?? '');
    $class_level   = trim($_POST['class_level'] ?? '');
    $room          = trim($_POST['room'] ?? '');
    $department    = trim($_POST['department'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $advisor_id    = !empty($_POST['advisor_id']) ? (int)$_POST['advisor_id'] : null;
    $status        = trim($_POST['internship_status'] ?? '');

    if (empty($student_code) || empty($first_name) || empty($last_name)) {
        $error = 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน';
    } else {
        try {
            $uStmt = $db->prepare("UPDATE students SET 
                student_code = :code, first_name = :fn, last_name = :ln,
                class_level = :cl, room = :rm, department = :dept,
                academic_year = :ay, phone = :phone, email = :email,
                address = :addr, advisor_id = :adv, internship_status = :st
                WHERE id = :id");
            $uStmt->execute([
                ':code'  => $student_code,
                ':fn'    => $first_name,
                ':ln'    => $last_name,
                ':cl'    => $class_level,
                ':rm'    => $room,
                ':dept'  => $department,
                ':ay'    => $academic_year,
                ':phone' => $phone,
                ':email' => $email,
                ':addr'  => $address,
                ':adv'   => $advisor_id,
                ':st'    => $status,
                ':id'    => $id
            ]);

            // Optional Password Reset if provided
            if (!empty($_POST['new_password'])) {
                $hPass = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET password = :p WHERE id = :uid")->execute([':p' => $hPass, ':uid' => $student['user_id']]);
            }

            redirectUrl("index.php?msg=updated");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

$teachersList = $db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, department FROM teachers ORDER BY first_name ASC")->fetchAll();
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขข้อมูลนักศึกษา</h3>
        <p class="text-muted small m-0">ปรับปรุงข้อมูลส่วนตัว ครูที่ปรึกษา และสถานะการฝึกงาน</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="edit.php?id=<?=$id?>" method="POST" class="row g-3">
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-person-badge-fill me-1"></i> ข้อมูลนักศึกษา</h6>

        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">รหัสนักศึกษา *</label>
            <input type="text" name="student_code" class="form-control" value="<?= htmlspecialchars($student['student_code']) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">ชื่อ *</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">นามสกุล *</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">ระดับชั้น *</label>
            <select name="class_level" class="form-select" required>
                <option value="ปวส.2" <?= $student['class_level'] === 'ปวส.2' ? 'selected' : '' ?>>ปวส.2</option>
                <option value="ปวส.1" <?= $student['class_level'] === 'ปวส.1' ? 'selected' : '' ?>>ปวส.1</option>
                <option value="ปวช.3" <?= $student['class_level'] === 'ปวช.3' ? 'selected' : '' ?>>ปวช.3</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">ห้อง / กลุ่มเรียน *</label>
            <select name="room" class="form-select" required>
                <?php
                $rooms = ['สท.2/1', 'สท.2/2', 'สท.1/1', 'ดก.2/1', 'ดก.1/1', 'บช.2/1', 'บช.2/2', 'ตล.2/1', 'คบ.2/1', 'สพ.2/1'];
                foreach ($rooms as $r):
                ?>
                    <option value="<?=$r?>" <?= $student['room'] === $r ? 'selected' : '' ?>><?=$r?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">สาขาวิชา *</label>
            <select name="department" class="form-select" required>
                <?php
                $depts = ['เทคโนโลยีสารสนเทศ', 'ดิจิทัลกราฟิก', 'การบัญชี', 'การตลาด', 'คอมพิวเตอร์ธุรกิจ', 'การจัดการสำนักงาน', 'คหกรรมศาสตร์', 'การโรงแรมและการท่องเที่ยว', 'อาหารและโภชนาการ', 'ดีไซน์แฟชั่นและสิ่งทอ'];
                foreach ($depts as $d):
                ?>
                    <option value="<?=$d?>" <?= $student['department'] === $d ? 'selected' : '' ?>><?=$d?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">ปีการศึกษา *</label>
            <input type="text" name="academic_year" class="form-control" value="<?= htmlspecialchars($student['academic_year']) ?>" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">เบอร์โทรศัพท์</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">อีเมล</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>">
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">ที่อยู่</label>
            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($student['address']) ?></textarea>
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-sliders me-1"></i> ครูที่ปรึกษา & รหัสผ่าน</h6>

        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">ครูที่ปรึกษา</label>
            <select name="advisor_id" class="form-select">
                <option value="">-- เลือกครูที่ปรึกษา --</option>
                <?php foreach ($teachersList as $t): ?>
                    <option value="<?=$t['id']?>" <?= $student['advisor_id'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">สถานะการฝึกงาน</label>
            <select name="internship_status" class="form-select">
                <option value="ยังไม่เริ่มฝึก" <?= $student['internship_status'] === 'ยังไม่เริ่มฝึก' ? 'selected' : '' ?>>ยังไม่เริ่มฝึก</option>
                <option value="กำลังฝึกงาน" <?= $student['internship_status'] === 'กำลังฝึกงาน' ? 'selected' : '' ?>>กำลังฝึกงาน</option>
                <option value="ฝึกงานเสร็จแล้ว" <?= $student['internship_status'] === 'ฝึกงานเสร็จแล้ว' ? 'selected' : '' ?>>ฝึกงานเสร็จแล้ว</option>
                <option value="มีปัญหา" <?= $student['internship_status'] === 'มีปัญหา' ? 'selected' : '' ?>>มีปัญหา</option>
                <option value="ยกเลิก" <?= $student['internship_status'] === 'ยกเลิก' ? 'selected' : '' ?>>ยกเลิก</option>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">รีเซ็ตรหัสผ่านใหม่ (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
            <input type="password" name="new_password" class="form-control" placeholder="กรอกรหัสผ่านใหม่...">
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning px-4"><i class="bi bi-save me-1"></i> บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
