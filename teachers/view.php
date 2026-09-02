<?php
// ====================================================================
// Teacher Detailed View (teachers/view.php)
// ====================================================================

$pageTitle = 'รายละเอียดครูที่ปรึกษา';
$activePage = 'teachers_list';
$activeGroup = 'teachers';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT t.*, CONCAT(t.first_name, ' ', t.last_name) as full_name FROM teachers t WHERE t.id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลครูที่ปรึกษา</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Fetch assigned students
$sStmt = $db->prepare("SELECT s.*, c.company_name, i.position as intern_position 
                       FROM students s 
                       LEFT JOIN internships i ON s.id = i.student_id
                       LEFT JOIN companies c ON i.company_id = c.id
                       WHERE s.advisor_id = :tid 
                       ORDER BY s.student_code ASC");
$sStmt->execute([':tid' => $id]);
$assignedStudents = $sStmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-workspace text-primary me-2"></i> <?= htmlspecialchars($teacher['full_name']) ?> (<?=$teacher['teacher_code']?>)</h3>
        <p class="text-muted small m-0">อาจารย์ประจำแผนกวิชา <?= htmlspecialchars($teacher['department']) ?></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-card-list me-1"></i> ข้อมูลครูที่ปรึกษา</h6>
            <table class="table table-sm table-borderless align-middle mb-0">
                <tr><td class="text-muted" style="width:120px;">รหัสครู:</td><td class="fw-bold"><?=$teacher['teacher_code']?></td></tr>
                <tr><td class="text-muted">ชื่อ-นามสกุล:</td><td class="fw-semibold"><?= htmlspecialchars($teacher['full_name']) ?></td></tr>
                <tr><td class="text-muted">แผนกวิชา:</td><td><?= htmlspecialchars($teacher['department']) ?></td></tr>
                <tr><td class="text-muted">เบอร์โทรศัพท์:</td><td><?=$teacher['phone'] ?? '-'?></td></tr>
                <tr><td class="text-muted">อีเมล:</td><td><?=$teacher['email'] ?? '-'?></td></tr>
                <tr><td class="text-muted">นักศึกษาในดูแล:</td><td><span class="badge bg-primary rounded-pill"><?=count($assignedStudents)?> คน</span></td></tr>
            </table>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="p-3 bg-light border-bottom fw-bold text-slate-800">
                <i class="bi bi-people-fill text-primary me-1"></i> รายชื่อนักศึกษาในความดูแล (<?=count($assignedStudents)?> คน)
            </div>
            <div class="table-responsive">
                <table class="table custom-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ระดับชั้น</th>
                            <th>สถานประกอบการ</th>
                            <th>สถานะฝึกงาน</th>
                            <th class="text-end">ดูข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignedStudents)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">ไม่พบนักศึกษาในความดูแล</td></tr>
                        <?php else: ?>
                            <?php foreach ($assignedStudents as $st): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?=$st['student_code']?></td>
                                    <td><?= htmlspecialchars($st['first_name'].' '.$st['last_name']) ?></td>
                                    <td><?=$st['class_level']?> (<?=$st['room']?>)</td>
                                    <td class="small text-secondary"><?= htmlspecialchars($st['company_name'] ?? '-') ?></td>
                                    <td><?= getStatusBadgeHtml($st['internship_status']) ?></td>
                                    <td class="text-end">
                                        <a href="../students/view.php?id=<?=$st['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
