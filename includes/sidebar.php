<?php
$bp = $basePath ?? '';
$currentUser = getCurrentUser();
$role = $currentUser['role'];
$activePage = $activePage ?? '';
$activeGroup = $activeGroup ?? '';
?>
<!-- Sidebar Backdrop Overlay -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Sidebar Navigation Offcanvas Component -->
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="sidebar-brand-text">
            PLVC Internship
            <div style="font-size:0.72rem; font-weight:400; color:#94a3b8;">วิทยาลัยอาชีวศึกษาพิษณุโลก</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="menu-category">เมนูหลัก</div>
        <a href="<?= $bp ?>dashboard.php" class="nav-link-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span><i class="bi bi-house-door-fill text-primary"></i> แดชบอร์ด</span>
        </a>

        <!-- นักศึกษา -->
        <div class="menu-category">การจัดการข้อมูล</div>
        <a href="#" class="nav-link-item has-submenu <?= $activeGroup === 'students' ? 'active' : '' ?>">
            <span><i class="bi bi-person-badge-fill text-info"></i> ข้อมูลนักศึกษา</span>
            <i class="bi bi-chevron-down submenu-arrow" style="font-size:0.8rem;"></i>
        </a>
        <div class="submenu <?= $activeGroup === 'students' ? 'show' : '' ?>">
            <a href="<?= $bp ?>students/index.php" class="submenu-item <?= $activePage === 'students_list' ? 'active' : '' ?>">รายชื่อนักศึกษา</a>
            <?php if (hasRole(['admin', 'teacher'])): ?>
                <a href="<?= $bp ?>students/add.php" class="submenu-item <?= $activePage === 'students_add' ? 'active' : '' ?>">เพิ่มนักศึกษาใหม่</a>
            <?php endif; ?>
        </div>

        <!-- สถานประกอบการ -->
        <a href="#" class="nav-link-item has-submenu <?= $activeGroup === 'companies' ? 'active' : '' ?>">
            <span><i class="bi bi-building-fill text-warning"></i> สถานประกอบการ</span>
            <i class="bi bi-chevron-down submenu-arrow" style="font-size:0.8rem;"></i>
        </a>
        <div class="submenu <?= $activeGroup === 'companies' ? 'show' : '' ?>">
            <a href="<?= $bp ?>companies/index.php" class="submenu-item <?= $activePage === 'companies_list' ? 'active' : '' ?>">รายชื่อสถานประกอบการ</a>
            <?php if (hasRole(['admin', 'teacher'])): ?>
                <a href="<?= $bp ?>companies/add.php" class="submenu-item <?= $activePage === 'companies_add' ? 'active' : '' ?>">เพิ่มสถานประกอบการ</a>
            <?php endif; ?>
            <a href="<?= $bp ?>companies/map.php" class="submenu-item <?= $activePage === 'companies_map' ? 'active' : '' ?>">แผนที่พิกัดสถานที่ฝึก</a>
        </div>

        <!-- การฝึกงาน -->
        <a href="#" class="nav-link-item has-submenu <?= $activeGroup === 'internships' ? 'active' : '' ?>">
            <span><i class="bi bi-clipboard-check-fill text-success"></i> การฝึกงาน</span>
            <i class="bi bi-chevron-down submenu-arrow" style="font-size:0.8rem;"></i>
        </a>
        <div class="submenu <?= $activeGroup === 'internships' ? 'show' : '' ?>">
            <a href="<?= $bp ?>internships/index.php" class="submenu-item <?= $activePage === 'internships_list' ? 'active' : '' ?>">รายการฝึกงานทั้งหมด</a>
            <a href="<?= $bp ?>internships/index.php?status=กำลังฝึกงาน" class="submenu-item <?= $activePage === 'internships_status' ? 'active' : '' ?>">ติดตามสถานะการฝึกงาน</a>
            <?php if (hasRole('student')): ?>
                <a href="<?= $bp ?>internships/view.php" class="submenu-item <?= $activePage === 'internships_timeline' ? 'active' : '' ?>">ลำดับเวลา (Timeline)</a>
            <?php endif; ?>
        </div>

        <!-- บันทึกฝึกงาน & Attendance -->
        <a href="#" class="nav-link-item has-submenu <?= $activeGroup === 'logs' ? 'active' : '' ?>">
            <span><i class="bi bi-journal-bookmark-fill text-danger"></i> บันทึก & เวลาฝึกงาน</span>
            <i class="bi bi-chevron-down submenu-arrow" style="font-size:0.8rem;"></i>
        </a>
        <div class="submenu <?= $activeGroup === 'logs' ? 'show' : '' ?>">
            <a href="<?= $bp ?>daily-logs/index.php" class="submenu-item <?= $activePage === 'daily_logs' ? 'active' : '' ?>">บันทึกประจำวัน (Daily Log)</a>
            <a href="<?= $bp ?>attendance/index.php" class="submenu-item <?= $activePage === 'attendance' ? 'active' : '' ?>">บันทึกเวลาเข้า-ออกงาน</a>
        </div>

        <!-- การประเมิน -->
        <a href="<?= $bp ?>evaluations/index.php" class="nav-link-item <?= $activePage === 'evaluations' ? 'active' : '' ?>">
            <span><i class="bi bi-star-fill text-warning"></i> ผลการประเมินการฝึกงาน</span>
        </a>

        <!-- นิเทศการฝึกงาน -->
        <a href="<?= $bp ?>supervision/index.php" class="nav-link-item <?= $activePage === 'supervision' ? 'active' : '' ?>">
            <span><i class="bi bi-car-front-fill text-info"></i> บันทึกการนิเทศงาน</span>
        </a>

        <div class="menu-category">การสื่อสารและรายงานผล</div>

        <!-- ประกาศ -->
        <a href="<?= $bp ?>announcements/index.php" class="nav-link-item <?= $activePage === 'announcements' ? 'active' : '' ?>">
            <span><i class="bi bi-megaphone-fill text-primary"></i> ประกาศและข่าวสาร</span>
        </a>

        <!-- การแจ้งเตือน -->
        <a href="<?= $bp ?>notifications/index.php" class="nav-link-item <?= $activePage === 'notifications' ? 'active' : '' ?>">
            <span><i class="bi bi-bell-fill text-warning"></i> การแจ้งเตือน</span>
            <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
                <span class="badge bg-danger rounded-pill px-2" style="font-size:0.75rem;"><?=$unreadCount?></span>
            <?php endif; ?>
        </a>

        <!-- รายงาน -->
        <a href="<?= $bp ?>reports/index.php" class="nav-link-item <?= $activePage === 'reports' ? 'active' : '' ?>">
            <span><i class="bi bi-bar-chart-line-fill text-success"></i> รายงานสถิติและส่งออก</span>
        </a>

        <?php if (hasRole('admin')): ?>
            <!-- ผู้ใช้งาน (Admin Only) -->
            <a href="<?= $bp ?>users/index.php" class="nav-link-item <?= $activePage === 'users' ? 'active' : '' ?>">
                <span><i class="bi bi-people-fill text-secondary"></i> จัดการผู้ใช้งานระบบ</span>
            </a>
        <?php endif; ?>

        <!-- ออกจากระบบ -->
        <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.08);">
            <a href="<?= $bp ?>logout.php" class="nav-link-item text-danger" onclick="return confirm('ยืนยันออกจากระบบ?');">
                <span><i class="bi bi-box-arrow-right text-danger"></i> ออกจากระบบ</span>
            </a>
        </div>
    </div>
</aside>
