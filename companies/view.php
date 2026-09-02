<?php
// ====================================================================
// Company Detailed View (companies/view.php)
// ====================================================================

$pageTitle = 'รายละเอียดสถานประกอบการ';
$activePage = 'companies_list';
$activeGroup = 'companies';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM companies WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$company = $stmt->fetch();

if (!$company) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลสถานประกอบการ</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Fetch students currently interning here
$iStmt = $db->prepare("SELECT s.*, i.position as intern_position, i.start_date, i.end_date, i.status as intern_status,
                              CONCAT(t.first_name, ' ', t.last_name) as advisor_name
                       FROM internships i
                       JOIN students s ON i.student_id = s.id
                       LEFT JOIN teachers t ON s.advisor_id = t.id
                       WHERE i.company_id = :cid ORDER BY s.student_code ASC");
$iStmt->execute([':cid' => $id]);
$interns = $iStmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-building-fill text-warning me-2"></i> <?= htmlspecialchars($company['company_name']) ?></h3>
        <p class="text-muted small m-0"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($company['address']) ?> (<?=$company['province']?>)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="https://www.google.com/maps/search/?api=1&query=<?=$company['latitude']?>,<?=$company['longitude']?>" target="_blank" class="btn btn-outline-info btn-sm">
            <i class="bi bi-geo-alt-fill me-1"></i> เปิด Google Maps
        </a>
        <?php if (hasRole(['admin', 'teacher'])): ?>
            <a href="edit.php?id=<?=$company['id']?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil-square"></i> แก้ไข</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> กลับ</a>
    </div>
</div>

<div class="row g-4">
    <!-- Company Details Card -->
    <div class="col-12 col-md-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-info-circle-fill me-1"></i> รายละเอียดองค์กร</h6>
            <table class="table table-sm table-borderless align-middle mb-0">
                <tr><td class="text-muted" style="width:130px;">ประเภทธุรกิจ:</td><td class="fw-semibold"><?= htmlspecialchars($company['business_type']) ?></td></tr>
                <tr><td class="text-muted">โทรศัพท์:</td><td><?=$company['phone'] ?? '-'?></td></tr>
                <tr><td class="text-muted">อีเมล:</td><td><?=$company['email'] ?? '-'?></td></tr>
                <tr><td class="text-muted">เว็บไซต์:</td><td><a href="<?=$company['website']?>" target="_blank"><?= htmlspecialchars($company['website'] ?? '-') ?></a></td></tr>
                <tr><td class="text-muted">พิกัด GPS:</td><td class="small"><?=$company['latitude']?>, <?=$company['longitude']?></td></tr>
            </table>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-person-lines-fill me-1"></i> ข้อมูลผู้ติดต่อ & พี่เลี้ยง</h6>
            <table class="table table-sm table-borderless align-middle mb-0">
                <tr><td class="text-muted" style="width:130px;">ผู้ติดต่อหลัก:</td><td class="fw-bold"><?= htmlspecialchars($company['contact_name'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">ตำแหน่ง:</td><td><?= htmlspecialchars($company['contact_position'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">เบอร์โทรศัพท์:</td><td><?=$company['contact_phone'] ?? '-'?></td></tr>
                <tr><td class="text-muted">อีเมล:</td><td><?=$company['contact_email'] ?? '-'?></td></tr>
            </table>
        </div>
    </div>

    <!-- Interns Roster Card -->
    <div class="col-12 col-md-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="p-3 bg-light border-bottom fw-bold text-slate-800">
                <i class="bi bi-mortarboard-fill text-primary me-1"></i> นักศึกษาที่ฝึกงาน ณ สถานประกอบการนี้ (<?=count($interns)?> คน)
            </div>
            <div class="table-responsive">
                <table class="table custom-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>นักศึกษา</th>
                            <th>ตำแหน่ง</th>
                            <th>ระยะเวลา</th>
                            <th>ครูที่ปรึกษา</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($interns)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีนักศึกษาฝึกงานที่บริษัทนี้</td></tr>
                        <?php else: ?>
                            <?php foreach ($interns as $st): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($st['first_name'].' '.$st['last_name']) ?></div>
                                        <small class="text-muted"><?=$st['student_code']?> (<?=$st['class_level']?>)</small>
                                    </td>
                                    <td class="fw-semibold text-primary small"><?= htmlspecialchars($st['intern_position']) ?></td>
                                    <td class="small text-muted"><?= formatThaiDate($st['start_date']) ?> - <?= formatThaiDate($st['end_date']) ?></td>
                                    <td class="small text-secondary"><?= htmlspecialchars($st['advisor_name'] ?? '-') ?></td>
                                    <td><?= getStatusBadgeHtml($st['intern_status']) ?></td>
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
