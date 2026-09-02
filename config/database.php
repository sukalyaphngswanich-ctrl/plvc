<?php
// ====================================================================
// Configuration & Database Connection File
// Student Internship Management & Tracking System (InfinityFree Deployment)
// ====================================================================

define('DB_HOST', 'sql301.infinityfree.com');
define('DB_USER', 'if0_42607705');
define('DB_PASS', '0841631269aa');
define('DB_NAME', 'if0_42607705_if0_42607705');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("<div style='padding:25px; font-family:\"Prompt\", sans-serif; background:#fef2f2; color:#991b1b; border:1px solid #fca5a5; border-radius:12px; margin:30px auto; max-width:650px; box-shadow:0 10px 25px rgba(0,0,0,0.1);'>
                <h3 style='margin:0 0 10px 0; color:#dc2626;'>⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</h3>
                <p style='margin-bottom:12px; font-size:0.95rem; line-height:1.6;'>
                    กรุณาตรวจสอบว่าค่าเชื่อมต่อใน <code>config/database.php</code> บนโฮสติ้งตรงกับข้อมูล MySQL ของคุณ
                </p>
                <small style='color:#7f1d1d;'><strong>Error details:</strong> " . htmlspecialchars($e->getMessage()) . "</small>
            </div>");
        }
    }
    return $pdo;
}

// Global App Settings (วิทยาลัยอาชีวศึกษาพิษณุโลก)
define('SITE_NAME', 'ระบบจัดการและติดตามการฝึกงาน - วิทยาลัยอาชีวศึกษาพิษณุโลก');
define('SITE_NAME_EN', 'Student Internship Management - Phitsanulok Vocational College');
define('COLLEGE_NAME', 'วิทยาลัยอาชีวศึกษาพิษณุโลก');
define('COLLEGE_NAME_EN', 'Phitsanulok Vocational College');
define('COLLEGE_ADDRESS', 'เลขที่ 60 ถนนวังจันทน์ ตำบลในเมือง อำเภอเมือง จังหวัดพิษณุโลก 65000');
define('COLLEGE_PHONE', '055-258570');
define('COLLEGE_URL', 'https://www.plvc.ac.th');
?>
