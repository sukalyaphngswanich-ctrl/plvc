<?php
// ====================================================================
// Internship Detailed View & Interactive Timeline (internships/view.php)
// ====================================================================

$pageTitle = 'รายละเอียดและ Timeline การฝึกงาน';
$activePage = 'internships_timeline';
$activeGroup = 'internships';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Determine ID or Student ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0 && hasRole('student')) {
    $sId = $currentUser['profile']['id'] ?? 0;
    $stmt = $db->prepare("SELECT id FROM internships WHERE student_id = :sid LIMIT 1");
    $stmt->execute([':sid' => $sId]);
    $id = $stmt->fetchColumn() ?: 0;
}

$sql = "SELECT i.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level, s.phone as student_phone,
               c.company_name, c.address as company_address, c.phone as company_phone,
               CONCAT(t.first_name, ' ', t.last_name) as advisor_name, t.phone as advisor_phone
        FROM internships i
        JOIN students s ON i.student_id = s.id
        JOIN companies c ON i.company_id = c.id
        LEFT JOIN teachers t ON i.advisor_id = t.id
        WHERE i.id = :id LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$intern = $stmt->fetch();

if (!$intern) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลการฝึกงาน</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$m = calculateInternshipMetrics($intern['start_date'], $intern['end_date']);

// 10 Steps Timeline Config
$steps = [
    1 => ['name' => '1. ลงทะเบียน', 'status' => 'completed'],
    2 => ['name' => '2. เลือกสถานที่', 'status' => 'completed'],
    3 => ['name' => '3. ยืนยันสถานที่', 'status' => 'completed'],
    4 => ['name' => '4. เตรียมตัวฝึก', 'status' => 'completed'],
    5 => ['name' => '5. เริ่มฝึกงาน', 'status' => 'completed'],
    6 => ['name' => '6. บันทึก Daily Log', 'status' => ($intern['status'] === 'กำลังฝึกงาน' ? 'current' : ($intern['status'] === 'ฝึกงานเสร็จแล้ว' ? 'completed' : 'upcoming'))],
    7 => ['name' => '7. ครูติดตาม', 'status' => ($intern['status'] === 'กำลังฝึกงาน' ? 'current' : ($intern['status'] === 'ฝึกงานเสร็จแล้ว' ? 'completed' : 'upcoming'))],
    8 => ['name' => '8. นิเทศการฝึก', 'status' => 'upcoming'],
    9 => ['name' => '9. การประเมิน', 'status' => 'upcoming'],
    10 => ['name' => '10. ฝึกงานเสร็จสิ้น', 'status' => ($intern['status'] === 'ฝึกงานเสร็จแล้ว' ? 'completed' : 'upcoming')]
];
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i> Timeline & รายละเอียดการฝึกงาน</h3>
        <p class="text-muted small m-0">ติดตามขั้นตอนลำดับการฝึกปฏิบัติงาน 10 ขั้นตอน</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<!-- Timeline Stepper Component Card -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-diagram-3-fill me-1"></i> ลำดับขั้นตอนการฝึกงาน (Internship Timeline)</h6>
    
    <div class="timeline-stepper d-none d-md-flex">
        <?php foreach ($steps as $stepNo => $st): ?>
            <div class="timeline-step <?=$st['status']?>">
                <div class="step-circle">
                    <?php if ($st['status'] === 'completed'): ?>
                        <i class="bi bi-check-lg"></i>
                    <?php else: ?>
                        <?=$stepNo?>
                    <?php endif; ?>
                </div>
                <div class="step-label"><?=$st['name']?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Mobile Vertical Timeline -->
    <div class="d-md-none">
        <ul class="list-group list-group-flush">
            <?php foreach ($steps as $stepNo => $st): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                    <span class="small fw-semibold"><?=$st['name']?></span>
                    <?php if ($st['status'] === 'completed'): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> สำเร็จ</span>
                    <?php elseif ($st['status'] === 'current'): ?>
                        <span class="badge bg-primary"><i class="bi bi-play-circle me-1"></i> กำลังดำเนินการ</span>
                    <?php else: ?>
                        <span class="badge bg-light text-muted border">ยังไม่ถึงขั้นตอน</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Overview Cards -->
<div class="row g-4">
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-building me-1"></i> สถานประกอบการ & ครูที่ปรึกษา</h6>
            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($intern['company_name']) ?></h5>
            <p class="text-primary small fw-semibold mb-3">ตำแหน่ง: <?= htmlspecialchars($intern['position']) ?></p>
            
            <table class="table table-sm table-borderless align-middle mb-0">
                <tr><td class="text-muted" style="width:130px;">นักศึกษา:</td><td class="fw-bold"><?= htmlspecialchars($intern['student_name']) ?> (<?=$intern['student_code']?>)</td></tr>
                <tr><td class="text-muted">ครูที่ปรึกษา:</td><td><?= htmlspecialchars($intern['advisor_name']) ?> (<?=$intern['advisor_phone']?>)</td></tr>
                <tr><td class="text-muted">พี่เลี้ยง:</td><td><?= htmlspecialchars($intern['supervisor_name']) ?> (<?=$intern['supervisor_phone']?>)</td></tr>
            </table>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-calendar-check me-1"></i> ระยะเวลา & ความคืบหน้า</h6>
            
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-muted">ความคืบหน้าสะสม</span>
                <span class="fw-bold text-primary"><?=$m['percentage']?>%</span>
            </div>
            <div class="progress mb-3" style="height: 12px; border-radius: 8px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?=$m['percentage']?>%;"></div>
            </div>

            <table class="table table-sm table-borderless align-middle mb-0">
                <tr><td class="text-muted" style="width:130px;">วันที่เริ่มฝึก:</td><td><?= formatThaiDate($intern['start_date']) ?></td></tr>
                <tr><td class="text-muted">วันที่สิ้นสุด:</td><td><?= formatThaiDate($intern['end_date']) ?></td></tr>
                <tr><td class="text-muted">ฝึกไปแล้ว:</td><td><strong class="text-primary"><?=$m['days_elapsed']?> วัน</strong> (เหลือ <?=$m['days_remaining']?> วัน)</td></tr>
                <tr><td class="text-muted">ชั่วโมงรวม:</td><td><?=$intern['total_hours']?> ชั่วโมง</td></tr>
                <tr><td class="text-muted">สถานะ:</td><td><?= getStatusBadgeHtml($intern['status']) ?></td></tr>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
