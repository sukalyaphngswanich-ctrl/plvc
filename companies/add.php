<?php
// ====================================================================
// Add Company Interface (companies/add.php)
// ====================================================================

$pageTitle = 'เพิ่มสถานประกอบการ';
$activePage = 'companies_add';
$activeGroup = 'companies';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher', 'student']);

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name     = trim($_POST['company_name'] ?? '');
    $business_type    = trim($_POST['business_type'] ?? '');
    $description      = trim($_POST['description'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $province         = trim($_POST['province'] ?? 'พิษณุโลก');
    $district         = trim($_POST['district'] ?? '');
    $subdistrict      = trim($_POST['subdistrict'] ?? '');
    $postal_code      = trim($_POST['postal_code'] ?? '');
    $latitude         = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : 16.8211000;
    $longitude        = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : 100.2658000;
    $phone            = trim($_POST['phone'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $website          = trim($_POST['website'] ?? '');
    $contact_name     = trim($_POST['contact_name'] ?? '');
    $contact_position = trim($_POST['contact_position'] ?? '');
    $contact_phone    = trim($_POST['contact_phone'] ?? '');
    $contact_email    = trim($_POST['contact_email'] ?? '');

    if (empty($company_name) || empty($address) || empty($province)) {
        $error = 'กรุณากรอกชื่อบริษัท ที่อยู่ และจังหวัด';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO companies (company_name, business_type, description, address, province, district, subdistrict, postal_code, latitude, longitude, phone, email, website, contact_name, contact_position, contact_phone, contact_email) 
                                  VALUES (:cn, :bt, :desc, :addr, :prov, :dist, :subd, :pc, :lat, :lng, :ph, :em, :web, :cname, :cpos, :cph, :cem)");
            $stmt->execute([
                ':cn' => $company_name, ':bt' => $business_type, ':desc' => $description,
                ':addr' => $address, ':prov' => $province, ':dist' => $district, ':subd' => $subdistrict, ':pc' => $postal_code,
                ':lat' => $latitude, ':lng' => $longitude, ':ph' => $phone, ':em' => $email, ':web' => $website,
                ':cname' => $contact_name, ':cpos' => $contact_position, ':cph' => $contact_phone, ':cem' => $contact_email
            ]);
            $newCompId = $db->lastInsertId();

            if (hasRole('student')) {
                redirectUrl("../internships/add.php?company_id=" . $newCompId . "&msg=company_added");
            } else {
                redirectUrl("index.php?msg=added");
            }
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-plus-circle-fill text-warning me-2"></i> เพิ่มสถานประกอบการใหม่</h3>
        <p class="text-muted small m-0">กรอกข้อมูลบริษัท ที่อยู่ พิกัดแผนก GPS และผู้ติดต่อ</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST" class="row g-3">
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-building me-1"></i> 1. ข้อมูลบริษัท/สถานประกอบการ</h6>
        
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อสถานประกอบการ *</label>
            <input type="text" name="company_name" class="form-control" placeholder="เช่น บริษัท พิษณุโลก ซอฟต์แวร์ โซลูชั่น จำกัด" required>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ประเภทธุรกิจ *</label>
            <input type="text" name="business_type" class="form-control" placeholder="เช่น พัฒนาซอฟต์แวร์, ดิจิทัลมาร์เก็ตติ้ง" required>
        </div>
        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">รายละเอียดบริษัท</label>
            <textarea name="description" class="form-control" rows="2" placeholder="รายละเอียดลักษณะงานและสินค้า/บริการ..."></textarea>
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-geo-alt-fill me-1"></i> 2. ที่อยู่และพิกัดแผนที่ (GPS)</h6>
        
        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">ที่อยู่ *</label>
            <input type="text" name="address" class="form-control" placeholder="เช่น 99/9 ถ.สิงหวัฒน์ ต.พลายชุมพล อ.เมือง" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">จังหวัด *</label>
            <input type="text" name="province" class="form-control" value="พิษณุโลก" required>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">อำเภอ/เขต</label>
            <input type="text" name="district" class="form-control" value="เมืองพิษณุโลก">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">ตำบล/แขวง</label>
            <input type="text" name="subdistrict" class="form-control" value="ในเมือง">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-medium small text-secondary">รหัสไปรษณีย์</label>
            <input type="text" name="postal_code" class="form-control" value="65000">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">Latitude (ละติจูด)</label>
            <input type="number" step="any" name="latitude" class="form-control" value="16.8211000">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">Longitude (ลองจิจูด)</label>
            <input type="number" step="any" name="longitude" class="form-control" value="100.2658000">
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-person-lines-fill me-1"></i> 3. ข้อมูลการติดต่อ</h6>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">เบอร์โทรศัพท์บริษัท</label>
            <input type="text" name="phone" class="form-control" placeholder="055XXXXXX">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">อีเมลบริษัท</label>
            <input type="email" name="email" class="form-control" placeholder="contact@company.com">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">เว็บไซต์</label>
            <input type="url" name="website" class="form-control" placeholder="https://www.company.com">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อผู้ประสานงาน / HR</label>
            <input type="text" name="contact_name" class="form-control" placeholder="คุณวิชัย เทคโนโลยี">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ตำแหน่งผู้ประสานงาน</label>
            <input type="text" name="contact_position" class="form-control" placeholder="HR Manager">
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-warning text-white px-4"><i class="bi bi-save me-1"></i> บันทึกสถานประกอบการ</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
