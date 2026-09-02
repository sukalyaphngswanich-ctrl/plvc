<?php
// ====================================================================
// Reports & Export Engine (reports/index.php)
// ====================================================================

$pageTitle = 'รายงานและสถิติ';
$activePage = 'reports';
$activeGroup = 'reports';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();

// --- CSV Export Action ---
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $csvSql = "SELECT s.student_code, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.department,
                      c.company_name, i.position, i.start_date, i.end_date, i.status
               FROM internships i
               JOIN students s ON i.student_id = s.id
               JOIN companies c ON i.company_id = c.id
               ORDER BY s.student_code";
    $rows = $db->query($csvSql)->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="internship_report_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    // BOM for Excel UTF-8
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['รหัสนักศึกษา', 'ชื่อ-สกุล', 'แผนกวิชา', 'สถานประกอบการ', 'ตำแหน่ง', 'วันเริ่ม', 'วันสิ้นสุด', 'สถานะ']);
    foreach ($rows as $r) {
        fputcsv($out, array_values($r));
    }
    fclose($out);
    exit;
}

// --- Summary Statistics ---
$totalStudents   = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$activeInterns   = $db->query("SELECT COUNT(*) FROM internships WHERE status = 'กำลังฝึกงาน'")->fetchColumn();
$totalCompanies  = $db->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$totalTeachers   = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();

// --- Internship Status Distribution ---
$statusData = $db->query("SELECT status, COUNT(*) as cnt FROM internships GROUP BY status")->fetchAll();
$statusLabels = []; $statusCounts = [];
$statusColors = ['รอจัดสถานที่' => '#94a3b8', 'รอยืนยัน' => '#f59e0b', 'พร้อมฝึกงาน' => '#3b82f6', 'กำลังฝึกงาน' => '#10b981', 'ใกล้สิ้นสุด' => '#f97316', 'ฝึกงานเสร็จแล้ว' => '#6366f1', 'มีปัญหา' => '#ef4444', 'ยกเลิก' => '#6b7280'];
$bgColors = [];
foreach ($statusData as $sd) {
    $statusLabels[] = $sd['status'];
    $statusCounts[] = (int)$sd['cnt'];
    $bgColors[] = $statusColors[$sd['status']] ?? '#94a3b8';
}

// --- Attendance Summary ---
$attData = $db->query("SELECT status, COUNT(*) as cnt FROM attendance GROUP BY status")->fetchAll();
$attLabels = []; $attCounts = []; 
$attColors = ['ปกติ' => '#10b981', 'ขาด' => '#ef4444', 'มาสาย' => '#f59e0b', 'ลา' => '#6366f1', 'ออกก่อนเวลา' => '#f97316'];
$attBg = [];
foreach ($attData as $ad) {
    $attLabels[] = $ad['status'];
    $attCounts[] = (int)$ad['cnt'];
    $attBg[] = $attColors[$ad['status']] ?? '#94a3b8';
}

