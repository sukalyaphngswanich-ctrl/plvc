<?php
// ====================================================================
// Daily Log Management & Approval Interface (daily-logs/index.php)
// ====================================================================

$pageTitle = 'บันทึกการฝึกงาน (Daily Log)';
$activePage = 'daily_logs';
$activeGroup = 'logs';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Handle Advisor Approval / Correction Action POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve_log') {
    requireRole(['admin', 'teacher']);
    $logId   = (int)$_POST['log_id'];
    $status  = trim($_POST['status']);
    $comment = trim($_POST['teacher_comment'] ?? '');

    $stmt = $db->prepare("UPDATE daily_logs SET status = :st, teacher_comment = :cm WHERE id = :lid");
    $stmt->execute([':st' => $status, ':cm' => $comment, ':lid' => $logId]);

    // Fetch log details to trigger student notification
    $dlInfo = $db->query("SELECT student_id, log_date FROM daily_logs WHERE id = $logId")->fetch();
    if ($dlInfo) {
        $stUser = $db->query("SELECT user_id FROM students WHERE id = {$dlInfo['student_id']}")->fetchColumn();
        if ($stUser) {
            $notifType = ($status === 'ผ่าน') ? 'success' : (($status === 'ไม่ผ่าน') ? 'danger' : 'warning');
            $db->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (:uid, :title, :msg, :type, :link)")
               ->execute([
                   ':uid'   => $stUser,
                   ':title' => "ครูตรวจ Daily Log แล้ว ($status)",
                   ':msg'   => "ครูผู้ตรวจได้ปรับสถานะ Daily Log ประจำวันที่ " . formatThaiDate($dlInfo['log_date']) . " เป็น: $status",
                   ':type'  => $notifType,
                   ':link'  => "/daily-logs/view.php?id=" . $logId
               ]);
        }
    }

    redirectUrl("index.php?msg=updated");
}

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$date   = trim($_GET['log_date'] ?? '');

$where = ["1=1"];
$params = [];

if (hasRole('teacher')) {
    $tId = $currentUser['profile']['id'] ?? 0;
    $where[] = "s.advisor_id = :tid";
    $params[':tid'] = $tId;
} else if (hasRole('student')) {
    $sId = $currentUser['profile']['id'] ?? 0;
    $where[] = "dl.student_id = :sid";
    $params[':sid'] = $sId;
}

if (!empty($search)) {
    $where[] = "(s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_code LIKE :search OR dl.work_description LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($status)) {
    $where[] = "dl.status = :st";
    $params[':st'] = $status;
}
if (!empty($date)) {
    $where[] = "dl.log_date = :ld";
    $params[':ld'] = $date;
}

$whereSql = implode(" AND ", $where);

