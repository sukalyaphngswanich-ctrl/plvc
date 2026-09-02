<?php
// ====================================================================
// Evaluation Detailed View (evaluations/view.php)
// ====================================================================

$pageTitle = 'ใบบันทึกผลการประเมินการฝึกงาน';
$activePage = 'evaluations';
$activeGroup = 'evaluations';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT ev.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level, s.department,
               c.company_name
        FROM evaluations ev
        JOIN students s ON ev.student_id = s.id
        JOIN internships i ON ev.internship_id = i.id
        JOIN companies c ON i.company_id = c.id
        WHERE ev.id = :id LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$ev = $stmt->fetch();

if (!$ev) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบบันทึกการประเมินนี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i> ใบบันทึกผลการประเมินการฝึกงาน</h3>
        <p class="text-muted small m-0">นักศึกษา: <?= htmlspecialchars($ev['student_name']) ?> (<?=$ev['student_code']?>)</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="printReport()" class="btn btn-outline-primary btn-sm"><i class="bi bi-printer me-1"></i> พิมพ์ใบประเมิน</button>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="row g-3 mb-4 border-bottom pb-3">
        <div class="col-12 col-md-6">
            <strong>ชื่อ-นามสกุล นักศึกษา:</strong> <?= htmlspecialchars($ev['student_name']) ?><br>
            <strong>รหัสนักศึกษา:</strong> <?=$ev['student_code']?> (<?=$ev['class_level']?>)<br>
            <strong>สาขาวิชา:</strong> <?=$ev['department']?>
        </div>
        <div class="col-12 col-md-6 text-md-end">
            <strong>สถานประกอบการ:</strong> <?= htmlspecialchars($ev['company_name']) ?><br>
            <strong>ผู้ประเมิน:</strong> <span class="badge bg-primary"><?=$ev['evaluator_type']?></span><br>
            <strong>วันที่ประเมิน:</strong> <?= formatThaiDate($ev['created_at']) ?>
        </div>
    </div>

    <!-- 10 Competencies Table -->
    <h6 class="fw-bold text-primary mb-3">สรุปคะแนนประเมินรายข้อ (10 ด้าน)</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>หัวข้อเกณฑ์การประเมิน</th>
                    <th class="text-center" style="width:140px;">คะแนนที่ได้ (5)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>1. ความรับผิดชอบต่อหน้าที่และงานที่ได้รับมอบหมาย</td><td class="text-center fw-bold"><?=$ev['responsibility_score']?></td></tr>
                <tr><td>2. การตรงต่อเวลา (การเข้า-ออกงานและการส่งงาน)</td><td class="text-center fw-bold"><?=$ev['punctuality_score']?></td></tr>
                <tr><td>3. ความขยัน อดทน และเอาใจใส่ในการทำงาน</td><td class="text-center fw-bold"><?=$ev['hardworking_score']?></td></tr>
                <tr><td>4. การทำงานร่วมกับผู้อื่น การปรับตัว และมนุษยสัมพันธ์</td><td class="text-center fw-bold"><?=$ev['teamwork_score']?></td></tr>
                <tr><td>5. ทักษะการสื่อสารและการนำเสนองาน</td><td class="text-center fw-bold"><?=$ev['communication_score']?></td></tr>
                <tr><td>6. ความคิดสร้างสรรค์และการพัฒนางาน</td><td class="text-center fw-bold"><?=$ev['creativity_score']?></td></tr>
                <tr><td>7. ทักษะฝีมือทางวิชาชีพและเทคโนโลยี</td><td class="text-center fw-bold"><?=$ev['professional_skill_score']?></td></tr>
                <tr><td>8. การแก้ไขปัญหาเฉพาะหน้าและการตัดสินใจ</td><td class="text-center fw-bold"><?=$ev['problem_solving_score']?></td></tr>
                <tr><td>9. มารยาท สัมมาคารวะ และสัมพันธภาพในองค์กร</td><td class="text-center fw-bold"><?=$ev['etiquette_score']?></td></tr>
                <tr><td>10. การปฏิบัติตามกฎระเบียบและนโยบายความปลอดภัย</td><td class="text-center fw-bold"><?=$ev['discipline_score']?></td></tr>
            </tbody>
        </table>
    </div>

    <!-- Result Banner -->
    <div class="p-4 bg-light rounded-4 text-center mb-4">
        <div class="row align-items-center">
            <div class="col-4">
                <div class="small text-muted">คะแนนรวม</div>
                <div class="fs-2 fw-bold text-primary"><?=$ev['total_score']?> / 50</div>
            </div>
            <div class="col-4 border-start border-end">
                <div class="small text-muted">คิดเป็นเปอร์เซ็นต์</div>
                <div class="fs-2 fw-bold text-success"><?=$ev['average_score']?>%</div>
            </div>
            <div class="col-4">
                <div class="small text-muted">ผลสรุประดับ</div>
                <div class="fs-2 fw-bold text-dark"><?=$ev['result']?></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="p-3 border rounded-3 bg-light">
                <strong class="text-success">จุดเด่น:</strong>
                <div class="small mt-1"><?= htmlspecialchars($ev['strength'] ?? '-') ?></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 border rounded-3 bg-light">
                <strong class="text-warning">จุดที่ควรปรับปรุง:</strong>
                <div class="small mt-1"><?= htmlspecialchars($ev['improvement'] ?? '-') ?></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 border rounded-3 bg-light">
                <strong class="text-primary">ข้อเสนอแนะ:</strong>
                <div class="small mt-1"><?= htmlspecialchars($ev['suggestion'] ?? '-') ?></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
