<?php
// ====================================================================
// Add Supervision Record (supervision/add.php)
// ====================================================================

$pageTitle = 'บันทึกการนิเทศการฝึกงาน';
$activePage = 'supervision';
$activeGroup = 'supervision';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id     = (int)($_POST['student_id'] ?? 0);
    $company_id     = (int)($_POST['company_id'] ?? 0);
    $teacher_id     = hasRole('teacher') ? ($currentUser['profile']['id'] ?? 1) : (int)($_POST['teacher_id'] ?? 1);
    $visit_date     = trim($_POST['visit_date'] ?? date('Y-m-d'));
    $visit_time     = trim($_POST['visit_time'] ?? '10:00');
    $visit_type     = trim($_POST['visit_type'] ?? 'นิเทศที่สถานประกอบการ');
    $result         = trim($_POST['result'] ?? '');
    $problem        = trim($_POST['problem'] ?? '');
    $recommendation = trim($_POST['recommendation'] ?? '');
    $status         = trim($_POST['status'] ?? 'นิเทศแล้ว');

    if ($student_id <= 0 || $company_id <= 0 || empty($result)) {
        $error = 'กรุณาเลือกนักศึกษา สถานประกอบการ และกรอกผลการนิเทศ';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO supervision (student_id, company_id, teacher_id, visit_date, visit_time, visit_type, result, problem, recommendation, status)
                                  VALUES (:sid, :cid, :tid, :vdate, :vtime, :vtype, :res, :prob, :rec, :st)");
            $stmt->execute([
                ':sid' => $student_id, ':cid' => $company_id, ':tid' => $teacher_id,
                ':vdate' => $visit_date, ':vtime' => $visit_time, ':vtype' => $visit_type,
                ':res' => $result, ':prob' => $problem, ':rec' => $recommendation, ':st' => $status
            ]);

            // Notify student
            $stUser = $db->query("SELECT user_id FROM students WHERE id = $student_id")->fetchColumn();
            if ($stUser) {
                $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (:uid, 'มีการบันทึกการนิเทศการฝึกงาน', :msg, 'info')")
                   ->execute([
                       ':uid' => $stUser,
                       ':msg' => "ครูนิเทศได้บันทึกการนิเทศประจำวันที่ " . formatThaiDate($visit_date) . " เรียบร้อยแล้ว"
                   ]);
            }

            redirectUrl("index.php?msg=added");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

$studentsList = $db->query("SELECT s.id, s.student_code, CONCAT(s.first_name, ' ', s.last_name) as name, i.company_id 
                            FROM students s JOIN internships i ON s.id = i.student_id ORDER BY s.student_code ASC")->fetchAll();
$companiesList = $db->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();
$teachersList = $db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM teachers ORDER BY first_name ASC")->fetchAll();
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-car-front-fill text-info me-2"></i> บันทึกการนิเทศการฝึกงาน</h3>
        <p class="text-muted small m-0">กรอกข้อมูลนัดหมายและสรุปผลการนิเทศติดตามนักศึกษา</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST" class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">เลิอกนักศึกษา *</label>
            <select name="student_id" class="form-select" required onchange="autoSelectCompany(this)">
                <option value="">-- เลิอกนักศึกษา --</option>
                <?php foreach ($studentsList as $st): ?>
                    <option value="<?=$st['id']?>" data-cid="<?=$st['company_id']?>"><?=$st['student_code']?> - <?= htmlspecialchars($st['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สถานประกอบการ *</label>
            <select name="company_id" id="company_select" class="form-select" required>
                <option value="">-- เลือกสถานประกอบการ --</option>
                <?php foreach ($companiesList as $c): ?>
                    <option value="<?=$c['id']?>"><?= htmlspecialchars($c['company_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <script>
        function autoSelectCompany(sel) {
            const cid = sel.options[sel.selectedIndex].getAttribute('data-cid');
            if (cid) {
                document.getElementById('company_select').value = cid;
            }
        }
        </script>

        <?php if (hasRole('admin')): ?>
            <div class="col-12 col-md-4">
                <label class="form-label fw-medium small text-secondary">ครูผู้นิเทศ</label>
                <select name="teacher_id" class="form-select">
                    <?php foreach ($teachersList as $t): ?>
                        <option value="<?=$t['id']?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">วันที่นิเทศ *</label>
            <input type="date" name="visit_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">เวลานิเทศ *</label>
            <input type="time" name="visit_time" class="form-control" value="10:30" required>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">รูปแบบการนิเทศ *</label>
            <select name="visit_type" class="form-select" required>
                <option value="นิเทศที่สถานประกอบการ">นิเทศที่สถานประกอบการ</option>
                <option value="นิเทศออนไลน์">นิเทศออนไลน์</option>
                <option value="โทรศัพท์">โทรศัพท์</option>
                <option value="อื่น ๆ">อื่น ๆ</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">สถานะนิเทศ</label>
            <select name="status" class="form-select">
                <option value="นิเทศแล้ว" selected>นิเทศแล้ว</option>
                <option value="นัดหมายแล้ว">นัดหมายแล้ว</option>
                <option value="ต้องติดตามเพิ่มเติม">ต้องติดตามเพิ่มเติม</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">ผลการนิเทศ/ความเห็น *</label>
            <textarea name="result" class="form-control" rows="3" placeholder="สรุปรายละเอียดการพูดคุยกับสถานประกอบการและการปฏิบัติงานของนักศึกษา..." required></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ปัญหาที่พบ (ถ้ามี)</label>
            <textarea name="problem" class="form-control" rows="2" placeholder="ปัญหาเรื่องการเดินทาง การเข้างาน หรือทักษะ..."></textarea>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ข้อเสนอแนะ</label>
            <textarea name="recommendation" class="form-control" rows="2" placeholder="ข้อเสนอแนะสำหรับนักศึกษาและสถานประกอบการ..."></textarea>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-info text-white px-4"><i class="bi bi-save me-1"></i> บันทึกผลการนิเทศ</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
