<?php
// ====================================================================
// Company List Management (companies/index.php)
// ====================================================================

$pageTitle = 'ข้อมูลสถานประกอบการ';
$activePage = 'companies_list';
$activeGroup = 'companies';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Delete action (Admin only)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    requireRole('admin');
    $deleteId = (int)$_GET['id'];
    $db->prepare("DELETE FROM companies WHERE id = :id")->execute([':id' => $deleteId]);
    redirectUrl("index.php?msg=deleted");
}

$search   = trim($_GET['search'] ?? '');
$province = trim($_GET['province'] ?? '');
$btype    = trim($_GET['business_type'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(c.company_name LIKE :search OR c.contact_name LIKE :search OR c.address LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($province)) {
    $where[] = "c.province = :prov";
    $params[':prov'] = $province;
}
if (!empty($btype)) {
    $where[] = "c.business_type LIKE :btype";
    $params[':btype'] = "%$btype%";
}

$whereSql = implode(" AND ", $where);

$sql = "SELECT c.*, COUNT(i.id) as intern_count
        FROM companies c
        LEFT JOIN internships i ON c.id = i.company_id
        WHERE $whereSql
        GROUP BY c.id
        ORDER BY c.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$companies = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-building-fill text-warning me-2"></i> ข้อมูลสถานประกอบการ</h3>
        <p class="text-muted small m-0">จัดการรายชื่อบริษัท สถานประกอบการพันธมิตร และตำแหน่งที่ตั้ง</p>
    </div>
    <div class="d-flex gap-2">
        <a href="map.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-map-fill me-1"></i> ดูบนแผนที่ (Map)</a>
        <button onclick="exportTableToCSV('companiesTable', 'companies-list.csv')" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </button>
        <?php if (hasRole(['admin', 'teacher', 'student'])): ?>
            <a href="add.php" class="btn btn-warning btn-sm text-white"><i class="bi bi-plus-circle-fill me-1"></i> เพิ่มสถานประกอบการ</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i> ลบข้อมูลสถานประกอบการเรียบร้อยแล้ว
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search Filter -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา ชื่อบริษัท, ผู้ติดต่อ, ที่อยู่..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-3">
            <input type="text" name="province" class="form-control form-control-sm" placeholder="จังหวัด..." value="<?= htmlspecialchars($province) ?>">
        </div>
        <div class="col-6 col-md-4 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i> ค้นหา</button>
            <a href="index.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle" id="companiesTable">
            <thead>
                <tr>
                    <th>ชื่อสถานประกอบการ</th>
                    <th>ประเภทธุรกิจ</th>
                    <th>จังหวัด / ที่อยู่</th>
                    <th>ผู้ติดต่อ / เบอร์โทร</th>
                    <th>นักศึกษาฝึกงาน</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($companies)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">ไม่พบข้อมูลสถานประกอบการ</td></tr>
                <?php else: ?>
                    <?php foreach ($companies as $c): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($c['company_name']) ?></div>
                                <small class="text-muted"><i class="bi bi-telephone"></i> <?=$c['phone'] ?? '-'?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['business_type']) ?></span></td>
                            <td>
                                <div class="fw-medium text-slate-800 small"><?= htmlspecialchars($c['province']) ?></div>
                                <small class="text-muted text-truncate d-inline-block" style="max-width:200px;"><?= htmlspecialchars($c['address']) ?></small>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($c['contact_name'] ?? '-') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($c['contact_phone'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3"><?=$c['intern_count']?> คน</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?=$c['id']?>" class="btn btn-outline-primary" title="ดูรายละเอียด"><i class="bi bi-eye-fill"></i></a>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?=$c['latitude']?>,<?=$c['longitude']?>" target="_blank" class="btn btn-outline-info" title="เปิด Google Maps"><i class="bi bi-geo-alt-fill"></i></a>
                                    <?php if (hasRole(['admin', 'teacher'])): ?>
                                        <a href="edit.php?id=<?=$c['id']?>" class="btn btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasRole('admin')): ?>
                                        <button onclick="confirmDelete('index.php?action=delete&id=<?=$c['id']?>', 'บริษัท <?= htmlspecialchars($c['company_name']) ?>')" class="btn btn-outline-danger" title="ลบ"><i class="bi bi-trash-fill"></i></button>
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
