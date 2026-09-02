<?php
// ====================================================================
// Supervision Management System (supervision/index.php)
// ====================================================================

$pageTitle = 'นิเทศการฝึกงาน';
$activePage = 'supervision';
$activeGroup = 'supervision';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Delete Action (Admin only)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    requireRole('admin');
    $deleteId = (int)$_GET['id'];
    $db->prepare("DELETE FROM supervision WHERE id = :id")->execute([':id' => $deleteId]);
    redirectUrl("index.php?msg=deleted");
}

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$type   = trim($_GET['visit_type'] ?? '');

$where = ["1=1"];
$params = [];

if (hasRole('teacher')) {
    $tId = $currentUser['profile']['id'] ?? 0;
    $where[] = "sp.teacher_id = :tid";
    $params[':tid'] = $tId;
} else if (hasRole('student')) {
    $sId = $currentUser['profile']['id'] ?? 0;
    $where[] = "sp.student_id = :sid";
    $params[':sid'] = $sId;
}

if (!empty($search)) {
    $where[] = "(s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_code LIKE :search OR c.company_name LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($status)) {
    $where[] = "sp.status = :st";
    $params[':st'] = $status;
}
if (!empty($type)) {
    $where[] = "sp.visit_type = :vt";
    $params[':vt'] = $type;
}

$whereSql = implode(" AND ", $where);

$sql = "SELECT sp.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level,
               c.company_name, CONCAT(t.first_name, ' ', t.last_name) as teacher_name
        FROM supervision sp
        JOIN students s ON sp.student_id = s.id
        JOIN companies c ON sp.company_id = c.id
        JOIN teachers t ON sp.teacher_id = t.id
        WHERE $whereSql
        ORDER BY sp.visit_date DESC, sp.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$supervisions = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-car-front-fill text-info me-2"></i> บันทึกและติดตามนิเทศการฝึกงาน</h3>
        <p class="text-muted small m-0">จัดการนัดหมาย ผลการนิเทศติดตาม และข้อเสนอแนะจากการนิเทศ</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportTableToCSV('supervisionTable', 'supervision-records.csv')" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </button>
        <?php if (hasRole(['admin', 'teacher'])): ?>
            <a href="add.php" class="btn btn-info text-white btn-sm"><i class="bi bi-plus-circle-fill me-1"></i> บันทึกการนิเทศใหม่</a>
        <?php endif; ?>
    </div>
</div>

<!-- Search Filter -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา นักศึกษา, บริษัท..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-3">
            <select name="visit_type" class="form-select form-select-sm">
                <option value="">-- ทุกรูปแบบนิเทศ --</option>
                <option value="นิเทศที่สถานประกอบการ" <?= $type === 'นิเทศที่สถานประกอบการ' ? 'selected' : '' ?>>นิเทศที่สถานประกอบการ</option>
                <option value="นิเทศออนไลน์" <?= $type === 'นิเทศออนไลน์' ? 'selected' : '' ?>>นิเทศออนไลน์</option>
                <option value="โทรศัพท์" <?= $type === 'โทรศัพท์' ? 'selected' : '' ?>>โทรศัพท์</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">-- ทุกสถานะ --</option>
                <option value="ยังไม่นิเทศ" <?= $status === 'ยังไม่นิเทศ' ? 'selected' : '' ?>>ยังไม่นิเทศ</option>
                <option value="นัดหมายแล้ว" <?= $status === 'นัดหมายแล้ว' ? 'selected' : '' ?>>นัดหมายแล้ว</option>
                <option value="นิเทศแล้ว" <?= $status === 'นิเทศแล้ว' ? 'selected' : '' ?>>นิเทศแล้ว</option>
                <option value="ต้องติดตามเพิ่มเติม" <?= $status === 'ต้องติดตามเพิ่มเติม' ? 'selected' : '' ?>>ต้องติดตามเพิ่มเติม</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i> ค้นหา</button>
            <a href="index.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle" id="supervisionTable">
            <thead>
                <tr>
                    <th>วันที่ / เวลา</th>
                    <th>นักศึกษา</th>
                    <th>สถานประกอบการ</th>
                    <th>รูปแบบนิเทศ</th>
                    <th>ครูผู้นิเทศ</th>
                    <th>สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($supervisions)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">ไม่พบข้อมูลบันทึกการนิเทศ</td></tr>
                <?php else: ?>
                    <?php foreach ($supervisions as $sp): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= formatThaiDate($sp['visit_date']) ?></div>
                                <small class="text-muted"><i class="bi bi-clock"></i> <?=$sp['visit_time']?> น.</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($sp['student_name']) ?></div>
                                <small class="text-muted"><?=$sp['student_code']?> (<?=$sp['class_level']?>)</small>
                            </td>
                            <td class="small text-secondary"><?= htmlspecialchars($sp['company_name']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?=$sp['visit_type']?></span></td>
                            <td class="small text-secondary"><?= htmlspecialchars($sp['teacher_name']) ?></td>
                            <td><?= getStatusBadgeHtml($sp['status']) ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?=$sp['id']?>" class="btn btn-outline-primary" title="ดูผลนิเทศ"><i class="bi bi-eye"></i></a>
                                    <?php if (hasRole(['admin', 'teacher'])): ?>
                                        <a href="edit.php?id=<?=$sp['id']?>" class="btn btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasRole('admin')): ?>
                                        <button onclick="confirmDelete('index.php?action=delete&id=<?=$sp['id']?>', 'บันทึกนิเทศของ <?= htmlspecialchars($sp['student_name']) ?>')" class="btn btn-outline-danger" title="ลบ"><i class="bi bi-trash-fill"></i></button>
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
