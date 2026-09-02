<?php
/**
 * SQLite Database Initializer for PLVC Internship Management System
 * Automatically generates database/internship.sqlite with full schema & seed data
 */

$dbPath = __DIR__ . '/internship.sqlite';

function initSqliteDatabase($dbPath) {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 1. Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'student',
        email TEXT DEFAULT NULL,
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Teachers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS teachers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        teacher_code TEXT NOT NULL UNIQUE,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        phone TEXT DEFAULT NULL,
        email TEXT DEFAULT NULL,
        department TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Students table
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        student_code TEXT NOT NULL UNIQUE,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        profile_image TEXT DEFAULT NULL,
        class_level TEXT NOT NULL,
        room TEXT NOT NULL,
        department TEXT NOT NULL,
        academic_year TEXT NOT NULL,
        phone TEXT DEFAULT NULL,
        email TEXT DEFAULT NULL,
        address TEXT DEFAULT NULL,
        advisor_id INTEGER DEFAULT NULL,
        internship_status TEXT NOT NULL DEFAULT 'ยังไม่เริ่มฝึก',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Companies table
    $pdo->exec("CREATE TABLE IF NOT EXISTS companies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_name TEXT NOT NULL,
        business_type TEXT NOT NULL,
        description TEXT DEFAULT NULL,
        address TEXT NOT NULL,
        province TEXT NOT NULL,
        district TEXT NOT NULL,
        subdistrict TEXT NOT NULL,
        postal_code TEXT NOT NULL,
        latitude REAL NOT NULL DEFAULT 16.8211000,
        longitude REAL NOT NULL DEFAULT 100.2658000,
        phone TEXT DEFAULT NULL,
        email TEXT DEFAULT NULL,
        website TEXT DEFAULT NULL,
        contact_name TEXT DEFAULT NULL,
        contact_position TEXT DEFAULT NULL,
        contact_phone TEXT DEFAULT NULL,
        contact_email TEXT DEFAULT NULL,
        max_students INTEGER NOT NULL DEFAULT 5,
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 5. Internships table
    $pdo->exec("CREATE TABLE IF NOT EXISTS internships (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL UNIQUE,
        company_id INTEGER NOT NULL,
        advisor_id INTEGER DEFAULT NULL,
        supervisor_name TEXT DEFAULT NULL,
        supervisor_position TEXT DEFAULT NULL,
        supervisor_phone TEXT DEFAULT NULL,
        supervisor_email TEXT DEFAULT NULL,
        position TEXT NOT NULL,
        department TEXT DEFAULT NULL,
        job_description TEXT DEFAULT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        working_hours_per_day REAL NOT NULL DEFAULT 8.00,
        total_hours REAL NOT NULL DEFAULT 320.00,
        status TEXT NOT NULL DEFAULT 'กำลังฝึกงาน',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 6. Daily logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS daily_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        internship_id INTEGER NOT NULL,
        log_date DATE NOT NULL,
        check_in TIME DEFAULT NULL,
        check_out TIME DEFAULT NULL,
        work_description TEXT NOT NULL,
        learning TEXT DEFAULT NULL,
        problem TEXT DEFAULT NULL,
        solution TEXT DEFAULT NULL,
        image TEXT DEFAULT NULL,
        note TEXT DEFAULT NULL,
        status TEXT NOT NULL DEFAULT 'รอตรวจสอบ',
        teacher_comment TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 7. Attendance table
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        internship_id INTEGER NOT NULL,
        attendance_date DATE NOT NULL,
        check_in TIME DEFAULT NULL,
        check_out TIME DEFAULT NULL,
        total_hours REAL NOT NULL DEFAULT 8.00,
        status TEXT NOT NULL DEFAULT 'ปกติ',
        note TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 8. Evaluations table
    $pdo->exec("CREATE TABLE IF NOT EXISTS evaluations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        internship_id INTEGER NOT NULL,
        evaluator_type TEXT NOT NULL DEFAULT 'ครูที่ปรึกษา',
        responsibility_score INTEGER NOT NULL DEFAULT 5,
        punctuality_score INTEGER NOT NULL DEFAULT 5,
        hardworking_score INTEGER NOT NULL DEFAULT 5,
        teamwork_score INTEGER NOT NULL DEFAULT 5,
        communication_score INTEGER NOT NULL DEFAULT 5,
        creativity_score INTEGER NOT NULL DEFAULT 5,
        professional_skill_score INTEGER NOT NULL DEFAULT 5,
        problem_solving_score INTEGER NOT NULL DEFAULT 5,
        etiquette_score INTEGER NOT NULL DEFAULT 5,
        discipline_score INTEGER NOT NULL DEFAULT 5,
        total_score INTEGER NOT NULL DEFAULT 50,
        average_score REAL NOT NULL DEFAULT 100.00,
        result TEXT NOT NULL DEFAULT 'ดีเยี่ยม',
        strength TEXT DEFAULT NULL,
        improvement TEXT DEFAULT NULL,
        suggestion TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 9. Supervision table
    $pdo->exec("CREATE TABLE IF NOT EXISTS supervision (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        company_id INTEGER NOT NULL,
        teacher_id INTEGER NOT NULL,
        visit_date DATE NOT NULL,
        visit_time TIME NOT NULL,
        visit_type TEXT NOT NULL DEFAULT 'นิเทศที่สถานประกอบการ',
        result TEXT NOT NULL,
        problem TEXT DEFAULT NULL,
        recommendation TEXT DEFAULT NULL,
        image TEXT DEFAULT NULL,
        status TEXT NOT NULL DEFAULT 'นิเทศแล้ว',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Alias table for supervision_records if referenced
    $pdo->exec("CREATE TABLE IF NOT EXISTS supervision_records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        company_id INTEGER NOT NULL,
        teacher_id INTEGER NOT NULL,
        visit_date DATE NOT NULL,
        visit_time TIME NOT NULL,
        visit_type TEXT NOT NULL DEFAULT 'นิเทศที่สถานประกอบการ',
        result TEXT NOT NULL,
        problem TEXT DEFAULT NULL,
        recommendation TEXT DEFAULT NULL,
        image TEXT DEFAULT NULL,
        status TEXT NOT NULL DEFAULT 'นิเทศแล้ว',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 10. Announcements table
    $pdo->exec("CREATE TABLE IF NOT EXISTS announcements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        image TEXT DEFAULT NULL,
        created_by INTEGER NOT NULL DEFAULT 1,
        expire_at DATE DEFAULT NULL,
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 11. Notifications table
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        type TEXT NOT NULL DEFAULT 'info',
        link TEXT DEFAULT NULL,
        reference_id INTEGER DEFAULT NULL,
        is_read INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 12. Chat logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        session_id TEXT NOT NULL,
        message_text TEXT NOT NULL,
        reply_text TEXT NOT NULL,
        intent_detected TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Check if seeded
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        // Seed Users
        $pdo->exec("INSERT INTO users (id, username, password, role, email, status) VALUES
            (1, 'admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@plvc.ac.th', 'active'),
            (2, 'teacher1', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'somchai@plvc.ac.th', 'active'),
            (3, 'teacher2', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'supawadee@plvc.ac.th', 'active'),
            (4, 'student1', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'kannika@plvc.ac.th', 'active'),
            (5, 'student2', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'worachit@plvc.ac.th', 'active'),
            (6, 'student3', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'thanakorn@plvc.ac.th', 'active'),
            (7, 'student4', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'nattacha@plvc.ac.th', 'active'),
            (8, 'student5', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'phumiphat@plvc.ac.th', 'active')");

        // Seed Teachers
        $pdo->exec("INSERT INTO teachers (id, user_id, teacher_code, first_name, last_name, department, phone, email) VALUES
            (1, 2, 'T001', 'สมชาย', 'ใจดี', 'เทคโนโลยีสารสนเทศ', '0812345678', 'somchai@plvc.ac.th'),
            (2, 3, 'T002', 'สุภาวดี', 'รักการสอน', 'ดิจิทัลกราฟิก', '0823456789', 'supawadee@plvc.ac.th')");

        // Seed Students
        $pdo->exec("INSERT INTO students (id, user_id, student_code, first_name, last_name, class_level, room, department, academic_year, phone, email, address, advisor_id, internship_status) VALUES
            (1, 4, 'STD6701', 'กรรณิการ์', 'แซ่ลิ้ม', 'ปวส.2', 'สท.2/1', 'เทคโนโลยีสารสนเทศ', '2567', '0811112233', 'kannika@plvc.ac.th', '60 ถ.วังจันทน์ ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000', 1, 'กำลังฝึกงาน'),
            (2, 5, 'STD6702', 'วรชิต', 'ทองแท้', 'ปวส.2', 'สท.2/1', 'เทคโนโลยีสารสนเทศ', '2567', '0822223344', 'worachit@plvc.ac.th', '15/2 ถ.บรมไตรโลกนารถ ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000', 1, 'กำลังฝึกงาน'),
            (3, 6, 'STD6703', 'ธนกร', 'มีสุข', 'ปวส.2', 'สท.2/2', 'เทคโนโลยีสารสนเทศ', '2567', '0843334455', 'thanakorn@plvc.ac.th', '88/9 ถ.สิงหวัฒน์ ต.พลายชุมพล อ.เมือง จ.พิษณุโลก 65000', 1, 'กำลังฝึกงาน'),
            (4, 7, 'STD6704', 'ณัฐชา', 'บุญมี', 'ปวส.2', 'ดก.2/1', 'ดิจิทัลกราฟิก', '2567', '0854445566', 'nattacha@plvc.ac.th', '99/1 ถ.มิตรภาพ ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000', 2, 'มีปัญหา'),
            (5, 8, 'STD6705', 'ภูมิพัฒน์', 'คงดี', 'ปวส.2', 'บช.2/1', 'การบัญชี', '2567', '0865556677', 'phumiphat@plvc.ac.th', '12/3 ถ.สนามบิน ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000', 2, 'ฝึกงานเสร็จแล้ว')");

        // Seed Companies
        $pdo->exec("INSERT INTO companies (id, company_name, business_type, description, address, province, district, subdistrict, postal_code, latitude, longitude, phone, email, website, contact_name, contact_position, contact_phone, contact_email) VALUES
            (1, 'บริษัท พิษณุโลก ซอฟต์แวร์ โซลูชั่น จำกัด', 'พัฒนาซอฟต์แวร์และไอที', 'บริษัทผู้นำด้านการพัฒนาเว็บแอปพลิเคชันและโมบายแอปพลิเคชันในภาคเหนือตอนล่าง', '99/9 ถ.สิงหวัฒน์ ต.พลายชุมพล', 'พิษณุโลก', 'เมืองพิษณุโลก', 'พลายชุมพล', '65000', 16.8211000, 100.2658000, '055123456', 'contact@plksoft.co.th', 'https://www.plksoft.co.th', 'คุณวิชัย เทคโนโลยี', 'HR Manager', '0812345678', 'vichai@plksoft.co.th'),
            (2, 'บริษัท นเรศวร ดิจิทัล เอเจนซี่ จำกัด', 'ดิจิทัลมาร์เก็ตติ้งและมีเดีย', 'ผู้ให้บริการโซลูชันด้านการตลาดดิจิทัลและกราฟิกดีไซน์', '15/2 ถ.บรมไตรโลกนารถ ต.ในเมือง', 'พิษณุโลก', 'เมืองพิษณุโลก', 'ในเมือง', '65000', 16.8154000, 100.2612000, '055654321', 'info@naresuanmedia.com', 'https://www.naresuanmedia.com', 'คุณสมศักดิ์ ดิจิทัล', 'Managing Director', '0828887777', 'somsak@naresuanmedia.com'),
            (3, 'บริษัท ไอที โกลบอล อินโนเวชั่น จำกัด', 'ออกแบบและพัฒนาระบบเครือข่าย', 'บริการติดตั้งระบบเครือข่ายและพัฒนาโซลูชันไอทีสำหรับองค์กร', '60 ถ.วังจันทน์ ต.ในเมือง', 'พิษณุโลก', 'เมืองพิษณุโลก', 'ในเมือง', '65000', 16.8285000, 100.2635000, '055987654', 'hello@itglobal.co.th', 'https://www.itglobal.co.th', 'คุณนภา สุขใส', 'Lead Designer', '0837776666', 'napa@itglobal.co.th'),
            (4, 'ศูนย์บริการคอมพิวเตอร์และระบบสารสนเทศพิษณุโลก', 'ซ่อมบำรุงและดูแลระบบไอที', 'ศูนย์บริการและติดตั้งระบบคอมพิวเตอร์และเครือข่ายครบวงจร', '88/5 ถ.มิตรภาพ ต.ในเมือง', 'พิษณุโลก', 'เมืองพิษณุโลก', 'ในเมือง', '65000', 16.8235000, 100.2710000, '055333444', 'support@plkcomp.co.th', 'https://www.plkcomp.co.th', 'คุณเกรียงไกร ขยัน', 'IT Director', '0846665555', 'kriengkrai@plkcomp.co.th'),
            (5, 'สตูดิโอดิจิทัลมาร์เก็ตติ้งแอนด์ดีไซน์ พิษณุโลก', 'การตลาดออนไลน์และกราฟิก', 'เอเจนซี่โฆษณาออนไลน์และสร้างสรรค์คอนเทนต์ดิจิทัล', '12/3 ถ.สนามบิน ต.ในเมือง', 'พิษณุโลก', 'เมืองพิษณุโลก', 'ในเมือง', '65000', 16.7850000, 100.2780000, '055222333', 'contact@plkstudio.com', 'https://www.plkstudio.com', 'คุณวรวุฒิ มีไอเดีย', 'Creative Director', '0855554444', 'worawut@plkstudio.com')");

        // Seed Internships
        $pdo->exec("INSERT INTO internships (id, student_id, company_id, advisor_id, supervisor_name, supervisor_position, supervisor_phone, supervisor_email, position, department, job_description, start_date, end_date, working_hours_per_day, total_hours, status) VALUES
            (1, 1, 1, 1, 'คุณประเสริฐ งานดี', 'Senior Full-Stack Developer', '0891234567', 'prasert@plksoft.co.th', 'Full-Stack Developer Intern', 'พัฒนาซอฟต์แวร์', 'เขียนโค้ดพัฒนาเว็บด้วย PHP/MySQL และ Tailwind CSS', '2026-08-01', '2026-09-30', 8.00, 320.00, 'กำลังฝึกงาน'),
            (2, 2, 2, 1, 'คุณชนิดา ไบรท์', 'Digital Content Lead', '0892345678', 'chanida@naresuanmedia.com', 'Web Marketing Intern', 'การตลาดดิจิทัล', 'ดูแล UX/UI เว็บไซต์และวิเคราะห์ทราฟฟิกด้วย Google Analytics', '2026-08-01', '2026-09-30', 8.00, 320.00, 'กำลังฝึกงาน'),
            (3, 3, 3, 2, 'คุณธีระ พัฒนา', 'Front-End Lead', '0893456789', 'theera@itglobal.co.th', 'Front-End Developer Intern', 'ไอทีและดีไซน์', 'พัฒนา UI คอนโพเนนต์และปรับปรุง Responsive Design', '2026-08-01', '2026-09-30', 8.00, 320.00, 'กำลังฝึกงาน'),
            (4, 4, 4, 1, 'คุณอนุชา สายช่าง', 'Network Admin', '0894567890', 'anucha@plkcomp.co.th', 'System Support Intern', 'ดูแลระบบ', 'บำรุงรักษาเครื่องคอมพิวเตอร์และดูแลระบบ LAN ในองค์กร', '2026-08-01', '2026-09-30', 8.00, 320.00, 'มีปัญหา'),
            (5, 5, 5, 2, 'คุณกมลวรรณ ชาญฉลาด', 'Media Specialist', '0895678901', 'kamonwan@plkstudio.com', 'Graphic & Web Intern', 'สื่อดิจิทัล', 'ออกแบบแบนเนอร์และกราฟิกสำหรับเว็บไซต์', '2026-06-01', '2026-07-31', 8.00, 320.00, 'ฝึกงานเสร็จแล้ว')");

        // Seed Daily Logs
        $pdo->exec("INSERT INTO daily_logs (id, student_id, internship_id, log_date, check_in, check_out, work_description, learning, problem, solution, status, teacher_comment) VALUES
            (1, 1, 1, '2026-08-01', '08:25:00', '17:30:00', 'ปฐมนิเทศและติดตั้งสภาพแวดล้อมระบบการทำงาน VS Code, Docker, Git', 'เรียนรู้โครงสร้างโปรเจกต์ของบริษัทและขั้นตอน Workflow', 'มีปัญหาในการตั้งค่า Environment เล็กน้อย', 'พี่เลี้ยงช่วยแนะนำแก้ไขไฟล์ configuration', 'ผ่าน', 'ดีมากครับ เริ่มต้นได้สมบูรณ์'),
            (2, 1, 1, '2026-08-04', '08:20:00', '17:25:00', 'ออกแบบหน้าจอ Dashboard สำหรับผู้ใช้งานระบบบริหารจัดการ', 'ได้เรียนรู้ CSS Flexbox และ Grid System', 'ไม่มีปัญหา', 'ดำเนินงานได้เรียบร้อย', 'ผ่าน', 'การออกแบบสวยงามและเป็นระบบดี'),
            (3, 1, 1, '2026-08-05', '08:28:00', '17:35:00', 'เขียนคำสั่ง SQL Query และ PDO Prepared Statements เชื่อมต่อฐานข้อมูล', 'เข้าใจเรื่อง SQL Injection Prevention และ Session Guarding', 'Query ซับซ้อนใช้เวลานานในการ debug', 'ใช้คำสั่ง EXPLAIN วิเคราะห์ดัชนี Index', 'รอตรวจสอบ', NULL),
            (4, 2, 2, '2026-08-01', '08:30:00', '17:30:00', 'รับมอบหมายงานวิเคราะห์ Keyword และ SEO On-page', 'เข้าใจโครงสร้าง Meta Title และ Heading tags', 'ไม่มี', 'ผ่านไปด้วยดี', 'ผ่าน', 'ตั้งใจทำงานดีมาก')");

        // Seed Attendance
        $pdo->exec("INSERT INTO attendance (id, student_id, internship_id, attendance_date, check_in, check_out, total_hours, status, note) VALUES
            (1, 1, 1, '2026-08-01', '08:25:00', '17:30:00', 8.00, 'ปกติ', 'เข้างานตรงเวลา'),
            (2, 1, 1, '2026-08-04', '08:20:00', '17:25:00', 8.00, 'ปกติ', 'เข้างานตรงเวลา'),
            (3, 1, 1, '2026-08-05', '08:28:00', '17:35:00', 8.00, 'ปกติ', 'เข้างานตรงเวลา'),
            (4, 2, 2, '2026-08-01', '08:30:00', '17:30:00', 8.00, 'ปกติ', 'เข้างานตรงเวลา'),
            (5, 3, 3, '2026-08-01', '08:45:00', '17:30:00', 7.75, 'มาสาย', 'การจราจรติดขัด'),
            (6, 4, 4, '2026-08-01', '08:15:00', '17:30:00', 8.00, 'ปกติ', 'เข้างานตรงเวลา')");

        // Seed Evaluations
        $pdo->exec("INSERT INTO evaluations (id, student_id, internship_id, evaluator_type, responsibility_score, punctuality_score, hardworking_score, teamwork_score, communication_score, creativity_score, professional_skill_score, problem_solving_score, etiquette_score, discipline_score, total_score, average_score, result, strength, improvement, suggestion) VALUES
            (1, 5, 5, 'ครูที่ปรึกษา', 5, 5, 5, 5, 4, 4, 5, 5, 5, 5, 48, 96.00, 'ดีเยี่ยม', 'มีความรับผิดชอบสูง ตรงต่อเวลามาก', 'เพิ่มความมั่นใจในการนำเสนองาน', 'ส่งเสริมให้เรียนรู้เทคโนโลยีใหม่ๆ เพิ่มเติม')");

        // Seed Supervision
        $pdo->exec("INSERT INTO supervision (id, student_id, company_id, teacher_id, visit_date, visit_time, visit_type, result, problem, recommendation, status) VALUES
            (1, 1, 1, 1, '2026-08-15', '10:30:00', 'นิเทศที่สถานประกอบการ', 'นักศึกษาปฏิบัติตามกฎระเบียบของบริษัทได้ดีมาก พี่เลี้ยงชื่นชมในความตั้งใจและการเรียนรู้ที่รวดเร็ว', 'ไม่มีปัญหา', 'แนะนำให้นักศึกษาบันทึกความก้าวหน้าของโครงงานอย่างต่อเนื่อง', 'นิเทศแล้ว'),
            (2, 2, 2, 1, '2026-08-18', '13:30:00', 'นิเทศที่สถานประกอบการ', 'นักศึกษาทำงานออกแบบกราฟิกและสื่อดิจิทัลได้ตรงตามความต้องการของสถานประกอบการ', 'ไม่มี', 'ให้ฝึกฝนการใช้เครื่องมือใหม่ๆ เพิ่มเติม', 'นิเทศแล้ว')");

        $pdo->exec("INSERT INTO supervision_records (id, student_id, company_id, teacher_id, visit_date, visit_time, visit_type, result, problem, recommendation, status) VALUES
            (1, 1, 1, 1, '2026-08-15', '10:30:00', 'นิเทศที่สถานประกอบการ', 'นักศึกษาปฏิบัติตามกฎระเบียบของบริษัทได้ดีมาก พี่เลี้ยงชื่นชมในความตั้งใจและการเรียนรู้ที่รวดเร็ว', 'ไม่มีปัญหา', 'แนะนำให้นักศึกษาบันทึกความก้าวหน้าของโครงงานอย่างต่อเนื่อง', 'นิเทศแล้ว'),
            (2, 2, 2, 1, '2026-08-18', '13:30:00', 'นิเทศที่สถานประกอบการ', 'นักศึกษาทำงานออกแบบกราฟิกและสื่อดิจิทัลได้ตรงตามความต้องการของสถานประกอบการ', 'ไม่มี', 'ให้ฝึกฝนการใช้เครื่องมือใหม่ๆ เพิ่มเติม', 'นิเทศแล้ว')");

        // Seed Announcements
        $pdo->exec("INSERT INTO announcements (id, title, content, created_by, status) VALUES
            (1, 'กำหนดการส่งสมุดบันทึกการฝึกงาน ประจำภาคเรียนที่ 1/2569', 'ขอให้นักศึกษาทุกคนส่งบันทึกการฝึกงานทุกวันศุกร์ และตรวจทานลายเซ็นพี่เลี้ยงให้ครบถ้วนก่อนส่ง', 1, 'active'),
            (2, 'ประชาสัมพันธ์การนิเทศก์งานรอบที่ 1', 'อาจารย์นิเทศก์จะเริ่มลงพื้นที่ตรวจเยี่ยมสถานประกอบการตั้งแต่วันที่ 15 สิงหาคม 2569 เป็นต้นไป', 1, 'active')");

        // Seed Notifications
        $pdo->exec("INSERT INTO notifications (id, user_id, title, message, type, link, is_read) VALUES
            (1, 4, 'บันทึกการฝึกงานผ่านการตรวจสอบ', 'อาจารย์สมชาย ใจดี ได้ตรวจสอบและอนุมัติบันทึกประจำวันที่ 01/08/2569 แล้ว', 'success', '/daily-logs/view.php?id=1', 0),
            (2, 4, 'มีประกาศใหม่จากระบบ', 'กำหนดการส่งสมุดบันทึกการฝึกงาน ประจำภาคเรียนที่ 1/2569', 'info', '/announcements/view.php?id=1', 1)");
    }

    return $pdo;
}

if (php_sapi_name() === 'cli' || basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'init_sqlite.php') {
    initSqliteDatabase($dbPath);
    echo "SQLite database successfully initialized at: " . realpath($dbPath) . "\n";
}
