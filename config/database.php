<?php
// ====================================================================
// Configuration & Database Connection File
// Student Internship Management & Tracking System (PLVC - Phitsanulok Vocational College)
// Supports: Standalone SQLite (Default for Render/Docker) & MySQL (InfinityFree / Cloud DB)
// ====================================================================

// Determine database driver: 'sqlite' (default for Render/Docker) or 'mysql'
define('DB_DRIVER', getenv('DB_DRIVER') ?: (getenv('DB_USE_MYSQL') === 'true' ? 'mysql' : 'sqlite'));

// MySQL Settings (when DB_DRIVER == 'mysql')
define('DB_HOST', getenv('DB_HOST') ?: 'sql300.infinityfree.com');
define('DB_USER', getenv('DB_USER') ?: 'if0_42670463');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '0841631269aa');
define('DB_NAME', getenv('DB_NAME') ?: 'if0_42670463_diff');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// SQLite Settings (when DB_DRIVER == 'sqlite')
define('DB_SQLITE_PATH', __DIR__ . '/../database/internship.sqlite');

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            if (DB_DRIVER === 'sqlite') {
                $dbPath = DB_SQLITE_PATH;
                $dbDir = dirname($dbPath);
                if (!is_dir($dbDir)) {
                    @mkdir($dbDir, 0777, true);
                }
                
                // If sqlite file does not exist, auto-initialize
                if (!file_exists($dbPath) || filesize($dbPath) === 0) {
                    require_once __DIR__ . '/../database/init_sqlite.php';
                    $pdo = initSqliteDatabase($dbPath);
                } else {
                    $pdo = new PDO('sqlite:' . $dbPath);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                }

                // Register MySQL compatibility functions in SQLite
                $pdo->sqliteCreateFunction('CONCAT', function(...$args) {
                    return implode('', $args);
                });
                $pdo->sqliteCreateFunction('NOW', function() {
                    return date('Y-m-d H:i:s');
                });
                $pdo->sqliteCreateFunction('CURDATE', function() {
                    return date('Y-m-d');
                });
                $pdo->sqliteCreateFunction('IFNULL', function($a, $b) {
                    return $a !== null ? $a : $b;
                });
                $pdo->sqliteCreateFunction('DATE_FORMAT', function($date, $format) {
                    if (!$date) return null;
                    return date('Y-m-d', strtotime($date));
                });
            } else {
                // MySQL Connection
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                
                if (getenv('DB_SSL') === 'true' || DB_PORT == '4000') {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }

                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (Exception $e) {
            die("<div style='padding:25px; font-family:\"Prompt\", sans-serif; background:#fef2f2; color:#991b1b; border:1px solid #fca5a5; border-radius:12px; margin:30px auto; max-width:650px; box-shadow:0 10px 25px rgba(0,0,0,0.1);'>
                <h3 style='margin:0 0 10px 0; color:#dc2626;'>⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</h3>
                <p style='margin-bottom:12px; font-size:0.95rem; line-height:1.6;'>
                    เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล (Driver: " . htmlspecialchars(DB_DRIVER) . ")
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
