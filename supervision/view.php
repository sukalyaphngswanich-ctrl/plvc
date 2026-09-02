<?php
// ====================================================================
// Supervision Detailed View (supervision/view.php)
// ====================================================================

$pageTitle = 'รายละเอียดผลการนิเทศการฝึกงาน';
$activePage = 'supervision';
$activeGroup = 'supervision';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT sp.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level,
               c.company_name, c.address as company_address, c.phone as company_phone,
               CONCAT(t.first_name, ' ', t.last_name) as teacher_name, t.phone as teacher_phone
        FROM supervision sp
        JOIN students s ON sp.student_id = s.id
        JOIN companies c ON sp.company_id = c.id
        JOIN teachers t ON sp.teacher_id = t.id
        WHERE sp.id = :id LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$sup = $stmt->fetch();

if (!$sup) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลการนิเทศนี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-car-front-fill text-info me-2"></i> ผลการนิเทศประจำวันที่ <?= formatThaiDate($sup['visit_date']) ?></h3>
        <p class="text-muted small m-0">นักศึกษา: <?= htmlspecialchars($sup['student_name']) ?> (<?=$sup['student_code']?>)</p>
    </div>
    <div class="d-flex gap-2">
        <?= getStatusBadgeHtml($sup['status']) ?>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-info-circle me-1"></i> ข้อมูลการนิเทศ</h6>
            <table class="table table-sm table-borderless align-middle mb-0">
                <tr><td class="text-muted" style="width:130px;">ครูผู้นิเทศ:</td><td class="fw-bold"><?= htmlspecialchars($sup['teacher_name']) ?> (<?=$sup['teacher_phone']?>)</td></tr>
                <tr><td class="text-muted">รูปแบบการนิเทศ:</td><td><span class="badge bg-light text-dark border"><?=$sup['visit_type']?></span></td></tr>
                <tr><td class="text-muted">วันที่และเวลา:</td><td><?= formatThaiDate($sup['visit_date']) ?> เวลา <?=$sup['visit_time']?> น.</td></tr>
            </table>
        </div>

        <div class="col-12 col-md-6">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-building me-1"></i> สถานประกอบการ</h6>
            <div class="fw-bold text-dark fs-5"><?= htmlspecialchars($sup['company_name']) ?></div>
            <div class="small text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($sup['company_address']) ?></div>
            <div class="small text-muted"><i class="bi bi-telephone me-1"></i> <?=$sup['company_phone']?></div>
        </div>

        <div class="col-12">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-2"><i class="bi bi-card-checklist me-1"></i> สรุปผลการนิเทศติดตาม</h6>
            <div class="p-3 border rounded-3 bg-light text-dark">
                <?= nl2br(htmlspecialchars($sup['result'])) ?>
            </div>
        </div>

        <?php if ($sup['problem']): ?>
            <div class="col-12 col-md-6">
                <h6 class="fw-bold text-danger border-bottom pb-2 mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> ปัญหาและอุปสรรคที่พบ</h6>
                <div class="p-3 border rounded-3 bg-danger-subtle text-danger-emphasis">
                    <?= nl2br(htmlspecialchars($sup['problem'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($sup['recommendation']): ?>
            <div class="col-12 col-md-6">
                <h6 class="fw-bold text-success border-bottom pb-2 mb-2"><i class="bi bi-check-circle-fill me-1"></i> ข้อเสนอแนะการปรับปรุง</h6>
                <div class="p-3 border rounded-3 bg-success-subtle text-success-emphasis">
                    <?= nl2br(htmlspecialchars($sup['recommendation'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
