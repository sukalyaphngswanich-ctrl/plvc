<?php
// ====================================================================
// Internship List & Progress Management (internships/index.php)
// ====================================================================

$pageTitle = 'ข้อมูลการฝึกงาน';
$activePage = 'internships_list';
$activeGroup = 'internships';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Delete Action (Admin only)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    requireRole('admin');
    $deleteId = (int)$_GET['id'];
    $db->prepare("DELETE FROM internships WHERE id = :id")->execute([':id' => $deleteId]);
    redirectUrl("index.php?msg=deleted");
}

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');

$where = ["1=1"];
$params = [];

if (hasRole('teacher')) {
    $tId = $currentUser['profile']['id'] ?? 0;
    $where[] = "i.advisor_id = :tid";
    $params[':tid'] = $tId;
} else if (hasRole('student')) {
    $sId = $currentUser['profile']['id'] ?? 0;
    $where[] = "i.student_id = :sid";
    $params[':sid'] = $sId;
}

if (!empty($search)) {
    $where[] = "(s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_code LIKE :search OR c.company_name LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($status)) {
    $where[] = "i.status = :st";
    $params[':st'] = $status;
}

$whereSql = implode(" AND ", $where);

$sql = "SELECT i.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level, s.room,
               c.company_name, CONCAT(t.first_name, ' ', t.last_name) as advisor_name
        FROM internships i
        JOIN students s ON i.student_id = s.id
        JOIN companies c ON i.company_id = c.id
        LEFT JOIN teachers t ON i.advisor_id = t.id
        WHERE $whereSql
        ORDER BY i.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$internships = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-clipboard-check-fill text-success me-2"></i> รายการข้อมูลการฝึกงาน</h3>
        <p class="text-muted small m-0">ติดตามระยะเวลาฝึกงาน คำนวณความคืบหน้าอัตโนมัติ และสถานะปัจจุบัน</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportTableToCSV('internshipsTable', 'internships-list.csv')" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </button>
        <?php if (hasRole(['admin', 'teacher', 'student'])): ?>
            <a href="add.php" class="btn btn-success btn-sm"><i class="bi bi-plus-circle-fill me-1"></i> ลงทะเบียนฝึกงาน</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i> ลบข้อมูลการฝึกงานเรียบร้อยแล้ว
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search Filter -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา นักศึกษา, รหัส, บริษัท..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-4">
            <select name="status" class="form-select form-select-sm">
                <option value="">-- ทุกสถานะ --</option>
                <option value="รอจัดสถานที่" <?= $status === 'รอจัดสถานที่' ? 'selected' : '' ?>>รอจัดสถานที่</option>
                <option value="รอยืนยัน" <?= $status === 'รอยืนยัน' ? 'selected' : '' ?>>รอยืนยัน</option>
                <option value="พร้อมฝึกงาน" <?= $status === 'พร้อมฝึกงาน' ? 'selected' : '' ?>>พร้อมฝึกงาน</option>
                <option value="กำลังฝึกงาน" <?= $status === 'กำลังฝึกงาน' ? 'selected' : '' ?>>กำลังฝึกงาน</option>
                <option value="ฝึกงานเสร็จแล้ว" <?= $status === 'ฝึกงานเสร็จแล้ว' ? 'selected' : '' ?>>ฝึกงานเสร็จแล้ว</option>
                <option value="มีปัญหา" <?= $status === 'มีปัญหา' ? 'selected' : '' ?>>มีปัญหา</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i> ค้นหา</button>
            <a href="index.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle" id="internshipsTable">
            <thead>
                <tr>
                    <th>นักศึกษา</th>
                    <th>สถานประกอบการ / ตำแหน่ง</th>
                    <th>ระยะเวลา</th>
                    <th>ความคืบหน้า (%)</th>
                    <th>สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($internships)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">ไม่พบข้อมูลรายการฝึกงาน</td></tr>
                <?php else: ?>
                    <?php foreach ($internships as $item): ?>
                        <?php $m = calculateInternshipMetrics($item['start_date'], $item['end_date']); ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($item['student_name']) ?></div>
                                <small class="text-muted"><?=$item['student_code']?> (<?=$item['class_level']?>)</small>
                            </td>
                            <td>
                                <div class="fw-bold text-slate-800"><?= htmlspecialchars($item['company_name']) ?></div>
                                <small class="text-primary fw-medium"><?= htmlspecialchars($item['position']) ?></small>
                            </td>
                            <td class="small text-secondary">
                                <?= formatThaiDate($item['start_date']) ?> - <?= formatThaiDate($item['end_date']) ?><br>
                                <span class="badge bg-light text-muted border">ฝึกไปแล้ว <?=$m['days_elapsed']?> / <?=$m['total_days']?> วัน</span>
                            </td>
                            <td style="min-width: 150px;">
                                <div class="d-flex justify-content-between align-items-center mb-1 small fw-bold">
                                    <span><?=$m['percentage']?>%</span>
                                    <span class="text-muted" style="font-size:0.75rem;">เหลือ <?=$m['days_remaining']?> วัน</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?=$m['percentage']?>%;"></div>
                                </div>
                            </td>
                            <td><?= getStatusBadgeHtml($item['status']) ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?=$item['id']?>" class="btn btn-outline-primary" title="ดู Timeline และรายละเอียด"><i class="bi bi-clock-history"></i></a>
                                    <?php if (hasRole(['admin', 'teacher'])): ?>
                                        <a href="edit.php?id=<?=$item['id']?>" class="btn btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasRole('admin')): ?>
                                        <button onclick="confirmDelete('index.php?action=delete&id=<?=$item['id']?>', 'รายการฝึกงานของ <?= htmlspecialchars($item['student_name']) ?>')" class="btn btn-outline-danger" title="ลบ"><i class="bi bi-trash-fill"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
