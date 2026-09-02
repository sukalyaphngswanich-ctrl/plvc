<?php
// ====================================================================
// Notification Center (notifications/index.php)
// ====================================================================

$pageTitle = 'ศูนย์การแจ้งเตือน';
$activePage = 'notifications';
$activeGroup = 'notifications';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$userId = $currentUser['id'];

// Handle Actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'mark_read' && isset($_GET['id'])) {
        $nId = (int)$_GET['id'];
        $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid")->execute([':id' => $nId, ':uid' => $userId]);

        // If notification has a link, redirect to it
        $linkStmt = $db->prepare("SELECT link FROM notifications WHERE id = :id LIMIT 1");
        $linkStmt->execute([':id' => $nId]);
        $link = $linkStmt->fetchColumn();
        if ($link) {
            header("Location: " . $link);
            exit;
        }
        redirectUrl("index.php");
    }

    if ($action === 'mark_all_read') {
        $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0")->execute([':uid' => $userId]);
        redirectUrl("index.php?msg=all_read");
    }

    if ($action === 'delete_read') {
        $db->prepare("DELETE FROM notifications WHERE user_id = :uid AND is_read = 1")->execute([':uid' => $userId]);
        redirectUrl("index.php?msg=deleted");
    }
}

// Fetch all notifications
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC");
$stmt->execute([':uid' => $userId]);
$notifications = $stmt->fetchAll();

$unreadTotal = 0;
$readTotal = 0;
foreach ($notifications as $n) {
    if ($n['is_read']) $readTotal++;
    else $unreadTotal++;
}

// Icon mapping by type
function getNotifIcon($type) {
    $map = [
        'info'    => ['bi-info-circle-fill', 'text-primary', 'bg-primary-subtle'],
        'warning' => ['bi-exclamation-triangle-fill', 'text-warning', 'bg-warning-subtle'],
        'success' => ['bi-check-circle-fill', 'text-success', 'bg-success-subtle'],
        'danger'  => ['bi-x-circle-fill', 'text-danger', 'bg-danger-subtle'],
    ];
    return $map[$type] ?? $map['info'];
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-bell-fill text-warning me-2"></i> ศูนย์การแจ้งเตือน</h3>
        <p class="text-muted small m-0">
            ทั้งหมด <?= count($notifications) ?> รายการ
            <?php if ($unreadTotal > 0): ?>
                · <span class="text-danger fw-bold"><?=$unreadTotal?> ยังไม่ได้อ่าน</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($unreadTotal > 0): ?>
            <a href="index.php?action=mark_all_read" class="btn btn-outline-primary btn-sm"><i class="bi bi-check-all me-1"></i> อ่านทั้งหมด</a>
        <?php endif; ?>
        <?php if ($readTotal > 0): ?>
            <a href="index.php?action=delete_read" class="btn btn-outline-danger btn-sm" onclick="return confirm('ลบการแจ้งเตือนที่อ่านแล้วทั้งหมด?')"><i class="bi bi-trash me-1"></i> ลบที่อ่านแล้ว</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'all_read'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i> ทำเครื่องหมายอ่านทั้งหมดแล้ว <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-trash me-2"></i> ลบการแจ้งเตือนที่อ่านแล้วเรียบร้อย <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endif; ?>

<?php if (empty($notifications)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center">
        <div class="mb-3"><i class="bi bi-bell-slash text-muted" style="font-size:3rem;"></i></div>
        <h5 class="text-muted">ไม่มีการแจ้งเตือน</h5>
        <p class="text-secondary small">ยังไม่มีการแจ้งเตือนในขณะนี้</p>
    </div>
<?php else: ?>
    <div class="d-flex flex-column gap-2">
        <?php foreach ($notifications as $n):
            $icon = getNotifIcon($n['type']);
            $isUnread = !$n['is_read'];
        ?>
            <a href="index.php?action=mark_read&id=<?=$n['id']?>"
               class="card border-0 shadow-sm rounded-3 p-3 text-decoration-none <?= $isUnread ? 'border-start border-4 border-primary bg-primary-subtle bg-opacity-10' : 'bg-white' ?>"
               style="transition: all 0.2s;">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center <?=$icon[2]?> flex-shrink-0" style="width:42px;height:42px;">
                        <i class="bi <?=$icon[0]?> <?=$icon[1]?> fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-1 fw-bold text-dark <?= $isUnread ? '' : 'text-muted' ?>"><?= htmlspecialchars($n['title']) ?>
                                <?php if ($isUnread): ?>
                                    <span class="badge bg-danger ms-2" style="font-size:0.6rem;">ใหม่</span>
                                <?php endif; ?>
                            </h6>
                            <small class="text-muted flex-shrink-0 ms-2"><?= formatThaiDate($n['created_at']) ?></small>
                        </div>
                        <p class="mb-0 small <?= $isUnread ? 'text-dark' : 'text-muted' ?>"><?= htmlspecialchars($n['message']) ?></p>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