$sql = "SELECT dl.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.student_code, s.class_level,
               c.company_name
        FROM daily_logs dl
        JOIN students s ON dl.student_id = s.id
        JOIN internships i ON dl.internship_id = i.id
        JOIN companies c ON i.company_id = c.id
        WHERE $whereSql
        ORDER BY dl.log_date DESC, dl.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$dailyLogs = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-journal-bookmark-fill text-danger me-2"></i> บันทึกการฝึกงานประจำวัน</h3>
        <p class="text-muted small m-0">ตรวจสอบ อนุมัติ และบันทึกรายละเอียดการปฏิบัติงานประจำวัน</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportTableToCSV('dailyLogsTable', 'daily-logs.csv')" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> ส่งออกข้อมูล Excel
        </button>
        <?php if (hasRole('student')): ?>
            <a href="add.php" class="btn btn-danger btn-sm"><i class="bi bi-plus-circle-fill me-1"></i> บันทึกประจำวันวันนี้</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i> บันทึกผลการตรวจสอบเรียบร้อยแล้ว
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search Filter -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา รหัส, ชื่อนักศึกษา, งานที่ทำ..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">-- ทุกสถานะการตรวจ --</option>
                <option value="รอตรวจสอบ" <?= $status === 'รอตรวจสอบ' ? 'selected' : '' ?>>รอตรวจสอบ</option>
                <option value="ผ่าน" <?= $status === 'ผ่าน' ? 'selected' : '' ?>>ผ่าน</option>
                <option value="ไม่ผ่าน" <?= $status === 'ไม่ผ่าน' ? 'selected' : '' ?>>ไม่ผ่าน</option>
                <option value="ต้องแก้ไข" <?= $status === 'ต้องแก้ไข' ? 'selected' : '' ?>>ต้องแก้ไข</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <input type="date" name="log_date" class="form-control form-control-sm" value="<?= htmlspecialchars($date) ?>">
        </div>
        <div class="col-12 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i> ค้นหา</button>
            <a href="index.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Logs Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table custom-table mb-0 align-middle" id="dailyLogsTable">
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>นักศึกษา</th>
                    <th>เข้า - ออก</th>
                    <th>รายละเอียดงานที่ปฏิบัติ</th>
                    <th>สิ่งที่เรียนรู้ / ปัญหา</th>
                    <th>สถานะตรวจ</th>
                    <th class="text-end">จัดการ / ตรวจ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dailyLogs)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">ไม่พบข้อมูลบันทึก Daily Log</td></tr>
                <?php else: ?>
                    <?php foreach ($dailyLogs as $log): ?>
                        <tr>
                            <td class="fw-bold text-nowrap"><?= formatThaiDate($log['log_date']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($log['student_name']) ?></div>
                                <small class="text-muted"><?=$log['student_code']?> (<?=$log['class_level']?>)</small>
                            </td>
                            <td class="small text-nowrap">
                                <i class="bi bi-clock me-1 text-primary"></i><?=$log['check_in']?> - <?=$log['check_out']?>
                            </td>
                            <td>
                                <div class="fw-semibold text-slate-800 small"><?= htmlspecialchars($log['work_description']) ?></div>
                                <small class="text-muted"><i class="bi bi-building me-1"></i><?= htmlspecialchars($log['company_name']) ?></small>
                            </td>
                            <td class="small text-secondary">
                                <?= htmlspecialchars($log['learning'] ? 'เรียนรู้: '.$log['learning'] : '-') ?><br>
                                <?php if ($log['problem']): ?>
                                    <span class="text-danger">ปัญหา: <?= htmlspecialchars($log['problem']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= getStatusBadgeHtml($log['status']) ?>
                                <?php if ($log['teacher_comment']): ?>
                                    <div class="small text-muted border-top pt-1 mt-1">
                                        <i class="bi bi-chat-left-text me-1 text-info"></i><?= htmlspecialchars($log['teacher_comment']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?=$log['id']?>" class="btn btn-outline-primary" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
                                    
                                    <?php if (hasRole(['admin', 'teacher'])): ?>
                                        <!-- Approval Modal Trigger -->
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?=$log['id']?>" title="ตรวจสอบ/อนุมัติ">
                                            <i class="bi bi-check2-square"></i> ตรวจ
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <?php if (hasRole(['admin', 'teacher'])): ?>
                                    <!-- Approval Modal -->
                                    <div class="modal fade text-start" id="approveModal<?=$log['id']?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="index.php" method="POST">
                                                    <input type="hidden" name="action" value="approve_log">
                                                    <input type="hidden" name="log_id" value="<?=$log['id']?>">
                                                    
                                                    <div class="modal-header bg-light">
                                                        <h5 class="modal-title fw-bold"><i class="bi bi-check2-square text-success me-2"></i> ตรวจสอบ Daily Log</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <strong>นักศึกษา:</strong> <?= htmlspecialchars($log['student_name']) ?><br>
                                                            <strong>วันที่:</strong> <?= formatThaiDate($log['log_date']) ?>
                                                        </div>
                                                        <div class="p-3 bg-light rounded-3 mb-3 small">
                                                            <strong>งานที่ทำ:</strong> <?= htmlspecialchars($log['work_description']) ?>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small">ผลการตรวจ *</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="ผ่าน" <?= $log['status'] === 'ผ่าน' ? 'selected' : '' ?>>🟢 ผ่าน (อนุมัติ)</option>
                                                                <option value="ต้องแก้ไข" <?= $log['status'] === 'ต้องแก้ไข' ? 'selected' : '' ?>>🟡 ต้องแก้ไข</option>
                                                                <option value="ไม่ผ่าน" <?= $log['status'] === 'ไม่ผ่าน' ? 'selected' : '' ?>>🔴 ไม่ผ่าน</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small">ความคิดเห็น/ข้อเสนอแนะครู</label>
                                                            <textarea name="teacher_comment" class="form-control" rows="2" placeholder="กรอกความคิดเห็นเพื่อแจ้งนักศึกษา..."><?= htmlspecialchars($log['teacher_comment'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save me-1"></i> บันทึกผลการตรวจ</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
