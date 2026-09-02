<?php
// ====================================================================
// Teacher List Management (teachers/index.php)
// ====================================================================

$pageTitle = 'ข้อมูลครูที่ปรึกษา';
$activePage = 'teachers_list';
$activeGroup = 'teachers';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Handle Delete Request (Admin only)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    requireRole('admin');
    $deleteId = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT user_id FROM teachers WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $deleteId]);
    $t = $stmt->fetch();
    if ($t) {
        $db->prepare("DELETE FROM users WHERE id = :uid")->execute([':uid' => $t['user_id']]);
    }
    redirectUrl("index.php?msg=deleted");
}

$search = trim($_GET['search'] ?? '');
$dept   = trim($_GET['department'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(t.teacher_code LIKE :search OR t.first_name LIKE :search OR t.last_name LIKE :search OR t.phone LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($dept)) {
    $where[] = "t.department = :dept";
    $params[':dept'] = $dept;
}

$whereSql = implode(" AND ", $where);

$sql = "SELECT t.*, CONCAT(t.first_name, ' ', t.last_name) as teacher_name,
               COUNT(s.id) as assigned_students_count
        FROM teachers t
        LEFT JOIN students s ON t.id = s.advisor_id
        WHERE $whereSql
        GROUP BY t.id
        ORDER BY t.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$teachers = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-workspace text-primary me-2"></i> ข้อมูลครูที่ปรึกษา</h3>
        <p class="text-muted small m-0">จัดการข้อมูลครูที่ปรึกษา แผนกวิชา และจำนวนนักศึกษาในความดูแล</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportTableToCSV('teachersTable', 'teachers-list.csv')" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </button>
        <?php if (hasRole('admin')): ?>
            <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus-fill me-1"></i> เพิ่มครูใหม่</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> ลบข้อมูลครูเรียบร้อยแล้ว
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filter & Search -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา รหัสครู, ชื่อ-นามสกุล..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-4">
            <input type="text" name="department" class="form-control form-control-sm" placeholder="สาขาวิชา/แผนก..." value="<?= htmlspecialchars($dept) ?>">
        </div>
        <div class="col-6 col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i> ค้นหา</button>
            <a href="index.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Teachers Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle" id="teachersTable">
            <thead>
                <tr>
                    <th>รหัสครู</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>แผนกวิชา</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>อีเมล</th>
                    <th>นักศึกษาในดูแล</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">ไม่พบข้อมูลครูที่ปรึกษา</td></tr>
                <?php else: ?>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?=$t['teacher_code']?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($t['teacher_name']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($t['department']) ?></span></td>
                            <td><?=$t['phone'] ?? '-'?></td>
                            <td class="small text-secondary"><?=$t['email'] ?? '-'?></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?=$t['assigned_students_count']?> คน</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?=$t['id']?>" class="btn btn-outline-primary" title="ดูรายละเอียด"><i class="bi bi-eye-fill"></i></a>
                                    <?php if (hasRole('admin')): ?>
                                        <a href="edit.php?id=<?=$t['id']?>" class="btn btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                        <button onclick="confirmDelete('index.php?action=delete&id=<?=$t['id']?>', 'ครู <?= htmlspecialchars($t['teacher_name']) ?>')" class="btn btn-outline-danger" title="ลบ"><i class="bi bi-trash-fill"></i></button>
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
