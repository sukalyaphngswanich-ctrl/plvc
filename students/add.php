<?php
// ====================================================================
// Add Student Interface (students/add.php)
// ====================================================================

$pageTitle = 'เพิ่มนักศึกษาใหม่';
$activePage = 'students_add';
$activeGroup = 'students';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username      = trim($_POST['username'] ?? '');
    $password      = trim($_POST['password'] ?? '');
    $student_code  = trim($_POST['student_code'] ?? '');
    $first_name    = trim($_POST['first_name'] ?? '');
    $last_name     = trim($_POST['last_name'] ?? '');
    $class_level   = trim($_POST['class_level'] ?? 'ปวส.2');
    $room          = trim($_POST['room'] ?? 'สท.2/1');
    $department    = trim($_POST['department'] ?? 'เทคโนโลยีสารสนเทศ');
    $academic_year = trim($_POST['academic_year'] ?? '2567');
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $advisor_id    = !empty($_POST['advisor_id']) ? (int)$_POST['advisor_id'] : null;
    $status        = trim($_POST['internship_status'] ?? 'กำลังฝึกงาน');

    // Basic Validation
    if (empty($username) || empty($password) || empty($student_code) || empty($first_name) || empty($last_name)) {
        $error = 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน (ชื่อผู้ใช้, รหัสผ่าน, รหัสนักศึกษา, ชื่อ, นามสกุล)';
    } else {
        try {
            $db->beginTransaction();

            // Insert into users
            $uStmt = $db->prepare("INSERT INTO users (username, password, role, status) VALUES (:u, :p, 'student', 'active')");
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            $uStmt->execute([':u' => $username, ':p' => $hashedPass]);
            $userId = $db->lastInsertId();

            // Insert into students
            $sStmt = $db->prepare("INSERT INTO students (user_id, student_code, first_name, last_name, class_level, room, department, academic_year, phone, email, address, advisor_id, internship_status) 
                                   VALUES (:uid, :code, :fn, :ln, :cl, :rm, :dept, :ay, :phone, :email, :addr, :adv, :st)");
            $sStmt->execute([
                ':uid'   => $userId,
                ':code'  => $student_code,
                ':fn'    => $first_name,
                ':ln'    => $last_name,
                ':cl'    => $class_level,
                ':rm'    => $room,
                ':dept'  => $department,
                ':ay'    => $academic_year,
                ':phone' => $phone,
                ':email' => $email,
                ':addr'  => $address,
                ':adv'   => $advisor_id,
                ':st'    => $status
            ]);

            $db->commit();
            redirectUrl("index.php?msg=added");
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

// Fetch Teachers for select dropdown
$teachersList = $db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, department FROM teachers ORDER BY first_name ASC")->fetchAll();
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary me-2"></i> เพิ่มนักศึกษาใหม่</h3>
        <p class="text-muted small m-0">กรอกข้อมูลนักศึกษาและสร้างบัญชีเข้าใช้งานระบบ</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST" class="row g-3">
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-key-fill me-1"></i> 1. บัญชีผู้ใช้งานระบบ</h6>
        
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ชื่อผู้ใช้ (Username) *</label>
            <input type="text" name="username" class="form-control" required placeholder="เช่น std6706">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">รหัสผ่าน (Password) *</label>
            <input type="password" name="password" class="form-control" required placeholder="อย่างน้อย 6 ตัวอักษร">
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-person-badge-fill me-1"></i> 2. ข้อมูลส่วนตัวและสังกัด</h6>

        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">รหัสนักศึกษา *</label>
            <input type="text" name="student_code" class="form-control" required placeholder="เช่น STD6706">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">ชื่อ *</label>
            <input type="text" name="first_name" class="form-control" required placeholder="ชื่อภาษาไทย">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-medium small text-secondary">นามสกุล *</label>
            <input type="text" name="last_name" class="form-control" required placeholder="นามสกุลภาษาไทย">
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">ระดับชั้น *</label>
            <select name="class_level" class="form-select" required>
                <option value="ปวส.2">ปวส.2</option>
                <option value="ปวส.1">ปวส.1</option>
                <option value="ปวช.3">ปวช.3</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">ห้อง / กลุ่มเรียน *</label>
            <select name="room" class="form-select" required>
                <option value="สท.2/1" selected>สท.2/1</option>
                <option value="สท.2/2">สท.2/2</option>
                <option value="สท.1/1">สท.1/1</option>
                <option value="ดก.2/1">ดก.2/1</option>
                <option value="ดก.1/1">ดก.1/1</option>
                <option value="บช.2/1">บช.2/1</option>
                <option value="บช.2/2">บช.2/2</option>
                <option value="ตล.2/1">ตล.2/1</option>
                <option value="คบ.2/1">คบ.2/1</option>
                <option value="สพ.2/1">สพ.2/1</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">สาขาวิชา *</label>
            <select name="department" class="form-select" required>
                <option value="เทคโนโลยีสารสนเทศ" selected>เทคโนโลยีสารสนเทศ</option>
                <option value="ดิจิทัลกราฟิก">ดิจิทัลกราฟิก</option>
                <option value="การบัญชี">การบัญชี</option>
                <option value="การตลาด">การตลาด</option>
                <option value="คอมพิวเตอร์ธุรกิจ">คอมพิวเตอร์ธุรกิจ</option>
                <option value="การจัดการสำนักงาน">การจัดการสำนักงาน</option>
                <option value="คหกรรมศาสตร์">คหกรรมศาสตร์</option>
                <option value="การโรงแรมและการท่องเที่ยว">การโรงแรมและการท่องเที่ยว</option>
                <option value="อาหารและโภชนาการ">อาหารและโภชนาการ</option>
                <option value="ดีไซน์แฟชั่นและสิ่งทอ">ดีไซน์แฟชั่นและสิ่งทอ</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-medium small text-secondary">ปีการศึกษา *</label>
            <input type="text" name="academic_year" class="form-control" value="2567" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">เบอร์โทรศัพท์</label>
            <input type="text" name="phone" class="form-control" placeholder="08XXXXXXXX">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">อีเมล</label>
            <input type="email" name="email" class="form-control" placeholder="student@plvc.ac.th">
        </div>

        <div class="col-12">
            <label class="form-label fw-medium small text-secondary">ที่อยู่ปัจจุบัน</label>
            <textarea name="address" class="form-control" rows="2" placeholder="เช่น 60 ถ.วังจันทน์ ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000"></textarea>
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-sliders me-1"></i> 3. ข้อมูลการฝึกงานและครูที่ปรึกษา</h6>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">ครูที่ปรึกษา</label>
            <select name="advisor_id" class="form-select">
                <option value="">-- เลือกครูที่ปรึกษา --</option>
                <?php foreach ($teachersList as $t): ?>
                    <option value="<?=$t['id']?>"><?= htmlspecialchars($t['name']) ?> (<?=$t['department']?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-medium small text-secondary">สถานะการฝึกงาน</label>
            <select name="internship_status" class="form-select">
                <option value="ยังไม่เริ่มฝึก">ยังไม่เริ่มฝึก</option>
                <option value="กำลังฝึกงาน" selected>กำลังฝึกงาน</option>
                <option value="ฝึกงานเสร็จแล้ว">ฝึกงานเสร็จแล้ว</option>
                <option value="มีปัญหา">มีปัญหา</option>
                <option value="ยกเลิก">ยกเลิก</option>
            </select>
        </div>

        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> บันทึกข้อมูลนักศึกษา</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
