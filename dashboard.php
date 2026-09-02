<?php
// ====================================================================
// Main Dashboard Controller & View Switcher
// Student Internship Management & Tracking System
// ====================================================================

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/includes/navbar.php';

$db = getDB();
$role = $currentUser['role'];
?>

<?php if ($role === 'admin'): ?>
    <?php
    // Fetch Admin Statistics
    $totalStudents = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalTeachers = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $totalCompanies = $db->query("SELECT COUNT(*) FROM companies")->fetchColumn();
    $pendingLogs = $db->query("SELECT COUNT(*) FROM daily_logs WHERE status = 'รอตรวจสอบ'")->fetchColumn();

    $statusNotStarted = $db->query("SELECT COUNT(*) FROM students WHERE internship_status = 'ยังไม่เริ่มฝึก'")->fetchColumn();
    $statusTraining   = $db->query("SELECT COUNT(*) FROM students WHERE internship_status = 'กำลังฝึกงาน'")->fetchColumn();
    $statusCompleted  = $db->query("SELECT COUNT(*) FROM students WHERE internship_status = 'ฝึกงานเสร็จแล้ว'")->fetchColumn();
    $statusProblem    = $db->query("SELECT COUNT(*) FROM students WHERE internship_status = 'มีปัญหา'")->fetchColumn();
    ?>

    <!-- ADMIN DASHBOARD -->
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1 text-slate-900"><i class="bi bi-speedometer2 text-primary me-2"></i> สรุปภาพรวมระบบ</h3>
            <p class="text-muted small m-0">ยินดีต้อนรับผู้ดูแลระบบ - ข้อมูลสถิติและการติดตามการฝึกงานของวิทยาลัย</p>
        </div>
        <div>
            <a href="reports/index.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-printer me-1"></i> รายงานสถิติ</a>
        </div>
    </div>

    <!-- Admin KPI Cards Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">นักศึกษาทั้งหมด</div>
                        <div class="stat-val"><?=$totalStudents?> <span class="fs-6 text-muted font-normal">คน</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">ครูผู้สอน/ที่ปรึกษา</div>
                        <div class="stat-val"><?=$totalTeachers?> <span class="fs-6 text-muted font-normal">ท่าน</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">สถานประกอบการ</div>
                        <div class="stat-val"><?=$totalCompanies?> <span class="fs-6 text-muted font-normal">แห่ง</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="bi bi-building-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">บันทึกประจำวันรอตรวจ</div>
                        <div class="stat-val text-warning"><?=$pendingLogs?> <span class="fs-6 text-muted font-normal">รายการ</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary KPI Row -->
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-3 border text-center">
                <div class="small text-muted mb-1">ยังไม่เริ่มฝึก</div>
                <h4 class="fw-bold text-secondary mb-0"><?=$statusNotStarted?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-3 border text-center">
                <div class="small text-muted mb-1">กำลังฝึกงาน</div>
                <h4 class="fw-bold text-primary mb-0"><?=$statusTraining?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-3 border text-center">
                <div class="small text-muted mb-1">ฝึกเสร็จแล้ว</div>
                <h4 class="fw-bold text-success mb-0"><?=$statusCompleted?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-3 border text-center">
                <div class="small text-muted mb-1">มีปัญหา/ต้องติดตาม</div>
                <h4 class="fw-bold text-danger mb-0"><?=$statusProblem?></h4>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0"><i class="bi bi-pie-chart-fill text-primary me-2"></i> สัดส่วนสถานะการฝึกงาน</h6>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center" style="position:relative; height: 300px;">
                    <canvas id="adminStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0"><i class="bi bi-bar-chart-fill text-success me-2"></i> นักศึกษาแยกตามแผนกวิชา</h6>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center" style="position:relative; height: 300px;">
                    <canvas id="adminDeptChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Status Doughnut Chart
        new Chart(document.getElementById('adminStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['ยังไม่เริ่มฝึก', 'กำลังฝึกงาน', 'ฝึกเสร็จแล้ว', 'มีปัญหา'],
                datasets: [{
                    data: [<?=$statusNotStarted?>, <?=$statusTraining?>, <?=$statusCompleted?>, <?=$statusProblem?>],
                    backgroundColor: ['#94a3b8', '#2563eb', '#22c55e', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Department Bar Chart
        new Chart(document.getElementById('adminDeptChart'), {
            type: 'bar',
            data: {
                labels: ['เทคโนโลยีสารสนเทศ', 'ดิจิทัลกราฟิก', 'การบัญชี', 'การตลาด'],
                datasets: [{
                    label: 'จำนวนนักศึกษา (คน)',
                    data: [3, 2, 4, 1],
                    backgroundColor: '#3b82f6',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    });
    </script>

<?php elseif ($role === 'teacher'): ?>
    <?php
    $teacherId = $currentUser['profile']['id'] ?? 1;

    // Fetch Teacher Statistics
    $tStudentsStmt = $db->prepare("SELECT COUNT(*) FROM students WHERE advisor_id = :tid");
    $tStudentsStmt->execute([':tid' => $teacherId]);
    $tTotalStudents = $tStudentsStmt->fetchColumn();

    $tTrainingStmt = $db->prepare("SELECT COUNT(*) FROM students WHERE advisor_id = :tid AND internship_status = 'กำลังฝึกงาน'");
    $tTrainingStmt->execute([':tid' => $teacherId]);
    $tTraining = $tTrainingStmt->fetchColumn();

    $tCompletedStmt = $db->prepare("SELECT COUNT(*) FROM students WHERE advisor_id = :tid AND internship_status = 'ฝึกงานเสร็จแล้ว'");
    $tCompletedStmt->execute([':tid' => $teacherId]);
    $tCompleted = $tCompletedStmt->fetchColumn();

    $tProblemStmt = $db->prepare("SELECT COUNT(*) FROM students WHERE advisor_id = :tid AND internship_status = 'มีปัญหา'");
    $tProblemStmt->execute([':tid' => $teacherId]);
    $tProblem = $tProblemStmt->fetchColumn();

    $tPendingLogsStmt = $db->prepare("SELECT COUNT(dl.id) FROM daily_logs dl JOIN students s ON dl.student_id = s.id WHERE s.advisor_id = :tid AND dl.status = 'รอตรวจสอบ'");
    $tPendingLogsStmt->execute([':tid' => $teacherId]);
    $tPendingLogs = $tPendingLogsStmt->fetchColumn();

    // Fetch students needing tracking attention (Problem, Missing logs, Late attendance)
    $attentionStmt = $db->prepare("SELECT s.*, c.company_name, i.position 
                                   FROM students s
                                   LEFT JOIN internships i ON s.id = i.student_id
                                   LEFT JOIN companies c ON i.company_id = c.id
                                   WHERE s.advisor_id = :tid AND (s.internship_status = 'มีปัญหา' OR s.id IN (
                                       SELECT student_id FROM attendance WHERE status = 'มาสาย' OR status = 'ขาด'
                                   ))");
    $attentionStmt->execute([':tid' => $teacherId]);
    $attentionStudents = $attentionStmt->fetchAll();
    ?>

    <!-- TEACHER DASHBOARD -->
    <div class="mb-4">
        <h3 class="fw-bold mb-1 text-slate-900"><i class="bi bi-person-workspace text-primary me-2"></i> แดชบอร์ดครูที่ปรึกษา</h3>
        <p class="text-muted small m-0">สวัสดีอาจารย์ <?= htmlspecialchars($currentUser['profile']['full_name'] ?? '') ?> - สรุปข้อมูลนักศึกษาในความดูแลของคุณ</p>
    </div>

    <!-- Teacher KPI Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card border-start border-4 border-primary">
                <div class="stat-label">นักศึกษาที่รับผิดชอบ</div>
                <div class="stat-val"><?=$tTotalStudents?> <span class="fs-6 text-muted">คน</span></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card border-start border-4 border-info">
                <div class="stat-label">กำลังฝึกงาน</div>
                <div class="stat-val text-primary"><?=$tTraining?> <span class="fs-6 text-muted">คน</span></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card border-start border-4 border-success">
                <div class="stat-label">ฝึกเสร็จแล้ว</div>
                <div class="stat-val text-success"><?=$tCompleted?> <span class="fs-6 text-muted">คน</span></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card border-start border-4 border-warning">
                <div class="stat-label">บันทึกประจำวันรอตรวจ</div>
                <div class="stat-val text-warning"><?=$tPendingLogs?> <span class="fs-6 text-muted font-normal">รายการ</span></div>
            </div>
        </div>
    </div>

    <!-- Attention Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-danger-subtle text-danger border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold m-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> นักศึกษาที่ควรติดตามดูแลอย่างใกล้ชิด</h6>
            <span class="badge bg-danger"><?=count($attentionStudents)?> คน</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table custom-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>สถานประกอบการ</th>
                            <th>สถานะการฝึกงาน</th>
                            <th class="text-end">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attentionStudents)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">ไม่พบนักศึกษาที่มีปัญหาในขณะนี้</td></tr>
                        <?php else: ?>
                            <?php foreach ($attentionStudents as $st): ?>
                                <tr>
                                    <td class="fw-semibold"><?=$st['student_code']?></td>
                                    <td>
                                        <div class="fw-bold"><?=htmlspecialchars($st['first_name'].' '.$st['last_name'])?></div>
                                        <small class="text-muted"><?=$st['class_level']?> (<?=$st['room']?>)</small>
                                    </td>
                                    <td><?=htmlspecialchars($st['company_name'] ?? 'ยังไม่ระบุ')?></td>
                                    <td><?= getStatusBadgeHtml($st['internship_status']) ?></td>
                                    <td class="text-end">
                                        <a href="students/view.php?id=<?=$st['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> ติดตาม</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php else: ?>
    <?php
    // STUDENT DASHBOARD
    $studentId = $currentUser['profile']['id'] ?? 1;

    // Fetch Student Internship Details
    $iStmt = $db->prepare("SELECT i.*, c.company_name, c.address as company_address, c.phone as company_phone,
                                  t.first_name as adv_first, t.last_name as adv_last, t.phone as adv_phone
                           FROM internships i
                           LEFT JOIN companies c ON i.company_id = c.id
                           LEFT JOIN teachers t ON i.advisor_id = t.id
                           WHERE i.student_id = :sid LIMIT 1");
    $iStmt->execute([':sid' => $studentId]);
    $internship = $iStmt->fetch();

    $metrics = calculateInternshipMetrics($internship['start_date'] ?? '2026-08-01', $internship['end_date'] ?? '2026-09-30');

    // Fetch Today's Log
    $todayDate = date('Y-m-d');
    $todayLogStmt = $db->prepare("SELECT * FROM daily_logs WHERE student_id = :sid AND log_date = :ld LIMIT 1");
    $todayLogStmt->execute([':sid' => $studentId, ':ld' => $todayDate]);
    $todayLog = $todayLogStmt->fetch();
    ?>

    <!-- STUDENT DASHBOARD -->
    <?php if (!$internship): ?>
        <div class="alert alert-warning border-warning rounded-4 p-4 mb-4 text-center shadow-sm">
            <h5 class="fw-bold text-warning-emphasis mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> ท่านยังไม่มีข้อมูลสถานประกอบการและการฝึกงาน</h5>
            <p class="text-secondary small mb-3">กรุณาระบุบริษัท/สถานประกอบการที่คุณกำลังฝึกปฏิบัติงานเพื่อเริ่มบันทึก Daily Log และติดตามชั่วโมงฝึกงาน</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="internships/add.php" class="btn btn-success rounded-pill px-4"><i class="bi bi-plus-circle-fill me-1"></i> ลงทะเบียนระบุสถานที่ฝึกงาน</a>
                <a href="companies/add.php" class="btn btn-outline-primary rounded-pill px-4"><i class="bi bi-building-add me-1"></i> เพิ่มสถานประกอบการใหม่</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <!-- Student Profile Overview Card -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white h-100">
                <div class="mb-3 position-relative d-inline-block mx-auto">
                    <div class="user-avatar mx-auto" style="width:90px; height:90px; font-size:2.2rem; background:linear-gradient(135deg, #2563eb, #0ea5e9);">
                        <?= mb_substr($currentUser['profile']['first_name'] ?? 'S', 0, 1, 'UTF-8') ?>
                    </div>
                </div>
                <h5 class="fw-bold mb-1 text-slate-900"><?= htmlspecialchars($currentUser['profile']['full_name'] ?? 'นักศึกษา') ?></h5>
                <div class="text-muted small mb-2">รหัส: <strong><?= htmlspecialchars($currentUser['profile']['student_code'] ?? '-') ?></strong></div>
                <div class="d-flex justify-content-center gap-1 mb-3">
                    <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($currentUser['profile']['class_level'] ?? '') ?></span>
                    <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($currentUser['profile']['department'] ?? '') ?></span>
                </div>
                <div class="p-3 bg-light rounded-3 text-start mb-3">
                    <div class="small text-muted mb-1"><i class="bi bi-person-workspace text-primary me-1"></i> ครูที่ปรึกษา:</div>
                    <div class="fw-semibold small"><?= htmlspecialchars(($internship['adv_first'] ?? '').' '.($internship['adv_last'] ?? '')) ?></div>
                </div>
                <div>
                    <?= getStatusBadgeHtml($currentUser['profile']['internship_status'] ?? 'กำลังฝึกงาน') ?>
                </div>
            </div>
        </div>

        <!-- Internship Progress & Info Card -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0"><i class="bi bi-rocket-takeoff-fill text-primary me-2"></i> ความคืบหน้าการฝึกงาน</h5>
                    <span class="fs-5 fw-bold text-primary"><?=$metrics['percentage']?>%</span>
                </div>

                <!-- Progress Bar -->
                <div class="progress mb-4" style="height: 14px; border-radius:10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: <?=$metrics['percentage']?>%;"></div>
                </div>

                <!-- Days & Hours Stats Row -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 border rounded-3 text-center">
                            <div class="small text-muted">ฝึกไปแล้ว</div>
                            <div class="fs-4 fw-bold text-primary"><?=$metrics['days_elapsed']?> <span class="fs-6 font-normal">วัน</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 border rounded-3 text-center">
                            <div class="small text-muted">วันที่เหลือ</div>
                            <div class="fs-4 fw-bold text-secondary"><?=$metrics['days_remaining']?> <span class="fs-6 font-normal">วัน</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 border rounded-3 text-center">
                            <div class="small text-muted">ชั่วโมงสะสม</div>
                            <div class="fs-4 fw-bold text-success"><?= $metrics['days_elapsed'] * 8 ?> <span class="fs-6 font-normal">ชม.</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 border rounded-3 text-center">
                            <div class="small text-muted">ชั่วโมงทั้งหมด</div>
                            <div class="fs-4 fw-bold text-dark"><?= $internship['total_hours'] ?? 320 ?> <span class="fs-6 font-normal">ชม.</span></div>
                        </div>
                    </div>
                </div>

                <!-- Company Details -->
                <div class="p-3 bg-light rounded-3">
                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-building me-1"></i> สถานประกอบการ: <?= htmlspecialchars($internship['company_name'] ?? 'ยังไม่ระบุ') ?></h6>
                    <div class="small text-secondary mb-1"><i class="bi bi-briefcase me-1"></i> ตำแหน่ง: <?= htmlspecialchars($internship['position'] ?? 'นักศึกษาฝึกงาน') ?></div>
                    <div class="small text-secondary"><i class="bi bi-telephone me-1"></i> พี่เลี้ยง: <?= htmlspecialchars($internship['supervisor_name'] ?? 'คุณประเสริฐ งานดี') ?> (<?=$internship['supervisor_phone'] ?? '-'?>)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Internship Log Status -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-calendar-check-fill text-success me-2"></i> บันทึกการฝึกงานประจำวันนี้ (<?= formatThaiDate($todayDate) ?>)</h5>
            <a href="daily-logs/add.php" class="btn btn-primary btn-sm rounded-pill"><i class="bi bi-plus-lg me-1"></i> บันทึกประจำวันวันนี้</a>
        </div>

        <?php if (!$todayLog): ?>
            <div class="alert alert-warning d-flex align-items-center gap-3 py-3 mb-0" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-3 text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-1">ยังไม่ได้บันทึกการฝึกงานวันนี้</h6>
                    <p class="mb-0 small">อย่าลืมบันทึกเวลาเข้า-ออก และรายละเอียดงานเพื่อสะสมชั่วโมงและส่งครูที่ปรึกษาตรวจสอบ</p>
                </div>
            </div>
        <?php else: ?>
            <div class="p-3 border rounded-3 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-secondary"><i class="bi bi-clock me-1"></i> เวลาเข้า: <?=$todayLog['check_in']?> | ออก: <?=$todayLog['check_out']?></span>
                    <div><?= getStatusBadgeHtml($todayLog['status']) ?></div>
                </div>
                <div class="fw-semibold text-dark mb-1">งานที่ทำ:</div>
                <p class="text-secondary small mb-0"><?= htmlspecialchars($todayLog['work_description']) ?></p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
