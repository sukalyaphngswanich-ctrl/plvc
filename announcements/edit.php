<?php
// ====================================================================
// Edit Announcement Form (announcements/edit.php)
// ====================================================================

$pageTitle = 'แก้ไขประกาศ';
$activePage = 'announcements';
$activeGroup = 'announcements';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

$stmt = $db->prepare("SELECT * FROM announcements WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$anc = $stmt->fetch();

if (!$anc) {
    echo "<div class='alert alert-danger p-4 m-4'>ไม่พบข้อมูลประกาศนี้</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title'] ?? '');
    $content   = trim($_POST['content'] ?? '');
    $expire_at = !empty($_POST['expire_at']) ? $_POST['expire_at'] : null;
    $status    = $_POST['status'] ?? 'active';

    if (empty($title) || empty($content)) {
        $error = 'กรุณากรอกหัวข้อและเนื้อหาประกาศ';
    } else {
        try {
            $stmt = $db->prepare("UPDATE announcements SET title = :t, content = :c, expire_at = :exp, status = :st WHERE id = :id");
            $stmt->execute([':t' => $title, ':c' => $content, ':exp' => $expire_at, ':st' => $status, ':id' => $id]);
            redirectUrl("index.php?msg=updated");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขประกาศ</h3>
        <p class="text-muted small m-0">แก้ไขเนื้อหาหรือสถานะประกาศข่าวสาร</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="edit.php?id=<?=$id?>" method="POST" class="row g-3">
        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">หัวข้อประกาศ *</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($anc['title']) ?>" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">เนื้อหาประกาศ *</label>
            <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($anc['content']) ?></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">วันหมดอายุประกาศ</label>
            <input type="date" name="expire_at" class="form-control" value="<?= $anc['expire_at'] ? date('Y-m-d', strtotime($anc['expire_at'])) : '' ?>">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สถานะ</label>
            <select name="status" class="form-select">
                <option value="active" <?= $anc['status'] === 'active' ? 'selected' : '' ?>>เผยแพร่</option>
                <option value="inactive" <?= $anc['status'] === 'inactive' ? 'selected' : '' ?>>ซ่อน</option>
            </select>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning px-4"><i class="bi bi-save me-1"></i> บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
