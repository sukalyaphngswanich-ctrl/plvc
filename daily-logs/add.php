<?php
// ====================================================================
// Add Daily Log Entry (daily-logs/add.php)
// ====================================================================

$pageTitle = 'เพิ่มบันทึกการฝึกงานประจำวัน';
$activePage = 'daily_logs';
$activeGroup = 'logs';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$error = '';

$studentId = 0;
if (hasRole('student')) {
    $studentId = $currentUser['profile']['id'] ?? 0;
} else if (isset($_GET['student_id'])) {
    $studentId = (int)$_GET['student_id'];
}

// Fetch active internship for this student
$internStmt = $db->prepare("SELECT id FROM internships WHERE student_id = :sid LIMIT 1");
$internStmt->execute([':sid' => $studentId]);
$internshipId = $internStmt->fetchColumn();

if (!$internshipId && hasRole('student')) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลการฝึกงานของคุณ กรุณาติดต่อครูที่ปรึกษาเพื่อลงทะเบียนการฝึกงาน</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postStudentId    = hasRole('student') ? $studentId : (int)$_POST['student_id'];
    $postInternshipId = (int)$_POST['internship_id'];
    $log_date         = trim($_POST['log_date'] ?? date('Y-m-d'));
    $check_in         = trim($_POST['check_in'] ?? '08:30');
    $check_out        = trim($_POST['check_out'] ?? '17:30');
    $work_description = trim($_POST['work_description'] ?? '');
    $learning         = trim($_POST['learning'] ?? '');
    $problem          = trim($_POST['problem'] ?? '');
    $solution         = trim($_POST['solution'] ?? '');
    $note             = trim($_POST['note'] ?? '');

    if (empty($work_description) || empty($log_date)) {
        $error = 'กรุณากรอกวันที่และรายละเอียดงานที่ทำประจำวัน';
    } else {
        try {
            $db->beginTransaction();

            // 1. Insert or Update into daily_logs (Universal cross-database compatible)
            $existingLog = $db->prepare("SELECT id FROM daily_logs WHERE student_id = :sid AND log_date = :ldate LIMIT 1");
            $existingLog->execute([':sid' => $postStudentId, ':ldate' => $log_date]);
            $logId = $existingLog->fetchColumn();

            if ($logId) {
                $stmt = $db->prepare("UPDATE daily_logs SET internship_id = :iid, check_in = :cin, check_out = :cout, work_description = :wdesc, learning = :learn, problem = :prob, solution = :sol, note = :note, status = 'รอตรวจสอบ' WHERE id = :id");
                $stmt->execute([
                    ':iid' => $postInternshipId, ':cin' => $check_in, ':cout' => $check_out,
                    ':wdesc' => $work_description, ':learn' => $learning, ':prob' => $problem,
                    ':sol' => $solution, ':note' => $note, ':id' => $logId
                ]);
            } else {
                $stmt = $db->prepare("INSERT INTO daily_logs (student_id, internship_id, log_date, check_in, check_out, work_description, learning, problem, solution, note, status)
                                      VALUES (:sid, :iid, :ldate, :cin, :cout, :wdesc, :learn, :prob, :sol, :note, 'รอตรวจสอบ')");
                $stmt->execute([
                    ':sid' => $postStudentId, ':iid' => $postInternshipId, ':ldate' => $log_date,
                    ':cin' => $check_in, ':cout' => $check_out, ':wdesc' => $work_description,
                    ':learn' => $learning, ':prob' => $problem, ':sol' => $solution, ':note' => $note
                ]);
            }

            // 2. Sync Attendance entry (Universal cross-database compatible)
            $hours = 8.00;
            if ($check_in && $check_out) {
                $diff = (strtotime($check_out) - strtotime($check_in)) / 3600;
                if ($diff >= 5) $diff -= 1;
                $hours = max(0, round($diff, 2));
            }
            $attStatus = (strtotime($check_in) > strtotime('08:35:00')) ? 'มาสาย' : 'ปกติ';

            $existingAtt = $db->prepare("SELECT id FROM attendance WHERE student_id = :sid AND attendance_date = :adate LIMIT 1");
            $existingAtt->execute([':sid' => $postStudentId, ':adate' => $log_date]);
            $attId = $existingAtt->fetchColumn();

            if ($attId) {
                $attStmt = $db->prepare("UPDATE attendance SET internship_id = :iid, check_in = :cin, check_out = :cout, total_hours = :th, status = :ast WHERE id = :id");
                $attStmt->execute([
                    ':iid' => $postInternshipId, ':cin' => $check_in, ':cout' => $check_out,
                    ':th' => $hours, ':ast' => $attStatus, ':id' => $attId
                ]);
            } else {
                $attStmt = $db->prepare("INSERT INTO attendance (student_id, internship_id, attendance_date, check_in, check_out, total_hours, status)
                                        VALUES (:sid, :iid, :adate, :cin, :cout, :th, :ast)");
                $attStmt->execute([
                    ':sid' => $postStudentId, ':iid' => $postInternshipId, ':adate' => $log_date,
                    ':cin' => $check_in, ':cout' => $check_out, ':th' => $hours, ':ast' => $attStatus
                ]);
            }

            // Send notification to advisor
            $advStmt = $db->prepare("SELECT advisor_id FROM students WHERE id = :sid LIMIT 1");
            $advStmt->execute([':sid' => $postStudentId]);
            $advId = $advStmt->fetchColumn();
            if ($advId) {
                $advUser = $db->query("SELECT user_id FROM teachers WHERE id = $advId")->fetchColumn();
                if ($advUser) {
                    $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (:uid, 'Daily Log ใหม่รอการตรวจ', :msg, 'info')")
                       ->execute([
                           ':uid' => $advUser,
                           ':msg' => "นักศึกษาได้ส่ง Daily Log ประจำวันที่ " . formatThaiDate($log_date) . " รอการตรวจ"
                       ]);
                }
            }

            $db->commit();
            redirectUrl("index.php?msg=saved");echo "<script>window.location.href='index.php?msg=saved';</script>";
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

$studentsList = [];
if (hasRole(['admin', 'teacher'])) {
    $studentsList = $db->query("SELECT s.id, s.student_code, CONCAT(s.first_name, ' ', s.last_name) as name, s.department, s.room, i.id as internship_id FROM students s JOIN internships i ON s.id = i.student_id ORDER BY s.department, s.room, s.student_code ASC")->fetchAll();
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-journal-plus text-danger me-2"></i> บันทึกการฝึกงานประจำวัน</h3>
        <p class="text-muted small m-0">กรอกเวลาเข้า-ออก รายละเอียดงาน และสิ่งที่เรียนรู้ประจำวัน</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST" class="row g-3">
        <?php if (hasRole(['admin', 'teacher'])): ?>
            <div class="col-12 col-md-6">
                <label class="form-label fw-medium small text-secondary">เลิอกนักศึกษา *</label>
                <select name="student_id" id="student_select" class="form-select" required onchange="updateInternshipId(this)">
                    <option value="">-- เลือกนักศึกษา --</option>
                    <?php foreach ($studentsList as $st): ?>
                        <option value="<?=$st['id']?>" data-iid="<?=$st['internship_id']?>" <?= $studentId == $st['id'] ? 'selected' : '' ?>><?=$st['student_code']?> - <?= htmlspecialchars($st['name']) ?> [<?= htmlspecialchars($st['department']) ?> - <?= htmlspecialchars($st['room']) ?>]</option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="internship_id" id="internship_id_input" value="<?=$internshipId?>">
            </div>
            <script>
            function updateInternshipId(sel) {
                const opt = sel.options[sel.selectedIndex];
                document.getElementById('internship_id_input').value = opt.getAttribute('data-iid') || '';
            }
            </script>
        <?php else: ?>
            <input type="hidden" name="student_id" value="<?=$studentId?>">
            <input type="hidden" name="internship_id" value="<?=$internshipId?>">
        <?php endif; ?>

        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">วันที่บันทึก *</label>
            <input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="col-6 col-md-4">
            <label class="form-label fw-medium small text-secondary">เวลาเข้าฝึกงาน *</label>
            <input type="time" name="check_in" id="check_in" class="form-control" value="08:30" required>
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label fw-medium small text-secondary">เวลาออกฝึกงาน *</label>
            <input type="time" name="check_out" id="check_out" class="form-control" value="17:30" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">งานที่ปฏิบัติประจำวันนี้ (Work Description) *</label>
            <textarea name="work_description" class="form-control" rows="3" placeholder="อธิบายกิจกรรมและงานที่ได้รับมอบหมายในวันนี้..." required></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สิ่งที่ได้เรียนรู้/ทักษะใหม่ (Learning)</label>
            <textarea name="learning" class="form-control" rows="2" placeholder="ความรู้ เครื่องมือ หรือเทคนิคใหม่ที่ได้รับ..."></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ปัญหา/อุปสรรค และวิธีแก้ไข (Problems & Solutions)</label>
            <textarea name="problem" class="form-control" rows="2" placeholder="ปัญหาที่พบและวิธีการรับมือแก้ไข..."></textarea>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-danger px-4"><i class="bi bi-save me-1"></i> บันทึก Daily Log</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
