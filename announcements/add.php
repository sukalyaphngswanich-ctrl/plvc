<?php
// ====================================================================
// Add Announcement Form (announcements/add.php)
// ====================================================================

$pageTitle = 'สร้างประกาศใหม่';
$activePage = 'announcements';
$activeGroup = 'announcements';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title'] ?? '');
    $content   = trim($_POST['content'] ?? '');
    $expire_at = !empty($_POST['expire_at']) ? $_POST['expire_at'] : null;

    if (empty($title) || empty($content)) {
        $error = 'กรุณากรอกหัวข้อและเนื้อหาประกาศ';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO announcements (title, content, created_by, expire_at, status) VALUES (:t, :c, :uid, :exp, 'active')");
            $stmt->execute([':t' => $title, ':c' => $content, ':uid' => $currentUser['id'], ':exp' => $expire_at]);
            redirectUrl("index.php?msg=added");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-plus-circle-fill text-primary me-2"></i> สร้างประกาศใหม่</h3>
        <p class="text-muted small m-0">กระจายข่าวสารแจ้งนักศึกษาและผู้เกี่ยวข้อง</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST" class="row g-3">
        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">หัวข้อประกาศ *</label>
            <input type="text" name="title" class="form-control" placeholder="เช่น กำหนดส่ง Daily Log ประจำสัปดาห์" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">เนื้อหาประกาศ *</label>
            <textarea name="content" class="form-control" rows="4" placeholder="รายละเอียดประกาศ ข่าวสาร หรือคำชี้แจง..." required></textarea>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">วันหมดอายุประกาศ (เว้นว่างถ้าไม่มี)</label>
            <input type="date" name="expire_at" class="form-control">
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> โพสต์ประกาศ</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
