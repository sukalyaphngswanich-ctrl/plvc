<?php
// ====================================================================
// View Announcement Details (announcements/view.php)
// ====================================================================

$pageTitle = 'รายละเอียดประกาศ';
$activePage = 'announcements';
$activeGroup = 'announcements';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT a.*, u.username, u.role as author_role
                       FROM announcements a
                       JOIN users u ON a.created_by = u.id
                       WHERE a.id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$anc = $stmt->fetch();

if (!$anc) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลประกาศนี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$roleLabel = ['admin' => 'ผู้ดูแลระบบ', 'teacher' => 'ครูที่ปรึกษา', 'student' => 'นักศึกษา'];
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-megaphone-fill text-primary me-2"></i> รายละเอียดประกาศ</h3>
        <p class="text-muted small m-0">เผยแพร่เมื่อ: <?= formatThaiDate($anc['created_at'], true) ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasRole(['admin', 'teacher'])): ?>
            <a href="edit.php?id=<?=$anc['id']?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i> แก้ไข</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start mb-4 pb-3 border-bottom">
        <div>
            <h4 class="fw-bold text-dark mb-2"><?= htmlspecialchars($anc['title']) ?></h4>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-primary-subtle text-primary">
                    <i class="bi bi-person-fill me-1"></i> <?= htmlspecialchars($anc['username']) ?> (<?= $roleLabel[$anc['author_role']] ?? $anc['author_role'] ?>)
                </span>
                <span class="badge <?= $anc['status'] === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                    <i class="bi bi-<?= $anc['status'] === 'active' ? 'check-circle' : 'x-circle' ?>-fill me-1"></i>
                    <?= $anc['status'] === 'active' ? 'เผยแพร่' : 'ซ่อน' ?>
                </span>
            </div>
        </div>
        <div class="text-md-end mt-3 mt-md-0">
            <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i> สร้างเมื่อ: <?= formatThaiDate($anc['created_at']) ?></div>
            <?php if ($anc['expire_at']): ?>
                <div class="small text-muted"><i class="bi bi-calendar-x me-1"></i> หมดอายุ: <?= formatThaiDate($anc['expire_at']) ?></div>
            <?php else: ?>
                <div class="small text-muted"><i class="bi bi-infinity me-1"></i> ไม่มีวันหมดอายุ</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Content -->
    <div class="p-3 bg-light rounded-3" style="line-height:1.8; font-size:1rem;">
        <?= nl2br(htmlspecialchars($anc['content'])) ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
