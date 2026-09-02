<?php
// ====================================================================
// Edit Supervision Record (supervision/edit.php)
// ====================================================================

$pageTitle = 'แก้ไขข้อมูลการนิเทศ';
$activePage = 'supervision';
$activeGroup = 'supervision';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM supervision WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$sup = $stmt->fetch();

if (!$sup) {
    echo "<div class='alert alert-danger'>ไม่พบข้อมูลบันทึกการนิเทศ</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visit_date     = trim($_POST['visit_date'] ?? '');
    $visit_time     = trim($_POST['visit_time'] ?? '');
    $visit_type     = trim($_POST['visit_type'] ?? '');
    $result         = trim($_POST['result'] ?? '');
    $problem        = trim($_POST['problem'] ?? '');
    $recommendation = trim($_POST['recommendation'] ?? '');
    $status         = trim($_POST['status'] ?? '');

    if (empty($result)) {
        $error = 'กรุณากรอกผลการนิเทศ';
    } else {
        try {
            $uStmt = $db->prepare("UPDATE supervision SET visit_date = :vd, visit_time = :vt, visit_type = :vtype, result = :res, problem = :prob, recommendation = :rec, status = :st WHERE id = :id");
            $uStmt->execute([
                ':vd' => $visit_date, ':vt' => $visit_time, ':vtype' => $visit_type,
                ':res' => $result, ':prob' => $problem, ':rec' => $recommendation, ':st' => $status, ':id' => $id
            ]);
            redirectUrl("index.php?msg=updated");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขข้อมูลการนิเทศ</h3>
        <p class="text-muted small m-0">ปรับปรุงผลการนิเทศและข้อเสนอแนะ</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="edit.php?id=<?=$id?>" method="POST" class="row g-3">
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">วันที่นิเทศ *</label>
            <input type="date" name="visit_date" class="form-control" value="<?=$sup['visit_date']?>" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">เวลานิเทศ *</label>
            <input type="time" name="visit_time" class="form-control" value="<?=$sup['visit_time']?>" required>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">รูปแบบการนิเทศ *</label>
            <select name="visit_type" class="form-select" required>
                <option value="นิเทศที่สถานประกอบการ" <?= $sup['visit_type'] === 'นิเทศที่สถานประกอบการ' ? 'selected' : '' ?>>นิเทศที่สถานประกอบการ</option>
                <option value="นิเทศออนไลน์" <?= $sup['visit_type'] === 'นิเทศออนไลน์' ? 'selected' : '' ?>>นิเทศออนไลน์</option>
                <option value="โทรศัพท์" <?= $sup['visit_type'] === 'โทรศัพท์' ? 'selected' : '' ?>>โทรศัพท์</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">สถานะนิเทศ</label>
            <select name="status" class="form-select">
                <option value="นิเทศแล้ว" <?= $sup['status'] === 'นิเทศแล้ว' ? 'selected' : '' ?>>นิเทศแล้ว</option>
                <option value="นัดหมายแล้ว" <?= $sup['status'] === 'นัดหมายแล้ว' ? 'selected' : '' ?>>นัดหมายแล้ว</option>
                <option value="ต้องติดตามเพิ่มเติม" <?= $sup['status'] === 'ต้องติดตามเพิ่มเติม' ? 'selected' : '' ?>>ต้องติดตามเพิ่มเติม</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">ผลการนิเทศ *</label>
            <textarea name="result" class="form-control" rows="3" required><?= htmlspecialchars($sup['result']) ?></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ปัญหาที่พบ</label>
            <textarea name="problem" class="form-control" rows="2"><?= htmlspecialchars($sup['problem']) ?></textarea>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ข้อเสนอแนะ</label>
            <textarea name="recommendation" class="form-control" rows="2"><?= htmlspecialchars($sup['recommendation']) ?></textarea>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning text-white px-4"><i class="bi bi-save me-1"></i> บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
