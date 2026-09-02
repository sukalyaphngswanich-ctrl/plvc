<?php
// ====================================================================
// Student Internship Location Map (companies/map.php)
// ====================================================================

$pageTitle = 'แผนที่สถานที่ฝึกงานของนักศึกษา';
$activePage = 'companies_map';
$activeGroup = 'companies';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Determine selected student ID
$selectedStudentId = 0;

if (hasRole('student')) {
    $selectedStudentId = $currentUser['profile']['id'] ?? 0;
} else {
    if (isset($_GET['student_id']) && (int)$_GET['student_id'] > 0) {
        $selectedStudentId = (int)$_GET['student_id'];
    }
}

// Search & Filter parameters for Teacher/Admin dropdown
$deptFilter = trim($_GET['department'] ?? '');
$roomFilter = trim($_GET['room'] ?? '');
$searchKey  = trim($_GET['search'] ?? '');

// Fetch all students list for dropdown
$studentWhere = ["1=1"];
$studentParams = [];

if ($deptFilter) {
    $studentWhere[] = "s.department = :dept";
    $studentParams[':dept'] = $deptFilter;
}
if ($roomFilter) {
    $studentWhere[] = "s.room = :room";
    $studentParams[':room'] = $roomFilter;
}
if ($searchKey) {
    $studentWhere[] = "(s.first_name LIKE :sk OR s.last_name LIKE :sk OR s.student_code LIKE :sk)";
    $studentParams[':sk'] = "%$searchKey%";
}

$studentWhereSql = implode(" AND ", $studentWhere);

$studentsListSql = "SELECT s.id, s.student_code, CONCAT(s.first_name, ' ', s.last_name) as name, s.department, s.room,
                           c.company_name
                    FROM students s
                    LEFT JOIN internships i ON s.id = i.student_id
                    LEFT JOIN companies c ON i.company_id = c.id
                    WHERE $studentWhereSql
                    ORDER BY s.department ASC, s.room ASC, s.student_code ASC";

$stmtList = $db->prepare($studentsListSql);
$stmtList->execute($studentParams);
$allStudents = $stmtList->fetchAll();

// If no student selected yet for Teacher/Admin, pick the first student in the list
if ($selectedStudentId === 0 && !empty($allStudents)) {
    $selectedStudentId = (int)$allStudents[0]['id'];
}

