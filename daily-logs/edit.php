<?php
// ====================================================================
// Edit Daily Log Entry (daily-logs/edit.php)
// ====================================================================

$pageTitle = 'แก้ไขบันทึก Daily Log';
$activePage = 'daily_logs';
$activeGroup = 'logs';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM daily_logs WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$log = $stmt->fetch();

if (!$log) {
    echo "<div class='alert alert-danger'>ไม่พบบันทึก Daily Log นี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_description = trim($_POST['work_description'] ?? '');
    $learning         = trim($_POST['learning'] ?? '');
    $problem          = trim($_POST['problem'] ?? '');
    $solution         = trim($_POST['solution'] ?? '');

    if (empty($work_description)) {
        $error = 'กรุณากรอกรายละเอียดงาน';
    } else {
        try {
            $uStmt = $db->prepare("UPDATE daily_logs SET work_description = :wd, learning = :ln, problem = :pr, solution = :sl, status = 'รอตรวจสอบ' WHERE id = :id");
            $uStmt->execute([':wd' => $work_description, ':ln' => $learning, ':pr' => $problem, ':sl' => $solution, ':id' => $id]);
            redirectUrl("index.php?msg=updated");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขบันทึก Daily Log</h3>
        <p class="text-muted small m-0">ปรับปรุงรายละเอียดงานประจำวันที่ <?= formatThaiDate($log['log_date']) ?></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="edit.php?id=<?=$id?>" method="POST" class="row g-3">
        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">งานที่ปฏิบัติประจำวัน *</label>
            <textarea name="work_description" class="form-control" rows="3" required><?= htmlspecialchars($log['work_description']) ?></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สิ่งที่เรียนรู้</label>
            <textarea name="learning" class="form-control" rows="2"><?= htmlspecialchars($log['learning']) ?></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ปัญหาและแนวทางแก้ไข</label>
            <textarea name="problem" class="form-control" rows="2"><?= htmlspecialchars($log['problem']) ?></textarea>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning text-white px-4"><i class="bi bi-save me-1"></i> บันทึกแก้ไข</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
