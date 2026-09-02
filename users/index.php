<?php
// ====================================================================
// User Management List (users/index.php)
// ====================================================================

$pageTitle = 'จัดการผู้ใช้งาน';
$activePage = 'users';
$activeGroup = 'settings';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin']);

$db = getDB();

// Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    if ($deleteId !== $currentUser['id']) {
        $db->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $deleteId]);
        redirectUrl("index.php?msg=deleted");
    }
}

// Filter
$filterRole = $_GET['role'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($filterRole) {
    $sql .= " AND role = :role";
    $params[':role'] = $filterRole;
}
if ($search) {
    $sql .= " AND (username LIKE :search OR email LIKE :search2)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$roleBadge = [
    'admin'   => 'bg-danger',
    'teacher' => 'bg-primary',
    'student' => 'bg-success',
    'company' => 'bg-info',
];
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-danger me-2"></i> จัดการผู้ใช้งานระบบ</h3>
        <p class="text-muted small m-0">เพิ่ม แก้ไข ลบ บัญชีผู้ใช้งานทุกระดับสิทธิ์</p>
    </div>
    <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-person-fill-add me-1"></i> เพิ่มผู้ใช้ใหม่</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php
    $msgs = ['added' => 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว', 'updated' => 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว', 'deleted' => 'ลบผู้ใช้เรียบร้อยแล้ว'];
    $m = $msgs[$_GET['msg']] ?? '';
    ?>
    <?php if ($m): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i> <?=$m?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endif; ?>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form action="index.php" method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label small text-secondary fw-medium">ค้นหา</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ชื่อผู้ใช้, อีเมล..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-secondary fw-medium">บทบาท</label>
            <select name="role" class="form-select form-select-sm">
                <option value="">-- ทั้งหมด --</option>
                <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>ผู้ดูแลระบบ</option>
                <option value="teacher" <?= $filterRole === 'teacher' ? 'selected' : '' ?>>ครูที่ปรึกษา</option>
                <option value="student" <?= $filterRole === 'student' ? 'selected' : '' ?>>นักศึกษา</option>
                <option value="company" <?= $filterRole === 'company' ? 'selected' : '' ?>>สถานประกอบการ</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i> กรอง</button>
        </div>
        <div class="col-12 col-md-3 text-end">
            <span class="badge bg-secondary"><?= count($users) ?> รายการ</span>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4 p-0 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px;">#</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>อีเมล</th>
                    <th class="text-center">บทบาท</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-center">วันที่สร้าง</th>
                    <th class="text-center" style="width:140px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลผู้ใช้</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $i => $u): ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td class="fw-bold"><i class="bi bi-person-circle me-1 text-secondary"></i> <?= htmlspecialchars($u['username']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                            <td class="text-center">
                                <?php 
                                $roleLabelMap = ['admin' => 'ผู้ดูแลระบบ', 'teacher' => 'ครูที่ปรึกษา', 'student' => 'นักศึกษา', 'company' => 'สถานประกอบการ'];
                                ?>
                                <span class="badge <?= $roleBadge[$u['role']] ?? 'bg-secondary' ?>"><?= $roleLabelMap[$u['role']] ?? $u['role'] ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>ใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle me-1"></i>ปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center small text-muted"><?= formatThaiDate($u['created_at']) ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?=$u['id']?>" class="btn btn-outline-info" title="ดู"><i class="bi bi-eye"></i></a>
                                    <a href="edit.php?id=<?=$u['id']?>" class="btn btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                                    <?php if ($u['id'] !== $currentUser['id']): ?>
                                        <button onclick="confirmDelete('index.php?action=delete&id=<?=$u['id']?>', 'ผู้ใช้ <?= htmlspecialchars($u['username']) ?>')" class="btn btn-outline-danger" title="ลบ"><i class="bi bi-trash"></i></button>
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