// Fetch Detailed Internship & Location Data for the selected student
$studentData = null;
if ($selectedStudentId > 0) {
    $infoStmt = $db->prepare("SELECT s.*, CONCAT(s.first_name, ' ', s.last_name) as student_name,
                                     CONCAT(t.first_name, ' ', t.last_name) as advisor_name, t.phone as advisor_phone,
                                     i.position as intern_position, i.supervisor_name, i.supervisor_phone, i.supervisor_position,
                                     i.start_date, i.end_date, i.status as internship_status_detail,
                                     c.id as company_id, c.company_name, c.business_type, c.address as company_address,
                                     c.province, c.district, c.subdistrict, c.postal_code, c.phone as company_phone,
                                     c.email as company_email, c.website as company_website, c.contact_name, c.contact_phone,
                                     c.latitude, c.longitude
                              FROM students s
                              LEFT JOIN teachers t ON s.advisor_id = t.id
                              LEFT JOIN internships i ON s.id = i.student_id
                              LEFT JOIN companies c ON i.company_id = c.id
                              WHERE s.id = :sid LIMIT 1");
    $infoStmt->execute([':sid' => $selectedStudentId]);
    $studentData = $infoStmt->fetch();
}

// Fetch filter lists
$deptList = $db->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != '' ORDER BY department ASC")->fetchAll(PDO::FETCH_COLUMN);
$roomList = $db->query("SELECT DISTINCT room FROM students WHERE room IS NOT NULL AND room != '' ORDER BY room ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill text-danger me-2"></i> พิกัดสถานที่ฝึกงานของนักศึกษา</h3>
        <p class="text-muted small m-0">ค้นหาและดูแผนที่ระบุพิกัดสถานประกอบการที่นักศึกษาแต่ละคนฝึกงาน</p>
    </div>
</div>

<?php if (hasRole(['admin', 'teacher'])): ?>
    <!-- Student Selector Card for Teachers/Admin -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="map.php" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small text-secondary fw-bold">เลือกนักศึกษาที่ต้องการดูสถานที่ฝึกงาน *</label>
                <select name="student_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php if (empty($allStudents)): ?>
                        <option value="">-- ไม่พบนักศึกษา --</option>
                    <?php else: ?>
                        <?php foreach ($allStudents as $st): ?>
                            <option value="<?=$st['id']?>" <?= $selectedStudentId == $st['id'] ? 'selected' : '' ?>>
                                <?=$st['student_code']?> - <?= htmlspecialchars($st['name']) ?> [<?= htmlspecialchars($st['department']) ?> - <?= htmlspecialchars($st['room']) ?>] → <?= htmlspecialchars($st['company_name'] ?? 'ยังไม่ระบุสถานที่') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-secondary fw-medium">กรองสาขาวิชา</label>
                <select name="department" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- ทุกสาขาวิชา --</option>
                    <?php foreach ($deptList as $d): ?>
                        <option value="<?= htmlspecialchars($d) ?>" <?= $deptFilter === $d ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-secondary fw-medium">กรองห้อง / กลุ่มเรียน</label>
                <select name="room" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- ทุกห้อง --</option>
                    <?php foreach ($roomList as $rm): ?>
                        <option value="<?= htmlspecialchars($rm) ?>" <?= $roomFilter === $rm ? 'selected' : '' ?>><?= htmlspecialchars($rm) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-1">
                <a href="map.php" class="btn btn-light btn-sm w-100" title="รีเซ็ต"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php if (!$studentData || empty($studentData['company_name'])): ?>
    <div class="alert alert-warning p-4 rounded-4 shadow-sm text-center">
        <i class="bi bi-exclamation-triangle-fill fs-2 text-warning mb-2 d-block"></i>
        <h5 class="fw-bold mb-1">ไม่พบข้อมูลสถานที่ฝึกงานของนักศึกษารายนี้</h5>
        <p class="text-muted small mb-0">นักศึกษารายนี้ยังไม่ได้จัดลงลงทะเบียนสถานที่ฝึกงานในระบบ</p>
    </div>
<?php else: ?>
    <div class="row g-4 mb-4">
        <!-- Student & Company Profile Overview Card -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-3">
                    <div class="user-avatar" style="width:56px; height:56px; font-size:1.5rem; background:linear-gradient(135deg, #2563eb, #0ea5e9); flex-shrink:0;">
                        <?= mb_substr($studentData['first_name'], 0, 1, 'UTF-8') ?>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($studentData['student_name']) ?></h5>
                        <div class="small text-muted">รหัส: <strong><?=$studentData['student_code']?></strong></div>
                        <div class="small text-primary fw-medium"><?=$studentData['department']?> (<?=$studentData['room']?>)</div>
                    </div>
                </div>

                <!-- Company Details -->
                <div class="mb-3">
                    <div class="small text-muted mb-1"><i class="bi bi-building-fill text-warning me-1"></i> สถานประกอบการ:</div>
                    <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($studentData['company_name']) ?></h6>
                    <div class="small text-secondary mb-2"><i class="bi bi-briefcase-fill text-primary me-1"></i> ตำแหน่ง: <?= htmlspecialchars($studentData['intern_position'] ?? 'นักศึกษาฝึกงาน') ?></div>
                    <div><?= getStatusBadgeHtml($studentData['internship_status']) ?></div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-3 small">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> ที่อยู่สถานที่ฝึกงาน:</div>
                    <p class="mb-2 text-secondary"><?= htmlspecialchars($studentData['company_address']) ?> ต.<?= htmlspecialchars($studentData['subdistrict']) ?> อ.<?= htmlspecialchars($studentData['district']) ?> จ.<?= htmlspecialchars($studentData['province']) ?> <?=$studentData['postal_code']?></p>
                    
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-person-fill text-info me-1"></i> พี่เลี้ยงประจำบริษัท:</div>
                    <div class="text-secondary mb-1"><?= htmlspecialchars($studentData['supervisor_name'] ?? $studentData['contact_name'] ?? 'ไม่ระบุ') ?> (<?= htmlspecialchars($studentData['supervisor_phone'] ?? $studentData['contact_phone'] ?? '-') ?>)</div>

                    <div class="fw-bold text-dark mb-1"><i class="bi bi-person-badge-fill text-success me-1"></i> ครูที่ปรึกษา:</div>
                    <div class="text-secondary"><?= htmlspecialchars($studentData['advisor_name'] ?? 'ไม่ระบุ') ?></div>
                </div>

                <?php if ($studentData['latitude'] && $studentData['longitude']): ?>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?=$studentData['latitude']?>,<?=$studentData['longitude']?>" target="_blank" class="btn btn-outline-primary btn-sm w-100 rounded-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดูบน Google Maps (นำทาง)
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Interactive Map Container Card -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-2 bg-white h-100">
                <div id="companyMap" style="height: 100%; min-height: 480px; border-radius: 12px;"></div>
            </div>
        </div>
    </div>

    <!-- Leaflet Map Initialization Script -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const lat = <?= floatval($studentData['latitude'] ?? 16.8211000) ?>;
        const lng = <?= floatval($studentData['longitude'] ?? 100.2658000) ?>;
        const companyName = <?= json_encode($studentData['company_name'], JSON_UNESCAPED_UNICODE) ?>;
        const studentName = <?= json_encode($studentData['student_name'], JSON_UNESCAPED_UNICODE) ?>;
        const address = <?= json_encode($studentData['company_address'] . ' จ.' . $studentData['province'], JSON_UNESCAPED_UNICODE) ?>;

        const map = L.map('companyMap').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const popupContent = `
            <div style="font-family:'Prompt', sans-serif; min-width:220px; padding:4px;">
                <h6 style="margin:0 0 6px 0; font-weight:700; color:#2563eb;">${companyName}</h6>
                <div style="font-size:0.85rem; font-weight:600; color:#1e293b; margin-bottom:4px;">
                    <i class="bi bi-person-fill text-primary"></i> ${studentName}
                </div>
                <div style="font-size:0.8rem; color:#64748b; margin-bottom:8px;">
                    <i class="bi bi-geo-alt-fill text-danger"></i> ${address}
                </div>
                <a href="https://www.google.com/maps/search/?api=1&query=${lat},${lng}" target="_blank" class="btn btn-sm btn-primary w-100" style="font-size:0.75rem; padding:4px 8px; color:white; text-decoration:none; display:block; text-align:center; border-radius:6px;">
                    <i class="bi bi-geo-alt-fill me-1"></i> นำทางด้วย Google Maps
                </a>
            </div>
        `;

        L.marker([lat, lng]).addTo(map)
            .bindPopup(popupContent)
            .openPopup();
    });
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
