<?php
// ====================================================================
// Add Internship Registration (internships/add.php)
// ====================================================================

$pageTitle = 'ลงทะเบียนฝึกงาน';
$activePage = 'internships_list';
$activeGroup = 'internships';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher', 'student']);

$db = getDB();
$error = '';
$preSelectedCompany = (int)($_GET['company_id'] ?? 0);

$currentStudentId = 0;
$currentAdvisorId = null;
if (hasRole('student')) {
    $stStmt = $db->prepare("SELECT id, advisor_id FROM students WHERE user_id = :uid LIMIT 1");
    $stStmt->execute([':uid' => $currentUser['id']]);
    $stRow = $stStmt->fetch();
    if ($stRow) {
        $currentStudentId = (int)$stRow['id'];
        $currentAdvisorId = $stRow['advisor_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id            = hasRole('student') ? $currentStudentId : (int)($_POST['student_id'] ?? 0);
    $company_id            = (int)($_POST['company_id'] ?? 0);
    $advisor_id            = !empty($_POST['advisor_id']) ? (int)$_POST['advisor_id'] : $currentAdvisorId;
    $supervisor_name       = trim($_POST['supervisor_name'] ?? '');
    $supervisor_position   = trim($_POST['supervisor_position'] ?? '');
    $supervisor_phone      = trim($_POST['supervisor_phone'] ?? '');
    $supervisor_email      = trim($_POST['supervisor_email'] ?? '');
    $position              = trim($_POST['position'] ?? 'นักศึกษาฝึกงาน');
    $department            = trim($_POST['department'] ?? '');
    $job_description       = trim($_POST['job_description'] ?? '');
    $start_date            = trim($_POST['start_date'] ?? '2026-08-01');
    $end_date              = trim($_POST['end_date'] ?? '2026-09-30');
    $working_hours_per_day = (float)($_POST['working_hours_per_day'] ?? 8.0);
    $total_hours           = (float)($_POST['total_hours'] ?? 320.0);
    $status                = trim($_POST['status'] ?? 'กำลังฝึกงาน');

    if ($student_id <= 0 || $company_id <= 0 || empty($position)) {
        $error = 'กรุณาเลือกนักศึกษา สถานประกอบการ และกรอกตำแหน่งฝึกงาน';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO internships (student_id, company_id, advisor_id, supervisor_name, supervisor_position, supervisor_phone, supervisor_email, position, department, job_description, start_date, end_date, working_hours_per_day, total_hours, status)
                                  VALUES (:sid, :cid, :aid, :sname, :spos, :sph, :sem, :pos, :dept, :jdesc, :sdate, :edate, :whpd, :th, :st)");
            $stmt->execute([
                ':sid' => $student_id, ':cid' => $company_id, ':aid' => $advisor_id,
                ':sname' => $supervisor_name, ':spos' => $supervisor_position, ':sph' => $supervisor_phone, ':sem' => $supervisor_email,
                ':pos' => $position, ':dept' => $department, ':jdesc' => $job_description,
                ':sdate' => $start_date, ':edate' => $end_date, ':whpd' => $working_hours_per_day, ':th' => $total_hours, ':st' => $status
            ]);

            // Update student internship_status
            $db->prepare("UPDATE students SET internship_status = :st WHERE id = :sid")->execute([':st' => $status, ':sid' => $student_id]);

            redirectUrl("index.php?msg=added");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

// Fetch unassigned or available students
$studentsList = $db->query("SELECT id, student_code, CONCAT(first_name, ' ', last_name) as name, class_level FROM students ORDER BY student_code ASC")->fetchAll();
$companiesList = $db->query("SELECT id, company_name, province FROM companies ORDER BY company_name ASC")->fetchAll();
$teachersList = $db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM teachers ORDER BY first_name ASC")->fetchAll();
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-plus-circle-fill text-success me-2"></i> ลงทะเบียนการฝึกงาน</h3>
        <p class="text-muted small m-0">ระบุสถานประกอบการ ตำแหน่งงาน และกำหนดระยะเวลาการฝึกงาน</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'company_added'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> เพิ่มข้อมูลสถานประกอบการสำเร็จแล้ว! กรุณากรอกรายละเอียดเพื่อลงทะเบียนฝึกงานด้านล่าง
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST" class="row g-3">
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-person-fill me-1"></i> 1. นักศึกษาและสถานประกอบการ</h6>
        
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">นักศึกษา *</label>
            <?php if (hasRole('student')): ?>
                <?php
                $stName = '';
                foreach ($studentsList as $s) {
                    if ($s['id'] == $currentStudentId) $stName = $s['student_code'] . ' - ' . $s['name'] . ' (' . $s['class_level'] . ')';
                }
                ?>
                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($stName ?: $currentUser['username']) ?>" readonly>
                <input type="hidden" name="student_id" value="<?=$currentStudentId?>">
            <?php else: ?>
                <select name="student_id" class="form-select" required>
                    <option value="">-- เลือกนักศึกษา --</option>
                    <?php foreach ($studentsList as $s): ?>
                        <option value="<?=$s['id']?>"><?=$s['student_code']?> - <?= htmlspecialchars($s['name']) ?> (<?=$s['class_level']?>)</option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <div class="col-12 col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-medium small text-secondary m-0">เลือกสถานประกอบการ *</label>
                <a href="../companies/add.php" class="small text-primary text-decoration-none"><i class="bi bi-plus-circle me-1"></i>เพิ่มสถานประกอบการใหม่</a>
            </div>
            <select name="company_id" class="form-select" required>
                <option value="">-- เลือกสถานประกอบการ --</option>
                <?php foreach ($companiesList as $c): ?>
                    <option value="<?=$c['id']?>" <?= ($preSelectedCompany == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['company_name']) ?> (<?=$c['province']?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ตำแหน่งงานฝึกปฏิบัติ *</label>
            <input type="text" name="position" class="form-control" placeholder="เช่น Full-Stack Developer Intern" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ครูที่ปรึกษาการฝึกงาน</label>
            <select name="advisor_id" class="form-select">
                <option value="">-- เลือกครูที่ปรึกษา --</option>
                <?php foreach ($teachersList as $t): ?>
                    <option value="<?=$t['id']?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">ลักษณะงานที่รับผิดชอบ</label>
            <textarea name="job_description" class="form-control" rows="2" placeholder="อธิบายหน้าที่หลักและงานที่ต้องปฏิบัติ..."></textarea>
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-calendar-event me-1"></i> 2. กำหนดระยะเวลาและพี่เลี้ยง</h6>

        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">วันที่เริ่มฝึก *</label>
            <input type="date" name="start_date" class="form-control" value="2026-08-01" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">วันที่สิ้นสุด *</label>
            <input type="date" name="end_date" class="form-control" value="2026-09-30" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">ชั่วโมงปฏิบัติงาน/วัน</label>
            <input type="number" step="0.5" name="working_hours_per_day" class="form-control" value="8.0">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">ชั่วโมงรวมเป้าหมาย</label>
            <input type="number" step="1" name="total_hours" class="form-control" value="320">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อ-นามสกุล พี่เลี้ยงในสถานประกอบการ</label>
            <input type="text" name="supervisor_name" class="form-control" placeholder="เช่น คุณประเสริฐ งานดี">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">เบอร์โทรพี่เลี้ยง</label>
            <input type="text" name="supervisor_phone" class="form-control" placeholder="08XXXXXXXX">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สถานะการฝึกงาน</label>
            <select name="status" class="form-select">
                <option value="กำลังฝึกงาน" selected>กำลังฝึกงาน</option>
                <option value="พร้อมฝึกงาน">พร้อมฝึกงาน</option>
                <option value="ฝึกงานเสร็จแล้ว">ฝึกงานเสร็จแล้ว</option>
                <option value="มีปัญหา">มีปัญหา</option>
            </select>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i> บันทึกข้อมูลการฝึกงาน</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
