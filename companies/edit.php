<?php
// ====================================================================
// Edit Company Interface (companies/edit.php)
// ====================================================================

$pageTitle = 'แก้ไขข้อมูลสถานประกอบการ';
$activePage = 'companies_list';
$activeGroup = 'companies';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM companies WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$company = $stmt->fetch();

if (!$company) {
    echo "<div class='alert alert-danger'>ไม่พบข้อมูลสถานประกอบการ</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name  = trim($_POST['company_name'] ?? '');
    $business_type = trim($_POST['business_type'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $province      = trim($_POST['province'] ?? '');
    $district      = trim($_POST['district'] ?? '');
    $subdistrict   = trim($_POST['subdistrict'] ?? '');
    $postal_code   = trim($_POST['postal_code'] ?? '');
    $latitude      = (float)($_POST['latitude'] ?? 16.8211000);
    $longitude     = (float)($_POST['longitude'] ?? 100.2658000);
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $website       = trim($_POST['website'] ?? '');
    $contact_name  = trim($_POST['contact_name'] ?? '');

    if (empty($company_name) || empty($address)) {
        $error = 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน';
    } else {
        try {
            $uStmt = $db->prepare("UPDATE companies SET
                company_name = :cn, business_type = :bt, description = :desc,
                address = :addr, province = :prov, district = :dist, subdistrict = :subd,
                postal_code = :pc, latitude = :lat, longitude = :lng, phone = :ph, email = :em,
                website = :web, contact_name = :cname WHERE id = :id");
            $uStmt->execute([
                ':cn' => $company_name, ':bt' => $business_type, ':desc' => $description,
                ':addr' => $address, ':prov' => $province, ':dist' => $district, ':subd' => $subdistrict,
                ':pc' => $postal_code, ':lat' => $latitude, ':lng' => $longitude, ':ph' => $phone,
                ':em' => $email, ':web' => $website, ':cname' => $contact_name, ':id' => $id
            ]);
            redirectUrl("index.php?msg=updated");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> แก้ไขข้อมูลสถานประกอบการ</h3>
        <p class="text-muted small m-0">ปรับปรุงข้อมูลที่ตั้ง พิกัด GPS และผู้ติดต่อ</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="edit.php?id=<?=$id?>" method="POST" class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อสถานประกอบการ *</label>
            <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($company['company_name']) ?>" required>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ประเภทธุรกิจ *</label>
            <input type="text" name="business_type" class="form-control" value="<?= htmlspecialchars($company['business_type']) ?>" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">ที่อยู่ *</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($company['address']) ?>" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">จังหวัด *</label>
            <input type="text" name="province" class="form-control" value="<?= htmlspecialchars($company['province']) ?>" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">อำเภอ/เขต</label>
            <input type="text" name="district" class="form-control" value="<?= htmlspecialchars($company['district']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">Latitude</label>
            <input type="number" step="any" name="latitude" class="form-control" value="<?=$company['latitude']?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">Longitude</label>
            <input type="number" step="any" name="longitude" class="form-control" value="<?=$company['longitude']?>">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">เบอร์โทรศัพท์</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($company['phone']) ?>">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อผู้ติดต่อ</label>
            <input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($company['contact_name']) ?>">
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning text-white px-4"><i class="bi bi-save me-1"></i> บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
