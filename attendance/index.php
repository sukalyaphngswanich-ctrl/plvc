<?php
// ====================================================================
// Attendance Management System (attendance/index.php)
// ====================================================================

$pageTitle = 'การเข้า-ออกฝึกงาน (Attendance)';
$activePage = 'attendance';
$activeGroup = 'logs';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$date   = trim($_GET['date'] ?? '');

$where = ["1=1"];
$params = [];

if (hasRole('teacher')) {
    $tId = $currentUser['profile']['id'] ?? 0;
    $where[] = "s.advisor_id = :tid";
    $params[':tid'] = $tId;
} else if (hasRole('student')) {
    $sId = $currentUser['profile']['id'] ?? 0;
    $where[] = "a.student_id = :sid";
    $params[':sid'] = $sId;
}

if (!empty($search)) {
    $where[] = "(s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_code LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($status)) {
    $where[] = "a.status = :st";
    $params[':st'] = $status;
}
if (!empty($date)) {
    $where[] = "a.attendance_date = :adate";
    $params[':adate'] = $date;
}

$whereSql = implode(" AND ", $where);

// Fetch Summary Stats
$summarySql = "SELECT a.status, COUNT(*) as cnt, SUM(a.total_hours) as total_h
               FROM attendance a
               JOIN students s ON a.student_id = s.id
               WHERE $whereSql GROUP BY a.status";
$sStmt = $db->prepare($summarySql);
$sStmt->execute($params);
$rawSummary = $sStmt->fetchAll();

$sumMap = ['ปกติ' => 0, 'มาสาย' => 0, 'ขาด' => 0, 'ลา' => 0, 'ออกก่อนเวลา' => 0];
$totalLoggedHours = 0;
foreach ($rawSummary as $row) {
    if (isset($sumMap[$row['status']])) {
        $sumMap[$row['status']] = (int)$row['cnt'];
    }
    $totalLoggedHours += (float)$row['total_h'];
}

// Fetch Attendance List
$sql = "SELECT a.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level,
               c.company_name
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        JOIN internships i ON a.internship_id = i.id
        JOIN companies c ON i.company_id = c.id
        WHERE $whereSql
        ORDER BY a.attendance_date DESC, a.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$attendanceRecords = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i> สรุปการเข้า-ออกฝึกงาน</h3>
        <p class="text-muted small m-0">ตรวจสอบประวัติเวลาเข้า-ออก การมาสาย ขาด และสะสมชั่วโมงฝึกงาน</p>
    </div>
    <div>
        <button onclick="exportTableToCSV('attendanceTable', 'attendance-summary.csv')" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> ส่งออกข้อมูล Excel
        </button>
    </div>
</div>

<!-- Summary Counters Bar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="p-3 bg-white border-start border-4 border-success rounded-3 shadow-sm text-center">
            <div class="small text-muted">มาปกติ</div>
            <h4 class="fw-bold text-success mb-0"><?=$sumMap['ปกติ']?> <span class="fs-6 font-normal">วัน</span></h4>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="p-3 bg-white border-start border-4 border-warning rounded-3 shadow-sm text-center">
            <div class="small text-muted">มาสาย</div>
            <h4 class="fw-bold text-warning mb-0"><?=$sumMap['มาสาย']?> <span class="fs-6 font-normal">ครั้ง</span></h4>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="p-3 bg-white border-start border-4 border-danger rounded-3 shadow-sm text-center">
            <div class="small text-muted">ขาดฝึกงาน</div>
            <h4 class="fw-bold text-danger mb-0"><?=$sumMap['ขาด']?> <span class="fs-6 font-normal">ครั้ง</span></h4>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="p-3 bg-white border-start border-4 border-secondary rounded-3 shadow-sm text-center">
            <div class="small text-muted">ลางาน</div>
            <h4 class="fw-bold text-secondary mb-0"><?=$sumMap['ลา']?> <span class="fs-6 font-normal">ครั้ง</span></h4>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="p-3 bg-white border-start border-4 border-primary rounded-3 shadow-sm text-center">
            <div class="small text-muted">ชั่วโมงสะสมรวมทั้งหมด</div>
            <h4 class="fw-bold text-primary mb-0"><?=$totalLoggedHours?> <span class="fs-6 font-normal">ชั่วโมง</span></h4>
        </div>
    </div>
</div>

<!-- Search Filter -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา รหัสนักศึกษา, ชื่อ-นามสกุล..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">-- ทุกสถานะ --</option>
                <option value="ปกติ" <?= $status === 'ปกติ' ? 'selected' : '' ?>>ปกติ</option>
                <option value="มาสาย" <?= $status === 'มาสาย' ? 'selected' : '' ?>>มาสาย</option>
                <option value="ขาด" <?= $status === 'ขาด' ? 'selected' : '' ?>>ขาด</option>
                <option value="ลา" <?= $status === 'ลา' ? 'selected' : '' ?>>ลา</option>
                <option value="ออกก่อนเวลา" <?= $status === 'ออกก่อนเวลา' ? 'selected' : '' ?>>ออกก่อนเวลา</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($date) ?>">
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
        <table class="table custom-table mb-0 align-middle" id="attendanceTable">
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>นักศึกษา</th>
                    <th>เวลาเข้า</th>
                    <th>เวลาออก</th>
                    <th>จำนวนชั่วโมง</th>
                    <th>สถานะ</th>
                    <th>หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendanceRecords)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">ไม่พบข้อมูลการเข้า-ออกฝึกงาน</td></tr>
                <?php else: ?>
                    <?php foreach ($attendanceRecords as $att): ?>
                        <tr>
                            <td class="fw-bold"><?= formatThaiDate($att['attendance_date']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($att['student_name']) ?></div>
                                <small class="text-muted"><?=$att['student_code']?> (<?=$att['class_level']?>)</small>
                            </td>
                            <td class="fw-semibold text-success"><?=$att['check_in'] ?? '-'?></td>
                            <td class="fw-semibold text-danger"><?=$att['check_out'] ?? '-'?></td>
                            <td class="fw-bold"><?=$att['total_hours']?> ชม.</td>
                            <td><?= getStatusBadgeHtml($att['status']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($att['note'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
