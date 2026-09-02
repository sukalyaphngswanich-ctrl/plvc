<?php
// ====================================================================
// Student Detailed View with 6 Tabs (students/view.php)
// ====================================================================

$pageTitle = 'รายละเอียดนักศึกษา';
$activePage = 'students_list';
$activeGroup = 'students';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Determine Student ID (If student role, view own profile if ID not specified)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0 && hasRole('student')) {
    $id = $currentUser['profile']['id'] ?? 0;
}

if (!verifyStudentAccess($id)) {
    echo "<div class='alert alert-danger p-4 m-4'>403 Access Denied - คุณไม่มีสิทธิ์ดูข้อมูลนักศึกษารายนี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 1. Fetch Student Profile & Advisor
$stmt = $db->prepare("SELECT s.*, CONCAT(s.first_name, ' ', s.last_name) as full_name,
                             t.first_name as adv_fn, t.last_name as adv_ln, t.phone as adv_phone, t.email as adv_email
                      FROM students s
                      LEFT JOIN teachers t ON s.advisor_id = t.id
                      WHERE s.id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลนักศึกษานี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 2. Fetch Internship Details
$iStmt = $db->prepare("SELECT i.*, c.company_name, c.address as company_address, c.phone as company_phone, c.contact_name, c.contact_phone
                       FROM internships i
                       LEFT JOIN companies c ON i.company_id = c.id
                       WHERE i.student_id = :sid LIMIT 1");
$iStmt->execute([':sid' => $id]);
$internship = $iStmt->fetch();

// 3. Fetch Daily Logs
$dlStmt = $db->prepare("SELECT * FROM daily_logs WHERE student_id = :sid ORDER BY log_date DESC");
$dlStmt->execute([':sid' => $id]);
$dailyLogs = $dlStmt->fetchAll();

// 4. Fetch Attendance
$attStmt = $db->prepare("SELECT * FROM attendance WHERE student_id = :sid ORDER BY attendance_date DESC");
$attStmt->execute([':sid' => $id]);
$attendanceList = $attStmt->fetchAll();

// Attendance Counters
$attStats = ['ปกติ' => 0, 'มาสาย' => 0, 'ขาด' => 0, 'ลา' => 0, 'ออกก่อนเวลา' => 0];
foreach ($attendanceList as $a) {
    if (isset($attStats[$a['status']])) {
        $attStats[$a['status']]++;
    }
}

// 5. Fetch Evaluations
$evalStmt = $db->prepare("SELECT * FROM evaluations WHERE student_id = :sid ORDER BY id DESC");
$evalStmt->execute([':sid' => $id]);
$evaluations = $evalStmt->fetchAll();

// 6. Fetch Supervision History
$supStmt = $db->prepare("SELECT s.*, c.company_name, CONCAT(t.first_name, ' ', t.last_name) as teacher_name
                        FROM supervision s
                        LEFT JOIN companies c ON s.company_id = c.id
                        LEFT JOIN teachers t ON s.teacher_id = t.id
                        WHERE s.student_id = :sid ORDER BY s.visit_date DESC");
$supStmt->execute([':sid' => $id]);
$supervisions = $supStmt->fetchAll();
?>

<!-- Header Title -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="user-avatar" style="width: 58px; height: 58px; font-size: 1.5rem; background: linear-gradient(135deg, #2563eb, #0ea5e9);">
            <?= mb_substr($student['first_name'], 0, 1, 'UTF-8') ?>
        </div>
        <div>
            <h4 class="fw-bold mb-0 text-slate-900"><?= htmlspecialchars($student['full_name']) ?> (<?=$student['student_code']?>)</h4>
            <div class="text-muted small">
                <?=$student['class_level']?> ห้อง <?=$student['room']?> | สาขา<?=$student['department']?> | ปีการศึกษา <?=$student['academic_year']?>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?= getStatusBadgeHtml($student['internship_status']) ?>
        <?php if (hasRole(['admin', 'teacher'])): ?>
            <a href="edit.php?id=<?=$student['id']?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil-square"></i> แก้ไข</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> กลับ</a>
    </div>
</div>

<!-- 6 Tabs Navigation -->
<ul class="nav nav-tabs border-bottom mb-4" id="studentTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-medium" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab"><i class="bi bi-person-circle me-1"></i> 1. ข้อมูลส่วนตัว</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-medium" id="internship-tab" data-bs-toggle="tab" data-bs-target="#internship" type="button" role="tab"><i class="bi bi-building me-1"></i> 2. ข้อมูลการฝึกงาน</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-medium" id="dailylog-tab" data-bs-toggle="tab" data-bs-target="#dailylog" type="button" role="tab"><i class="bi bi-journal-check me-1"></i> 3. Daily Log (<?=count($dailyLogs)?>)</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-medium" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab"><i class="bi bi-clock-history me-1"></i> 4. Attendance (<?=count($attendanceList)?>)</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-medium" id="evaluation-tab" data-bs-toggle="tab" data-bs-target="#evaluation" type="button" role="tab"><i class="bi bi-star-fill text-warning me-1"></i> 5. Evaluation (<?=count($evaluations)?>)</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-medium" id="supervision-tab" data-bs-toggle="tab" data-bs-target="#supervision" type="button" role="tab"><i class="bi bi-car-front me-1"></i> 6. Supervision (<?=count($supervisions)?>)</button>
    </li>
</ul>

<!-- Tabs Content Window -->
<div class="tab-content" id="studentTabContent">

    <!-- TAB 1: ข้อมูลส่วนตัว -->
    <div class="tab-pane fade show active" id="personal" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-card-heading me-1"></i> ประวัติส่วนตัว</h6>
                    <table class="table table-sm table-borderless align-middle">
                        <tr><td class="text-muted" style="width:140px;">รหัสนักศึกษา:</td><td class="fw-bold"><?=$student['student_code']?></td></tr>
                        <tr><td class="text-muted">ชื่อ-นามสกุล:</td><td class="fw-semibold"><?= htmlspecialchars($student['full_name']) ?></td></tr>
                        <tr><td class="text-muted">ระดับชั้น / ห้อง:</td><td><?=$student['class_level']?> (<?=$student['room']?>)</td></tr>
                        <tr><td class="text-muted">สาขาวิชา:</td><td><?=$student['department']?></td></tr>
                        <tr><td class="text-muted">ปีการศึกษา:</td><td><?=$student['academic_year']?></td></tr>
                    </table>
                </div>

                <div class="col-12 col-md-6">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-telephone-fill me-1"></i> ข้อมูลการติดต่อ & ครูที่ปรึกษา</h6>
                    <table class="table table-sm table-borderless align-middle">
                        <tr><td class="text-muted" style="width:140px;">เบอร์โทรศัพท์:</td><td><?=$student['phone'] ?? '-'?></td></tr>
                        <tr><td class="text-muted">อีเมล:</td><td><?=$student['email'] ?? '-'?></td></tr>
                        <tr><td class="text-muted">ที่อยู่ปัจจุบัน:</td><td><?= htmlspecialchars($student['address'] ?? '-') ?></td></tr>
                        <tr>
                            <td class="text-muted">ครูที่ปรึกษา:</td>
                            <td class="fw-bold text-primary">
                                <?php if (!empty($student['adv_fn'])): ?>
                                    <?= htmlspecialchars($student['adv_fn'].' '.$student['adv_ln']) ?> (โทร: <?=$student['adv_phone']?>)
                                <?php else: ?>
                                    <span class="text-muted">- ไม่ได้ระบุ -</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: ข้อมูลการฝึกงาน -->
    <div class="tab-pane fade" id="internship" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <?php if (!$internship): ?>
                <div class="text-center py-5">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-building-exclamation fs-1 d-block mb-2"></i>
                        <h5 class="fw-bold text-slate-800">ยังไม่ได้ระบุ / ลงทะเบียนข้อมูลการฝึกงาน</h5>
                        <p class="text-muted small mb-3">นักศึกษายังไม่มีข้อมูลสถานประกอบการและตำแหน่งงานฝึกปฏิบัติในระบบ</p>
                    </div>
                    <?php if (hasRole(['admin', 'teacher', 'student'])): ?>
                        <a href="../internships/add.php?student_id=<?=$student['id']?>" class="btn btn-success px-4 rounded-pill">
                            <i class="bi bi-plus-circle-fill me-1"></i> ลงทะเบียนจัดสถานที่ฝึกงานให้นักศึกษาคนนี้
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-building me-1"></i> สถานประกอบการ</h6>
                        <h5 class="fw-bold text-slate-900 mb-2"><?= htmlspecialchars($internship['company_name']) ?></h5>
                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($internship['company_address']) ?></p>
                        <p class="text-muted small mb-2"><i class="bi bi-telephone me-1"></i> เบอร์โทรบริษัท: <?=$internship['company_phone']?></p>
                        <p class="text-muted small mb-0"><i class="bi bi-person me-1"></i> ผู้ติดต่อ: <?= htmlspecialchars($internship['contact_name']) ?> (<?=$internship['contact_phone']?>)</p>
                    </div>

                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-briefcase-fill me-1"></i> รายละเอียดการฝึกงาน</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted" style="width:140px;">ตำแหน่ง:</td><td class="fw-bold text-primary"><?= htmlspecialchars($internship['position']) ?></td></tr>
                            <tr><td class="text-muted">วันที่เริ่มฝึก:</td><td><?= formatThaiDate($internship['start_date']) ?></td></tr>
                            <tr><td class="text-muted">วันที่สิ้นสุด:</td><td><?= formatThaiDate($internship['end_date']) ?></td></tr>
                            <tr><td class="text-muted">ชั่วโมงฝึกงานต่อวัน:</td><td><?=$internship['working_hours_per_day']?> ชั่วโมง/วัน</td></tr>
                            <tr><td class="text-muted">พี่เลี้ยงฝึกงาน:</td><td class="fw-semibold"><?= htmlspecialchars($internship['supervisor_name']) ?> (<?=$internship['supervisor_phone']?>)</td></tr>
                        </table>
                    </div>

                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3">
                            <div class="fw-bold mb-1">รายละเอียดงานที่รับผิดชอบ (Job Description):</div>
                            <p class="text-secondary small mb-0"><?= htmlspecialchars($internship['job_description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB 3: Daily Log -->
    <div class="tab-pane fade" id="dailylog" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0"><i class="bi bi-journal-text text-primary me-2"></i> ประวัติการบันทึก Daily Log ทั้งหมด</h6>
                <?php if (hasRole('student')): ?>
                    <a href="../daily-logs/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> เพิ่มบันทึกใหม่</a>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table custom-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>เวลา เข้า-ออก</th>
                            <th>งานที่ทำประจำวัน</th>
                            <th>ปัญหา/วิธีแก้ไข</th>
                            <th>สถานะการตรวจ</th>
                            <th>ความคิดเห็นครู</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dailyLogs)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีประวัติการบันทึก Daily Log</td></tr>
                        <?php else: ?>
                            <?php foreach ($dailyLogs as $log): ?>
                                <tr>
                                    <td class="fw-bold text-nowrap"><?= formatThaiDate($log['log_date']) ?></td>
                                    <td class="small text-nowrap"><i class="bi bi-clock me-1 text-muted"></i> <?=$log['check_in']?> - <?=$log['check_out']?></td>
                                    <td>
                                        <div class="fw-semibold text-dark small"><?= htmlspecialchars($log['work_description']) ?></div>
                                        <small class="text-muted">สิ่งที่เรียนรู้: <?= htmlspecialchars($log['learning'] ?? '-') ?></small>
                                    </td>
                                    <td class="small text-muted">
                                        <?= htmlspecialchars($log['problem'] ? $log['problem'].' / '.$log['solution'] : '-') ?>
                                    </td>
                                    <td><?= getStatusBadgeHtml($log['status']) ?></td>
                                    <td class="small text-secondary"><?= htmlspecialchars($log['teacher_comment'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: Attendance -->
    <div class="tab-pane fade" id="attendance" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <div class="p-3 bg-white border rounded-3 text-center">
                    <div class="small text-muted">เข้าฝึกงานปกติ</div>
                    <h4 class="fw-bold text-success mb-0"><?=$attStats['ปกติ']?> วัน</h4>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-3 bg-white border rounded-3 text-center">
                    <div class="small text-muted">มาสาย</div>
                    <h4 class="fw-bold text-warning mb-0"><?=$attStats['มาสาย']?> ครั้ง</h4>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-3 bg-white border rounded-3 text-center">
                    <div class="small text-muted">ขาดฝึกงาน</div>
                    <h4 class="fw-bold text-danger mb-0"><?=$attStats['ขาด']?> ครั้ง</h4>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-3 bg-white border rounded-3 text-center">
                    <div class="small text-muted">ลางาน</div>
                    <h4 class="fw-bold text-secondary mb-0"><?=$attStats['ลา']?> ครั้ง</h4>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 bg-white border rounded-3 text-center">
                    <div class="small text-muted">ชั่วโมงฝึกงานรวมสะสม</div>
                    <h4 class="fw-bold text-primary mb-0"><?= array_sum(array_column($attendanceList, 'total_hours')) ?> / 320 ชม.</h4>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table custom-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>เวลาเข้า</th>
                            <th>เวลาออก</th>
                            <th>ชั่วโมงรวม</th>
                            <th>สถานะ</th>
                            <th>หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendanceList)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">ไม่พบประวัติการเข้า-ออกฝึกงาน</td></tr>
                        <?php else: ?>
                            <?php foreach ($attendanceList as $att): ?>
                                <tr>
                                    <td class="fw-bold"><?= formatThaiDate($att['attendance_date']) ?></td>
                                    <td><?=$att['check_in'] ?? '-'?></td>
                                    <td><?=$att['check_out'] ?? '-'?></td>
                                    <td class="fw-semibold"><?=$att['total_hours']?> ชม.</td>
                                    <td><?= getStatusBadgeHtml($att['status']) ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($att['note'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 5: Evaluation -->
    <div class="tab-pane fade" id="evaluation" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-star-fill text-warning me-1"></i> ผลการประเมินการฝึกงาน</h6>
            <?php if (empty($evaluations)): ?>
                <div class="text-center py-5 text-muted">ยังไม่มีบันทึกการประเมินการฝึกงานสำหรับนักศึกษารายนี้</div>
            <?php else: ?>
                <?php foreach ($evaluations as $ev): ?>
                    <div class="p-3 border rounded-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary"><i class="bi bi-person-check me-1"></i> ผู้ประเมิน: <?=$ev['evaluator_type']?></span>
                            <span class="fs-5 fw-bold text-success"><?=$ev['average_score']?>% (<?=$ev['result']?>)</span>
                        </div>
                        <div class="row g-2 small text-secondary mb-2">
                            <div class="col-6 col-md-3">1. ความรับผิดชอบ: <strong><?=$ev['responsibility_score']?>/5</strong></div>
                            <div class="col-6 col-md-3">2. ตรงต่อเวลา: <strong><?=$ev['punctuality_score']?>/5</strong></div>
                            <div class="col-6 col-md-3">3. ความขยัน: <strong><?=$ev['hardworking_score']?>/5</strong></div>
                            <div class="col-6 col-md-3">4. ทำงานร่วมกับผู้อื่น: <strong><?=$ev['teamwork_score']?>/5</strong></div>
                        </div>
                        <div class="small">
                            <strong>จุดเด่น:</strong> <?= htmlspecialchars($ev['strength'] ?? '-') ?><br>
                            <strong>จุดที่ควรปรับปรุง:</strong> <?= htmlspecialchars($ev['improvement'] ?? '-') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB 6: Supervision -->
    <div class="tab-pane fade" id="supervision" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-car-front-fill me-1"></i> ประวัติการนิเทศการฝึกงาน</h6>
            <?php if (empty($supervisions)): ?>
                <div class="text-center py-5 text-muted">ยังไม่มีบันทึกประวัติการนิเทศนักศึกษา</div>
            <?php else: ?>
                <?php foreach ($supervisions as $sup): ?>
                    <div class="p-3 border rounded-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-bold text-primary"><i class="bi bi-calendar-event me-1"></i> <?= formatThaiDate($sup['visit_date'], true) ?></div>
                            <?= getStatusBadgeHtml($sup['status']) ?>
                        </div>
                        <div class="small text-muted mb-2">ผู้นิเทศ: <?= htmlspecialchars($sup['teacher_name']) ?> | รูปแบบ: <strong><?=$sup['visit_type']?></strong></div>
                        <div class="small text-dark mb-1"><strong>ผลการนิเทศ:</strong> <?= htmlspecialchars($sup['result']) ?></div>
                        <div class="small text-danger"><strong>ปัญหาที่พบ/ข้อเสนอแนะ:</strong> <?= htmlspecialchars($sup['problem'] ? $sup['problem'].' - '.$sup['recommendation'] : 'ไม่มีปัญหา') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
