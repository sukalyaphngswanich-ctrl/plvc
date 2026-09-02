<?php
$bp = $basePath ?? '';
$currentUser = getCurrentUser();
$userName = $currentUser['profile']['full_name'] ?? $currentUser['username'];
$userInitial = mb_substr($userName, 0, 1, 'UTF-8');
$role = $currentUser['role'];
$activePage = $activePage ?? '';

// Fetch latest 5 unread notifications
$db = getDB();
$nStmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
$nStmt->execute([':uid' => $currentUser['id']]);
$navNotifications = $nStmt->fetchAll();
?>
<!-- SoGoodWeb Style Top Navigation Bar -->
<header class="app-navbar sogood-header shadow-sm">
    <div class="container-fluid px-3 px-lg-4 d-flex align-items-center justify-content-between">
        
        <!-- Left Section: Logo & Brand -->
        <div class="d-flex align-items-center gap-3">
            <a href="<?=$bp?>dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="sogood-brand-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="lh-1">
                    <span class="sogood-brand-title">PLVC</span>
                    <span class="sogood-brand-subtitle d-none d-sm-inline">วิทยาลัยอาชีวศึกษาพิษณุโลก</span>
                </div>
            </a>
        </div>

        <!-- Center/Right Section: Horizontal Top Menu Links (SoGoodWeb Style) -->
        <nav class="sogood-nav-links d-none d-xl-flex align-items-center gap-1">
            <!-- แดชบอร์ด -->
            <a href="<?=$bp?>dashboard.php" class="sogood-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                แดชบอร์ด
            </a>

            <!-- ข้อมูลนักศึกษา Dropdown -->
            <div class="sogood-dropdown">
                <a href="#" class="sogood-nav-item <?= strpos($activePage, 'students') === 0 ? 'active' : '' ?>">
                    ข้อมูลนักศึกษา <i class="bi bi-chevron-down ms-1" style="font-size:0.75rem;"></i>
                </a>
                <div class="sogood-dropdown-menu">
                    <a href="<?=$bp?>students/index.php"><i class="bi bi-list-stars me-2 text-primary"></i> รายชื่อนักศึกษา</a>
                    <?php if (hasRole(['admin', 'teacher'])): ?>
                        <a href="<?=$bp?>students/add.php"><i class="bi bi-person-plus-fill me-2 text-success"></i> เพิ่มนักศึกษาใหม่</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- สถานประกอบการ Dropdown -->
            <div class="sogood-dropdown">
                <a href="#" class="sogood-nav-item <?= strpos($activePage, 'companies') === 0 ? 'active' : '' ?>">
                    สถานประกอบการ <i class="bi bi-chevron-down ms-1" style="font-size:0.75rem;"></i>
                </a>
                <div class="sogood-dropdown-menu">
                    <a href="<?=$bp?>companies/index.php"><i class="bi bi-building-fill me-2 text-warning"></i> รายชื่อสถานประกอบการ</a>
                    <?php if (hasRole(['admin', 'teacher'])): ?>
                        <a href="<?=$bp?>companies/add.php"><i class="bi bi-plus-circle me-2 text-success"></i> เพิ่มสถานประกอบการ</a>
                    <?php endif; ?>
                    <a href="<?=$bp?>companies/map.php"><i class="bi bi-geo-alt-fill me-2 text-danger"></i> แผนที่สถานที่ฝึกงาน</a>
                </div>
            </div>

            <!-- การฝึกงาน Dropdown -->
            <div class="sogood-dropdown">
                <a href="#" class="sogood-nav-item <?= strpos($activePage, 'internship') === 0 ? 'active' : '' ?>">
                    การฝึกงาน <i class="bi bi-chevron-down ms-1" style="font-size:0.75rem;"></i>
                </a>
                <div class="sogood-dropdown-menu">
                    <a href="<?=$bp?>internships/index.php"><i class="bi bi-briefcase-fill me-2 text-success"></i> รายการฝึกงานทั้งหมด</a>
                    <a href="<?=$bp?>internships/index.php?status=กำลังฝึกงาน"><i class="bi bi-arrow-repeat me-2 text-info"></i> ติดตามสถานะการฝึกงาน</a>
                </div>
            </div>

            <!-- บันทึกเวลา & การฝึก Dropdown -->
            <div class="sogood-dropdown">
                <a href="#" class="sogood-nav-item <?= in_array($activePage, ['daily_logs', 'attendance']) ? 'active' : '' ?>">
                    บันทึก & เวลาฝึก <i class="bi bi-chevron-down ms-1" style="font-size:0.75rem;"></i>
                </a>
                <div class="sogood-dropdown-menu">
                    <a href="<?=$bp?>daily-logs/index.php"><i class="bi bi-journal-check me-2 text-danger"></i> บันทึกประจำวัน (Daily Log)</a>
                    <a href="<?=$bp?>attendance/index.php"><i class="bi bi-clock-history me-2 text-warning"></i> บันทึกเวลาเข้า-ออกงาน</a>
                </div>
            </div>

            <!-- การประเมิน -->
            <a href="<?=$bp?>evaluations/index.php" class="sogood-nav-item <?= strpos($activePage, 'eval') === 0 ? 'active' : '' ?>">
                การประเมิน
            </a>

            <!-- การนิเทศ -->
            <a href="<?=$bp?>supervision/index.php" class="sogood-nav-item <?= strpos($activePage, 'supervision') === 0 ? 'active' : '' ?>">
                การนิเทศ
            </a>

            <!-- ประกาศ -->
            <a href="<?=$bp?>announcements/index.php" class="sogood-nav-item <?= strpos($activePage, 'announcements') === 0 ? 'active' : '' ?>">
                ประกาศ
            </a>

            <!-- ผู้ช่วย AI Chatbot -->
            <a href="<?=$bp?>ai-chat/index.php" class="sogood-nav-item text-primary fw-bold <?= strpos($activePage, 'ai_chat') === 0 ? 'active' : '' ?>">
                <i class="bi bi-robot me-1 text-primary"></i> ผู้ช่วย AI
            </a>

            <?php if (hasRole(['admin', 'teacher'])): ?>
                <!-- รายงาน -->
                <a href="<?=$bp?>reports/index.php" class="sogood-nav-item <?= strpos($activePage, 'reports') === 0 ? 'active' : '' ?>">
                    รายงาน
                </a>
            <?php endif; ?>

            <?php if (hasRole('admin')): ?>
                <!-- ผู้ใช้งาน -->
                <a href="<?=$bp?>users/index.php" class="sogood-nav-item <?= strpos($activePage, 'users') === 0 ? 'active' : '' ?>">
                    ผู้ใช้งาน
                </a>
            <?php endif; ?>

            <!-- Notification Bell Icon -->
            <div class="dropdown ms-2">
                <button class="btn btn-light position-relative rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:38px; height:38px;">
                    <i class="bi bi-bell-fill text-slate-600"></i>
                    <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;">
                            <?=$unreadCount?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0" style="width: 300px; border-radius: 12px; overflow: hidden;">
                    <div class="bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="bi bi-bell me-1"></i> การแจ้งเตือน</span>
                        <a href="<?=$bp?>notifications/index.php" class="text-white text-decoration-none small">ดูทั้งหมด</a>
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 260px; overflow-y: auto;">
                        <?php if (empty($navNotifications)): ?>
                            <div class="p-3 text-center text-muted small">ไม่มีการแจ้งเตือนในขณะนี้</div>
                        <?php else: ?>
                            <?php foreach ($navNotifications as $notif): ?>
                                <a href="<?=$bp?>notifications/index.php" class="list-group-item list-group-item-action p-3 <?= $notif['is_read'] ? '' : 'bg-light fw-bold' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-primary"><?= htmlspecialchars($notif['title']) ?></span>
                                        <span class="text-muted" style="font-size:0.68rem;"><?= formatThaiDate($notif['created_at'], true) ?></span>
                                    </div>
                                    <p class="mb-0 text-secondary small text-truncate"><?= htmlspecialchars($notif['message']) ?></p>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Profile Action Button (Orange/Blue Rounded Pill Button style SoGoodWeb) -->
            <div class="dropdown ms-2">
                <button class="btn btn-warning rounded-pill px-3 py-1 fw-bold text-dark shadow-sm d-flex align-items-center gap-2" style="font-size:0.88rem; background-color:#ff9800; border:none;" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-6"></i>
                    <span><?= htmlspecialchars($userName) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius:10px;">
                    <li>
                        <div class="dropdown-header">
                            <strong><?= htmlspecialchars($userName) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($roleStr) ?></small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <?php if (hasRole('student')): ?>
                        <li><a class="dropdown-item py-2 small" href="<?=$bp?>students/view.php"><i class="bi bi-person-circle me-2 text-primary"></i> ข้อมูลส่วนตัว</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item py-2 small text-danger" href="<?=$bp?>logout.php" onclick="return confirm('ยืนยันออกจากระบบ?');"><i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ</a></li>
                </ul>
            </div>
        </nav>

        <!-- Mobile ☰ Toggle Button -->
        <button class="sidebar-toggle-btn d-xl-none ms-auto" id="sidebarToggleBtn" title="เปิดเมนู">
            <i class="bi bi-list fs-2 text-primary"></i>
        </button>

    </div>
</header>
<div class="app-content">
<main class="main-body-container">
