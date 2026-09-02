<?php
// ====================================================================
// Daily Log Detailed View (daily-logs/view.php)
// ====================================================================

$pageTitle = 'รายละเอียดบันทึก Daily Log';
$activePage = 'daily_logs';
$activeGroup = 'logs';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT dl.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level,
               c.company_name
        FROM daily_logs dl
        JOIN students s ON dl.student_id = s.id
        JOIN internships i ON dl.internship_id = i.id
        JOIN companies c ON i.company_id = c.id
        WHERE dl.id = :id LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$log = $stmt->fetch();

if (!$log) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบบันทึก Daily Log นี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-journal-check text-primary me-2"></i> บันทึกประจำวันที่ <?= formatThaiDate($log['log_date']) ?></h3>
        <p class="text-muted small m-0">นักศึกษา: <?= htmlspecialchars($log['student_name']) ?> (<?=$log['student_code']?>)</p>
    </div>
    <div class="d-flex gap-2">
        <?= getStatusBadgeHtml($log['status']) ?>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-clock me-1"></i> เวลาปฏิบัติงาน</h6>
            <div class="p-3 bg-light rounded-3 d-flex justify-content-around text-center">
                <div>
                    <div class="small text-muted">เวลาเข้า</div>
                    <div class="fs-4 fw-bold text-success"><?=$log['check_in']?></div>
                </div>
                <div class="border-start"></div>
                <div>
                    <div class="small text-muted">เวลาออก</div>
                    <div class="fs-4 fw-bold text-danger"><?=$log['check_out']?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-building me-1"></i> สถานประกอบการ</h6>
            <div class="fw-bold fs-5 text-dark mb-1"><?= htmlspecialchars($log['company_name']) ?></div>
            <div class="small text-muted">วันที่บันทึก: <?= formatThaiDate($log['created_at'], true) ?></div>
        </div>

        <div class="col-12">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-2"><i class="bi bi-card-text me-1"></i> รายละเอียดงานที่ทำประจำวัน</h6>
            <div class="p-3 border rounded-3 bg-light text-slate-800">
                <?= nl2br(htmlspecialchars($log['work_description'])) ?>
            </div>
        </div>

        <?php if ($log['learning']): ?>
            <div class="col-12 col-md-6">
                <h6 class="fw-bold text-success border-bottom pb-2 mb-2"><i class="bi bi-lightbulb-fill me-1"></i> สิ่งที่เรียนรู้/ทักษะที่พัฒนา</h6>
                <div class="p-3 border rounded-3 bg-success-subtle text-success-emphasis">
                    <?= nl2br(htmlspecialchars($log['learning'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($log['problem']): ?>
            <div class="col-12 col-md-6">
                <h6 class="fw-bold text-danger border-bottom pb-2 mb-2"><i class="bi bi-exclamation-octagon-fill me-1"></i> ปัญหาที่พบและการแก้ไข</h6>
                <div class="p-3 border rounded-3 bg-danger-subtle text-danger-emphasis">
                    <?= nl2br(htmlspecialchars($log['problem'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($log['teacher_comment']): ?>
            <div class="col-12">
                <div class="p-3 border border-warning rounded-3 bg-warning-subtle">
                    <div class="fw-bold text-warning-emphasis mb-1"><i class="bi bi-chat-left-quote-fill me-1"></i> ความคิดเห็นครูผู้ตรวจ:</div>
                    <div class="text-dark small"><?= htmlspecialchars($log['teacher_comment']) ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
