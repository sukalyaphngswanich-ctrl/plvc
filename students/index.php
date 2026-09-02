<?php
// ====================================================================
// Student List Management (students/index.php)
// ====================================================================

$pageTitle = 'รายชื่อนักศึกษา';
$activePage = 'students_list';
$activeGroup = 'students';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    requireRole('admin');
    $deleteId = (int)$_GET['id'];
    
    // Find user_id first
    $stmt = $db->prepare("SELECT user_id FROM students WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $deleteId]);
    $st = $stmt->fetch();
    if ($st) {
        $db->prepare("DELETE FROM users WHERE id = :uid")->execute([':uid' => $st['user_id']]);
    }
    redirectUrl("index.php?msg=deleted");
}

// Search & Filter Parameters
$search    = trim($_GET['search'] ?? '');
$class     = trim($_GET['class_level'] ?? '');
$room      = trim($_GET['room'] ?? '');
$dept      = trim($_GET['department'] ?? '');
$advisor   = trim($_GET['advisor_id'] ?? '');
$status    = trim($_GET['status'] ?? '');

$whereClauses = ["1=1"];
$params = [];

if (hasRole('student')) {
    $sId = $currentUser['profile']['id'] ?? 0;
    $whereClauses[] = "s.id = :current_student_id";
    $params[':current_student_id'] = $sId;
}

if (!empty($search)) {
    $whereClauses[] = "(s.student_code LIKE :search OR s.first_name LIKE :search OR s.last_name LIKE :search OR s.phone LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($class)) {
    $whereClauses[] = "s.class_level = :class";
    $params[':class'] = $class;
}
if (!empty($room)) {
    $whereClauses[] = "s.room = :room";
    $params[':room'] = $room;
}
if (!empty($dept)) {
    $whereClauses[] = "s.department = :dept";
    $params[':dept'] = $dept;
}
if (!empty($advisor)) {
    $whereClauses[] = "s.advisor_id = :adv";
    $params[':adv'] = $advisor;
}
if (!empty($status)) {
    $whereClauses[] = "s.internship_status = :status";
    $params[':status'] = $status;
}

$whereSql = implode(" AND ", $whereClauses);

// Fetch Students
$sql = "SELECT s.*, CONCAT(s.first_name, ' ', s.last_name) as student_name,
               CONCAT(t.first_name, ' ', t.last_name) as advisor_name,
               c.company_name, i.position as intern_position
        FROM students s
        LEFT JOIN teachers t ON s.advisor_id = t.id
        LEFT JOIN internships i ON s.id = i.student_id
        LEFT JOIN companies c ON i.company_id = c.id
        WHERE $whereSql
        ORDER BY s.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Fetch Distinct Lists for Filter dropdowns