// --- Evaluation Average by Department ---
$evalData = $db->query("SELECT s.department, AVG(e.total_score) as avg_score, COUNT(e.id) as cnt
                         FROM evaluations e
                         JOIN students s ON e.student_id = s.id
                         GROUP BY s.department")->fetchAll();
$evalLabels = []; $evalScores = [];
foreach ($evalData as $ed) {
    $evalLabels[] = $ed['department'] ?: 'ไม่ระบุ';
    $evalScores[] = round((float)$ed['avg_score'], 1);
}

// --- Company Capacity ---
$capData = $db->query("SELECT c.company_name, c.max_students, COUNT(i.id) as current_students
                        FROM companies c
                        LEFT JOIN internships i ON i.company_id = c.id AND i.status IN ('พร้อมฝึกงาน', 'กำลังฝึกงาน')
                        GROUP BY c.id
                        ORDER BY current_students DESC
                        LIMIT 10")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i> รายงานและสถิติภาพรวม</h3>
        <p class="text-muted small m-0">สรุปข้อมูลการฝึกงาน การเข้าเรียน ผลประเมิน และสถานประกอบการ</p>
    </div>
    <a href="index.php?action=export_csv" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-spreadsheet me-1"></i> ส่งออก CSV</a>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
            <div class="rounded-circle bg-primary-subtle text-primary mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;"><i class="bi bi-people-fill fs-4"></i></div>
            <div class="fs-3 fw-bold text-dark"><?=$totalStudents?></div>
            <div class="small text-muted">นักศึกษาทั้งหมด</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
            <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;"><i class="bi bi-briefcase-fill fs-4"></i></div>
            <div class="fs-3 fw-bold text-dark"><?=$activeInterns?></div>
            <div class="small text-muted">กำลังฝึกงาน</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
            <div class="rounded-circle bg-warning-subtle text-warning mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;"><i class="bi bi-building fs-4"></i></div>
            <div class="fs-3 fw-bold text-dark"><?=$totalCompanies?></div>
            <div class="small text-muted">สถานประกอบการ</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
            <div class="rounded-circle bg-info-subtle text-info mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;"><i class="bi bi-person-badge-fill fs-4"></i></div>
            <div class="fs-3 fw-bold text-dark"><?=$totalTeachers?></div>
            <div class="small text-muted">ครูที่ปรึกษา</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Internship Status Doughnut -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i> สถานะการฝึกงาน</h6>
            <canvas id="statusChart" height="240"></canvas>
        </div>
    </div>
    <!-- Attendance Bar -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-calendar-check text-success me-2"></i> สรุปการเข้าเรียน</h6>
            <canvas id="attendanceChart" height="240"></canvas>
        </div>
    </div>
    <!-- Evaluation Score Bar -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-award-fill text-warning me-2"></i> คะแนนประเมินเฉลี่ย</h6>
            <canvas id="evalChart" height="240"></canvas>
        </div>
    </div>
</div>

<!-- Company Capacity Table -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <h6 class="fw-bold mb-3"><i class="bi bi-building-fill-gear text-info me-2"></i> ความจุสถานประกอบการ (Top 10)</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>สถานประกอบการ</th>
                    <th class="text-center">รับได้สูงสุด</th>
                    <th class="text-center">รับอยู่ปัจจุบัน</th>
                    <th class="text-center">ว่าง</th>
                    <th style="width:200px;">สัดส่วน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($capData as $cap):
                    $max = (int)$cap['max_students'];
                    $cur = (int)$cap['current_students'];
                    $avail = max(0, $max - $cur);
                    $pct = $max > 0 ? round(($cur / $max) * 100) : 0;
                    $barColor = $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success');
                ?>
                <tr>
                    <td class="fw-medium"><?= htmlspecialchars($cap['company_name']) ?></td>
                    <td class="text-center"><?=$max?></td>
                    <td class="text-center fw-bold"><?=$cur?></td>
                    <td class="text-center text-success fw-bold"><?=$avail?></td>
                    <td>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar <?=$barColor?>" style="width:<?=$pct?>%"></div>
                        </div>
                        <small class="text-muted"><?=$pct?>%</small>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($capData)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">ยังไม่มีข้อมูลสถานประกอบการ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Internship Status Doughnut
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{
            data: <?= json_encode($statusCounts) ?>,
            backgroundColor: <?= json_encode($bgColors) ?>,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } }
        }
    }
});

// Attendance Bar
new Chart(document.getElementById('attendanceChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($attLabels) ?>,
        datasets: [{
            label: 'จำนวน',
            data: <?= json_encode($attCounts) ?>,
            backgroundColor: <?= json_encode($attBg) ?>,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// Evaluation Average Score Bar
new Chart(document.getElementById('evalChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($evalLabels) ?>,
        datasets: [{
            label: 'คะแนนเฉลี่ย',
            data: <?= json_encode($evalScores) ?>,
            backgroundColor: '#6366f1',
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, max: 100 } }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
