<?php
// ====================================================================
// Edit Internship Interface (internships/edit.php)
// ====================================================================

$pageTitle = 'แก้ไขข้อมูลการฝึกงาน';
$activePage = 'internships_list';
$activeGroup = 'internships';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM internships WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$intern = $stmt->fetch();

if (!$intern) {
    echo "<div class='alert alert-danger'>ไม่พบรายการข้อมูลการฝึกงาน</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position        = trim($_POST['position'] ?? '');
    $job_description = trim($_POST['job_description'] ?? '');
    $start_date      = trim($_POST['start_date'] ?? '');
    $end_date        = trim($_POST['end_date'] ?? '');
    $supervisor_name = trim($_POST['supervisor_name'] ?? '');
    $status          = trim($_POST['status'] ?? '');

    if (empty($position) || empty($start_date) || empty($end_date)) {
        $error = 'กรุณากรอกตำแหน่งและระยะเวลาฝึกงาน';
    } else {
        try {
            $uStmt = $db->prepare("UPDATE internships SET position = :pos, job_description = :jdesc, start_date = :sd, end_date = :ed, supervisor_name = :sname, status = :st WHERE id = :id");
            $uStmt->execute([
                ':pos' => $position, ':jdesc' => $job_description, ':sd' => $start_date, ':ed' => $end_date, ':sname' => $supervisor_name, ':st' => $status, ':id' => $id
            ]);

            // Sync status to student
            $db->prepare("UPDATE students SET internship_status = :st WHERE id = :sid")->execute([':st' => $status, ':sid' => $intern['student_id']]);

            redirectUrl("index.php?msg=updated");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขข้อมูลการฝึกงาน</h3>
        <p class="text-muted small m-0">ปรับปรุงรายละเอียดการฝึกปฏิบัติงานและสถานะ</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="edit.php?id=<?=$id?>" method="POST" class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ตำแหน่งงาน *</label>
            <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($intern['position']) ?>" required>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">วันที่เริ่มฝึก *</label>
            <input type="date" name="start_date" class="form-control" value="<?=$intern['start_date']?>" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">วันที่สิ้นสุด *</label>
            <input type="date" name="end_date" class="form-control" value="<?=$intern['end_date']?>" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">ลักษณะงานที่รับผิดชอบ</label>
            <textarea name="job_description" class="form-control" rows="2"><?= htmlspecialchars($intern['job_description']) ?></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อพี่เลี้ยง</label>
            <input type="text" name="supervisor_name" class="form-control" value="<?= htmlspecialchars($intern['supervisor_name']) ?>">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สถานะการฝึกงาน</label>
            <select name="status" class="form-select">
                <option value="กำลังฝึกงาน" <?= $intern['status'] === 'กำลังฝึกงาน' ? 'selected' : '' ?>>กำลังฝึกงาน</option>
                <option value="พร้อมฝึกงาน" <?= $intern['status'] === 'พร้อมฝึกงาน' ? 'selected' : '' ?>>พร้อมฝึกงาน</option>
                <option value="ฝึกงานเสร็จแล้ว" <?= $intern['status'] === 'ฝึกงานเสร็จแล้ว' ? 'selected' : '' ?>>ฝึกงานเสร็จแล้ว</option>
                <option value="มีปัญหา" <?= $intern['status'] === 'มีปัญหา' ? 'selected' : '' ?>>มีปัญหา</option>
                <option value="ยกเลิก" <?= $intern['status'] === 'ยกเลิก' ? 'selected' : '' ?>>ยกเลิก</option>
            </select>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning text-white px-4"><i class="bi bi-save me-1"></i> บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