$deptList = $db->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != '' ORDER BY department ASC")->fetchAll(PDO::FETCH_COLUMN);
$roomList = $db->query("SELECT DISTINCT room FROM students WHERE room IS NOT NULL AND room != '' ORDER BY room ASC")->fetchAll(PDO::FETCH_COLUMN);
$teachersList = $db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM teachers ORDER BY first_name ASC")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-badge-fill text-primary me-2"></i> ข้อมูลนักศึกษา</h3>
        <p class="text-muted small m-0">จัดการรายชื่อนักศึกษา ประวัติการฝึกงาน และสถานะติดตาม</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportTableToCSV('studentsTable', 'students-list.csv')" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </button>
        <?php if (hasRole(['admin', 'teacher'])): ?>
            <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus-fill me-1"></i> เพิ่มนักศึกษาใหม่</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> ลบข้อมูลนักศึกษาเรียบร้อยแล้ว
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Filter & Search Card -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="index.php" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
            <label class="form-label small text-secondary fw-medium">ค้นหา</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา รหัส, ชื่อ-นามสกุล..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-secondary fw-medium">สาขาวิชา</label>
            <select name="department" class="form-select form-select-sm">
                <option value="">-- ทุกสาขาวิชา --</option>
                <?php foreach ($deptList as $d): ?>
                    <option value="<?= htmlspecialchars($d) ?>" <?= $dept === $d ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-secondary fw-medium">ห้อง / กลุ่มเรียน</label>
            <select name="room" class="form-select form-select-sm">
                <option value="">-- ทุกห้อง --</option>
                <?php foreach ($roomList as $rm): ?>
                    <option value="<?= htmlspecialchars($rm) ?>" <?= $room === $rm ? 'selected' : '' ?>><?= htmlspecialchars($rm) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-secondary fw-medium">สถานะฝึกงาน</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">-- ทุกสถานะ --</option>
                <option value="ยังไม่เริ่มฝึก" <?= $status === 'ยังไม่เริ่มฝึก' ? 'selected' : '' ?>>ยังไม่เริ่มฝึก</option>
                <option value="กำลังฝึกงาน" <?= $status === 'กำลังฝึกงาน' ? 'selected' : '' ?>>กำลังฝึกงาน</option>
                <option value="ฝึกงานเสร็จแล้ว" <?= $status === 'ฝึกงานเสร็จแล้ว' ? 'selected' : '' ?>>ฝึกงานเสร็จแล้ว</option>
                <option value="มีปัญหา" <?= $status === 'มีปัญหา' ? 'selected' : '' ?>>มีปัญหา</option>
            </select>
        </div>
        <?php if (hasRole(['admin', 'teacher'])): ?>
            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-medium">ครูที่ปรึกษา</label>
                <select name="advisor_id" class="form-select form-select-sm">
                    <option value="">-- ทุกครูที่ปรึกษา --</option>
                    <?php foreach ($teachersList as $t): ?>
                        <option value="<?=$t['id']?>" <?= $advisor == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i></button>
            <a href="index.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle" id="studentsTable">
            <thead>
                <tr>
                    <th>รหัสนักศึกษา</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>ระดับชั้น / ห้อง</th>
                    <th>สาขาวิชา</th>
                    <th>สถานประกอบการ</th>
                    <th>ครูที่ปรึกษา</th>
                    <th>สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">ไม่พบข้อมูลนักศึกษาที่ค้นหา</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?=$s['student_code']?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['student_name']) ?></div>
                                <small class="text-muted"><i class="bi bi-telephone"></i> <?=$s['phone'] ?? '-'?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?=$s['class_level']?> (<?=$s['room']?>)</span>
                            </td>
                            <td><small class="fw-medium text-secondary"><?= htmlspecialchars($s['department']) ?></small></td>
                            <td>
                                <?php if (!empty($s['company_name'])): ?>
                                    <div class="fw-semibold text-slate-800 small"><?= htmlspecialchars($s['company_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($s['intern_position'] ?? '') ?></small>
                                <?php else: ?>
                                    <a href="../internships/add.php?student_id=<?=$s['id']?>" class="badge bg-warning-subtle text-warning-emphasis border border-warning text-decoration-none py-1 px-2" title="คลิกเพื่อจัดสถานที่ฝึกงาน">
                                        <i class="bi bi-plus-circle me-1"></i>ยังไม่ระบุบริษัท
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-secondary"><?= htmlspecialchars($s['advisor_name'] ?? '-') ?></small></td>
                            <td><?= getStatusBadgeHtml($s['internship_status']) ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?=$s['id']?>" class="btn btn-outline-primary" title="ดูรายละเอียด"><i class="bi bi-eye-fill"></i></a>
                                    <?php if (empty($s['company_name']) && hasRole(['admin', 'teacher'])): ?>
                                        <a href="../internships/add.php?student_id=<?=$s['id']?>" class="btn btn-outline-success" title="จัดสถานที่ฝึกงานให้นักศึกษา"><i class="bi bi-building-add"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasRole(['admin', 'teacher'])): ?>
                                        <a href="edit.php?id=<?=$s['id']?>" class="btn btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasRole('admin')): ?>
                                        <button onclick="confirmDelete('index.php?action=delete&id=<?=$s['id']?>', 'นักศึกษา <?= htmlspecialchars($s['student_name']) ?>')" class="btn btn-outline-danger" title="ลบ"><i class="bi bi-trash-fill"></i></button>
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
