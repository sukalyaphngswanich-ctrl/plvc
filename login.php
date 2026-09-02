<?php
// ====================================================================
// Login Page - Student Internship Management & Tracking System
// ====================================================================

require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectUrl("dashboard.php");
}

$errorMsg = '';
$successMsg = '';

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $successMsg = 'ออกจากระบบเรียบร้อยแล้ว';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $errorMsg = 'กรุณากรอกชื่อผู้ใช้/รหัสนักศึกษา และรหัสผ่าน';
    } else {
        $result = loginUser($username, $password);
        if ($result['success']) {
            redirectUrl("dashboard.php");
        } else {
            $errorMsg = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | <?= SITE_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.35);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .login-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
            position: relative;
        }
        .login-header-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
        }
        .btn-login {
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        }
        .shortcut-badge {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .shortcut-badge:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="login-header-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h4 class="fw-bold mb-1"><?= COLLEGE_NAME ?></h4>
        <div class="small text-white-50"><?= SITE_NAME ?></div>
    </div>

    <div class="p-4 p-md-5">
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= htmlspecialchars($errorMsg) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 py-2" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div><?= htmlspecialchars($successMsg) ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label text-secondary fw-medium small">ชื่อผู้ใช้ / รหัสนักศึกษา</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="เช่น admin, student1" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-secondary fw-medium small">รหัสผ่าน</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" checked>
                    <label class="form-check-label text-muted small" for="remember">จดจำฉันในระบบ</label>
                </div>
                <a href="#" class="text-primary text-decoration-none small" onclick="alert('กรุณาลืมรหัสผ่านโดยติดต่อเจ้าหน้าที่ผู้ดูแลระบบแผนกวิชา'); return false;">ลืมรหัสผ่าน?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-login mb-4">
                <i class="bi bi-box-arrow-in-right me-2"></i> เข้าสู่ระบบ
            </button>
        </form>

        <!-- Quick Demo Testing Accounts Box -->
        <div class="border-top pt-3 text-center">
            <div class="text-muted small mb-2 fw-medium"><i class="bi bi-key-fill text-warning me-1"></i> บัญชีทดสอบระบบ (รหัสผ่าน: <code>123456</code>)</div>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <span class="badge bg-dark shortcut-badge p-2" onclick="fillLogin('admin', '123456')">
                    <i class="bi bi-shield-lock me-1"></i> Admin (admin)
                </span>
                <span class="badge bg-info shortcut-badge p-2" onclick="fillLogin('teacher1', '123456')">
                    <i class="bi bi-person-workspace me-1"></i> Teacher (teacher1)
                </span>
                <span class="badge bg-success shortcut-badge p-2" onclick="fillLogin('student1', '123456')">
                    <i class="bi bi-mortarboard me-1"></i> Student (student1)
                </span>
            </div>
        </div>
    </div>
</div>

<script>
function fillLogin(u, p) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = p;
}
</script>
</body>
</html>
