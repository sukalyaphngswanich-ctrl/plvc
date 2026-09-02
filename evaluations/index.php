<?php
// ====================================================================
// Evaluation Management System (evaluations/index.php)
// ====================================================================

$pageTitle = 'การประเมินผลการฝึกงาน';
$activePage = 'evaluations';
$activeGroup = 'evaluations';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

$search = trim($_GET['search'] ?? '');
$type   = trim($_GET['evaluator_type'] ?? '');

$where = ["1=1"];
$params = [];

if (hasRole('teacher')) {
    $tId = $currentUser['profile']['id'] ?? 0;
    $where[] = "s.advisor_id = :tid";
    $params[':tid'] = $tId;
} else if (hasRole('student')) {
    $sId = $currentUser['profile']['id'] ?? 0;
    $where[] = "ev.student_id = :sid";
    $params[':sid'] = $sId;
}

if (!empty($search)) {
    $where[] = "(s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_code LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($type)) {
    $where[] = "ev.evaluator_type = :etype";
    $params[':etype'] = $type;
}

$whereSql = implode(" AND ", $where);

$sql = "SELECT ev.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level,
               c.company_name
        FROM evaluations ev
        JOIN students s ON ev.student_id = s.id
        JOIN internships i ON ev.internship_id = i.id
        JOIN companies c ON i.company_id = c.id
        WHERE $whereSql
        ORDER BY ev.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$evaluations = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-star-fill text-warning me-2"></i> ผลการประเมินการฝึกงาน</h3>
        <p class="text-muted small m-0">ประเมินทักษะ 10 ด้านจากครูที่ปรึกษาและสถานประกอบการ</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportTableToCSV('evaluationsTable', 'evaluations-summary.csv')" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </button>
        <?php if (hasRole(['admin', 'teacher'])): ?>
            <a href="add.php" class="btn btn-warning text-white btn-sm"><i class="bi bi-plus-circle-fill me-1"></i> บันทึกการประเมินใหม่</a>
        <?php endif; ?>
    </div>
</div>

<!-- Search Filter -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา นักศึกษา, รหัส..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-4">
            <select name="evaluator_type" class="form-select form-select-sm">
                <option value="">-- ทุกผู้ประเมิน --</option>
                <option value="ครูที่ปรึกษา" <?= $type === 'ครูที่ปรึกษา' ? 'selected' : '' ?>>ครูที่ปรึกษา</option>
                <option value="สถานประกอบการ" <?= $type === 'สถานประกอบการ' ? 'selected' : '' ?>>สถานประกอบการ</option>
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
        <table class="table custom-table mb-0 align-middle" id="evaluationsTable">
            <thead>
                <tr>
                    <th>นักศึกษา</th>
                    <th>สถานประกอบการ</th>
                    <th>ผู้ประเมิน</th>
                    <th>คะแนนรวม (50)</th>
                    <th>คิดเป็น %</th>
                    <th>ผลการประเมิน</th>
                    <th class="text-end">รายละเอียด</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($evaluations)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">ไม่พบผลการประเมินการฝึกงาน</td></tr>
                <?php else: ?>
                    <?php foreach ($evaluations as $ev): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($ev['student_name']) ?></div>
                                <small class="text-muted"><?=$ev['student_code']?> (<?=$ev['class_level']?>)</small>
                            </td>
                            <td class="small text-secondary"><?= htmlspecialchars($ev['company_name']) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?=$ev['evaluator_type']?></span></td>
                            <td class="fw-bold fs-6"><?=$ev['total_score']?> / 50</td>
                            <td class="fw-bold text-success"><?=$ev['average_score']?>%</td>
                            <td>
                                <span class="badge bg-success border border-success px-3"><?=$ev['result']?></span>
                            </td>
                            <td class="text-end">
                                <a href="view.php?id=<?=$ev['id']?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i> ดูใบประเมิน</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
