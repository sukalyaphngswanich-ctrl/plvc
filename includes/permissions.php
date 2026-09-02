<?php
// ====================================================================
// Role Permission Security & Common Helpers
// Student Internship Management & Tracking System
// ====================================================================

require_once __DIR__ . '/auth.php';

/**
 * Sanitize User Inputs
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format Thai Date (e.g. 05 ส.ค. 2569)
 */
function formatThaiDate($dateStr, $showTime = false) {
    if (!$dateStr || $dateStr == '0000-00-00' || $dateStr == '0000-00-00 00:00:00') {
        return '-';
    }
    $timestamp = strtotime($dateStr);
    $thaiMonths = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
        7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    $day = date('j', $timestamp);
    $month = $thaiMonths[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;

    $formatted = "$day $month $year";
    if ($showTime) {
        $formatted .= " เวลา " . date('H:i', $timestamp) . " น.";
    }
    return $formatted;
}

/**
 * Calculate Progress Metrics from Internship Dates
 */
function calculateInternshipMetrics($startDateStr, $endDateStr) {
    $today = new DateTime();
    $start = new DateTime($startDateStr);
    $end   = new DateTime($endDateStr);

    if ($today < $start) {
        $daysElapsed = 0;
        $totalDays = $start->diff($end)->days + 1;
        $daysRemaining = $totalDays;
        $percentage = 0;
    } else if ($today > $end) {
        $totalDays = $start->diff($end)->days + 1;
        $daysElapsed = $totalDays;
        $daysRemaining = 0;
        $percentage = 100;
    } else {
        $totalDays = $start->diff($end)->days + 1;
        $daysElapsed = $start->diff($today)->days + 1;
        $daysRemaining = max(0, $totalDays - $daysElapsed);
        $percentage = min(100, round(($daysElapsed / $totalDays) * 100));
    }

    return [
        'total_days'     => $totalDays,
        'days_elapsed'   => $daysElapsed,
        'days_remaining' => $daysRemaining,
        'percentage'     => $percentage
    ];
}

/**
 * Returns Tailwind/CSS badge class for Internship/Daily Log statuses
 */
function getStatusBadgeHtml($status) {
    $map = [
        'ปกติ'            => '<span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle-fill me-1"></i> ปกติ</span>',
        'ผ่าน'            => '<span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle-fill me-1"></i> ผ่าน</span>',
        'ฝึกงานเสร็จแล้ว' => '<span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-all me-1"></i> ฝึกงานเสร็จแล้ว</span>',
        'กำลังฝึกงาน'    => '<span class="badge bg-primary-subtle text-primary border border-primary"><i class="bi bi-play-circle-fill me-1"></i> กำลังฝึกงาน</span>',
        'พร้อมฝึกงาน'    => '<span class="badge bg-info-subtle text-info border border-info"><i class="bi bi-info-circle-fill me-1"></i> พร้อมฝึกงาน</span>',
        'รอตรวจสอบ'       => '<span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-clock-history me-1"></i> รอตรวจสอบ</span>',
        'ต้องแก้ไข'       => '<span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> ต้องแก้ไข</span>',
        'มาสาย'           => '<span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-clock me-1"></i> มาสาย</span>',
        'ลา'              => '<span class="badge bg-secondary-subtle text-secondary border border-secondary"><i class="bi bi-journal-text me-1"></i> ลา</span>',
        'ขาด'             => '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-x-circle-fill me-1"></i> ขาด</span>',
        'ไม่ผ่าน'          => '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-x-octagon-fill me-1"></i> ไม่ผ่าน</span>',
        'มีปัญหา'          => '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i> มีปัญหา</span>',
        'ยกเลิก'          => '<span class="badge bg-dark-subtle text-dark border border-dark"><i class="bi bi-dash-circle me-1"></i> ยกเลิก</span>',
        'ยังไม่เริ่มฝึก'   => '<span class="badge bg-secondary-subtle text-secondary border border-secondary"><i class="bi bi-hourglass me-1"></i> ยังไม่เริ่มฝึก</span>',
    ];

    return $map[$status] ?? '<span class="badge bg-light text-dark border">' . htmlspecialchars($status) . '</span>';
}

/**
 * Verify whether current user has authorization to view/manage given student ID
 */
function verifyStudentAccess($student_id) {
    if (hasRole('admin')) return true;

    $user = getCurrentUser();
    $db = getDB();

    if (hasRole('teacher')) {
        $tProfile = $user['profile'];
        if (!$tProfile) return false;
        // Check if teacher is student's advisor
        $stmt = $db->prepare("SELECT id FROM students WHERE id = :sid AND advisor_id = :tid LIMIT 1");
        $stmt->execute([':sid' => $student_id, ':tid' => $tProfile['id']]);
        return $stmt->fetch() !== false;
    }

    if (hasRole('student')) {
        $sProfile = $user['profile'];
        if (!$sProfile) return false;
        return (int)$sProfile['id'] === (int)$student_id;
    }

    return false;
}
?>
