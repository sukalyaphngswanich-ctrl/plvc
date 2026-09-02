<?php
// ====================================================================
// Announcement Management Board (announcements/index.php)
// ====================================================================

$pageTitle = 'ประกาศและข่าวสาร';
$activePage = 'announcements';
$activeGroup = 'announcements';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    requireRole(['admin', 'teacher']);
    $deleteId = (int)$_GET['id'];
    $db->prepare("DELETE FROM announcements WHERE id = :id")->execute([':id' => $deleteId]);
    redirectUrl("index.php?msg=deleted");
}

$sql = "SELECT a.*, u.username, u.role as author_role
        FROM announcements a
        JOIN users u ON a.created_by = u.id
        WHERE a.status = 'active'
        ORDER BY a.created_at DESC";

$stmt = $db->query($sql);
$announcements = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-megaphone-fill text-primary me-2"></i> ประกาศและข่าวสารสำคัญ</h3>
        <p class="text-muted small m-0">ติดตามข่าวสาร กำหนดการฝึกงาน และข้อแจ้งเตือนจากทางวิทยาลัย</p>
    </div>
    <?php if (hasRole(['admin', 'teacher'])): ?>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle-fill me-1"></i> สร้างประกาศใหม่</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i> ลบประกาศเรียบร้อยแล้ว
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if (empty($announcements)): ?>
        <div class="col-12 text-center py-5 text-muted">ยังไม่มีประกาศข่าวสารในขณะนี้</div>
    <?php else: ?>
        <?php foreach ($announcements as $anc): ?>
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary-subtle text-primary"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($anc['username']) ?> (<?=$anc['author_role']?>)</span>
                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i> <?= formatThaiDate($anc['created_at']) ?></small>
                    </div>

                    <h5 class="fw-bold text-slate-900 mb-2"><?= htmlspecialchars($anc['title']) ?></h5>
                    <p class="text-secondary small mb-4" style="line-height:1.6;"><?= nl2br(htmlspecialchars($anc['content'])) ?></p>

                    <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                        <small class="text-muted">หมดอายุ: <?= $anc['expire_at'] ? formatThaiDate($anc['expire_at']) : 'ไม่มีวันหมดอายุ' ?></small>
                        
                        <?php if (hasRole(['admin', 'teacher'])): ?>
                            <div class="btn-group btn-group-sm">
                                <a href="edit.php?id=<?=$anc['id']?>" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                <button onclick="confirmDelete('index.php?action=delete&id=<?=$anc['id']?>', 'ประกาศนี้')" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
