<?php
// ====================================================================
// View User Details (users/view.php)
// ====================================================================

$pageTitle = 'รายละเอียดผู้ใช้';
$activePage = 'users';
$activeGroup = 'settings';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลผู้ใช้นี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Linked profile
$profile = null;
$profileType = '';
if ($user['role'] === 'student') {
    $st = $db->prepare("SELECT s.*, CONCAT(s.first_name, ' ', s.last_name) as full_name, t.first_name as advisor_first, t.last_name as advisor_last
                        FROM students s LEFT JOIN teachers t ON s.advisor_id = t.id WHERE s.user_id = :uid LIMIT 1");
    $st->execute([':uid' => $id]);
    $profile = $st->fetch();
    $profileType = 'student';
} elseif ($user['role'] === 'teacher') {
    $st = $db->prepare("SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM teachers WHERE user_id = :uid LIMIT 1");
    $st->execute([':uid' => $id]);
    $profile = $st->fetch();
    $profileType = 'teacher';
}

$roleBadge = ['admin' => 'bg-danger', 'teacher' => 'bg-primary', 'student' => 'bg-success', 'company' => 'bg-info'];
$roleLabel = ['admin' => 'ผู้ดูแลระบบ', 'teacher' => 'ครูที่ปรึกษา', 'student' => 'นักศึกษา', 'company' => 'สถานประกอบการ'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-badge-fill text-info me-2"></i> รายละเอียดผู้ใช้</h3>
        <p class="text-muted small m-0">ข้อมูลบัญชี: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?=$id?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i> แก้ไข</a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
    </div>
</div>

<div class="row g-4">
    <!-- Account Info -->
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-shield-lock me-1"></i> ข้อมูลบัญชี</h6>
            <table class="table table-sm table-borderless align-middle mb-0">
                <tr><td class="text-muted" style="width:120px;">User ID:</td><td class="fw-bold">#<?=$user['id']?></td></tr>
                <tr><td class="text-muted">ชื่อผู้ใช้:</td><td class="fw-bold"><?= htmlspecialchars($user['username']) ?></td></tr>
                <tr><td class="text-muted">อีเมล:</td><td><?= htmlspecialchars($user['email'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">บทบาท:</td><td><span class="badge <?= $roleBadge[$user['role']] ?? 'bg-secondary' ?>"><?= $roleLabel[$user['role']] ?? $user['role'] ?></span></td></tr>
                <tr>
                    <td class="text-muted">สถานะ:</td>
                    <td>
                        <?php if ($user['status'] === 'active'): ?>
                            <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>ใช้งาน</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle me-1"></i>ปิดใช้งาน</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr><td class="text-muted">สร้างเมื่อ:</td><td><?= formatThaiDate($user['created_at']) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Linked Profile -->
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-person-vcard me-1"></i> โปรไฟล์ที่เชื่อมต่อ</h6>
            <?php if ($profile): ?>
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tr><td class="text-muted" style="width:120px;">ชื่อ-สกุล:</td><td class="fw-bold"><?= htmlspecialchars($profile['full_name']) ?></td></tr>
                    <?php if ($profileType === 'student'): ?>
                        <tr><td class="text-muted">รหัสนักศึกษา:</td><td><?=$profile['student_code']?></td></tr>
                        <tr><td class="text-muted">แผนกวิชา:</td><td><?= htmlspecialchars($profile['department'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">ระดับชั้น:</td><td><?=$profile['class_level'] ?? '-'?></td></tr>
                        <tr><td class="text-muted">ครูที่ปรึกษา:</td><td><?= $profile['advisor_first'] ? htmlspecialchars($profile['advisor_first'] . ' ' . $profile['advisor_last']) : '-' ?></td></tr>
                        <tr><td class="text-muted">โทรศัพท์:</td><td><?=$profile['phone'] ?? '-'?></td></tr>
                    <?php elseif ($profileType === 'teacher'): ?>
                        <tr><td class="text-muted">ตำแหน่ง:</td><td><?= htmlspecialchars($profile['position'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">แผนกวิชา:</td><td><?= htmlspecialchars($profile['department'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">โทรศัพท์:</td><td><?=$profile['phone'] ?? '-'?></td></tr>
                        <tr><td class="text-muted">อีเมล:</td><td><?=$profile['email'] ?? '-'?></td></tr>
                    <?php endif; ?>
                </table>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                    <p class="small mb-0">
                        <?php if ($user['role'] === 'admin'): ?>
                            ผู้ดูแลระบบไม่มีโปรไฟล์เชื่อมต่อ
                        <?php else: ?>
                            ยังไม่มีโปรไฟล์เชื่อมต่อกับบัญชีนี้
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-clock-history me-1"></i> กิจกรรมล่าสุด</h6>
            <?php
            $notifs = $db->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
            $notifs->execute([':uid' => $id]);
            $recentNotifs = $notifs->fetchAll();
            ?>
            <?php if (empty($recentNotifs)): ?>
                <p class="text-muted small text-center py-3 mb-0">ยังไม่มีกิจกรรมล่าสุด</p>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentNotifs as $rn): ?>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium small"><?= htmlspecialchars($rn['title']) ?></span>
                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($rn['message']) ?></div>
                            </div>
                            <small class="text-muted flex-shrink-0 ms-3"><?= formatThaiDate($rn['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
