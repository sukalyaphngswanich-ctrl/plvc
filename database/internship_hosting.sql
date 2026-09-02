-- ====================================================================
-- Database: Student Internship Management & Tracking System
-- (ระบบจัดการและติดตามการฝึกงานของนักศึกษา)
-- ====================================================================


-- Drop existing tables cleanly (Reverse dependency order to prevent FK error #1451)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `supervision`;
DROP TABLE IF EXISTS `evaluations`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `daily_logs`;
DROP TABLE IF EXISTS `internships`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `teachers`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------
-- 1. Table: users
-- --------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'teacher', 'student', 'company') NOT NULL DEFAULT 'student',
  `email` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. Table: teachers
-- --------------------------------------------------------------------
CREATE TABLE `teachers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `teacher_code` VARCHAR(20) NOT NULL UNIQUE,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `department` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_teachers_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. Table: students
-- --------------------------------------------------------------------
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `student_code` VARCHAR(20) NOT NULL UNIQUE,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `class_level` VARCHAR(50) NOT NULL, -- e.g., ปวส.2
  `room` VARCHAR(20) NOT NULL,        -- e.g., สท.2/1
  `department` VARCHAR(100) NOT NULL,  -- e.g., เทคโนโลยีสารสนเทศ
  `academic_year` VARCHAR(10) NOT NULL, -- e.g., 2567
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `advisor_id` INT DEFAULT NULL,
  `internship_status` ENUM('ยังไม่เริ่มฝึก', 'กำลังฝึกงาน', 'ฝึกงานเสร็จแล้ว', 'มีปัญหา', 'ยกเลิก') NOT NULL DEFAULT 'ยังไม่เริ่มฝึก',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_students_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_students_advisor` FOREIGN KEY (`advisor_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  INDEX `idx_student_code` (`student_code`),
  INDEX `idx_internship_status` (`internship_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. Table: companies
-- --------------------------------------------------------------------
CREATE TABLE `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_name` VARCHAR(200) NOT NULL,
  `business_type` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `address` TEXT NOT NULL,
  `province` VARCHAR(100) NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `subdistrict` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(10) NOT NULL,
  `latitude` DECIMAL(10, 7) NOT NULL DEFAULT 16.8211000,
  `longitude` DECIMAL(10, 7) NOT NULL DEFAULT 100.2658000,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `website` VARCHAR(150) DEFAULT NULL,
  `contact_name` VARCHAR(100) DEFAULT NULL,
  `contact_position` VARCHAR(100) DEFAULT NULL,
  `contact_phone` VARCHAR(20) DEFAULT NULL,
  `contact_email` VARCHAR(100) DEFAULT NULL,
  `max_students` INT NOT NULL DEFAULT 5,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_company_province` (`province`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. Table: internships
-- --------------------------------------------------------------------
CREATE TABLE `internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL UNIQUE,
  `company_id` INT NOT NULL,
  `advisor_id` INT DEFAULT NULL,
  `supervisor_name` VARCHAR(100) DEFAULT NULL,
  `supervisor_position` VARCHAR(100) DEFAULT NULL,
  `supervisor_phone` VARCHAR(20) DEFAULT NULL,
  `supervisor_email` VARCHAR(100) DEFAULT NULL,
  `position` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `job_description` TEXT DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `working_hours_per_day` DECIMAL(4, 2) NOT NULL DEFAULT 8.00,
  `total_hours` DECIMAL(6, 2) NOT NULL DEFAULT 320.00,
  `status` ENUM('รอจัดสถานที่', 'รอยืนยัน', 'พร้อมฝึกงาน', 'กำลังฝึกงาน', 'ใกล้สิ้นสุด', 'ฝึกงานเสร็จแล้ว', 'มีปัญหา', 'ยกเลิก') NOT NULL DEFAULT 'กำลังฝึกงาน',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_internships_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_internships_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_internships_advisor` FOREIGN KEY (`advisor_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  INDEX `idx_internship_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. Table: daily_logs
-- --------------------------------------------------------------------
CREATE TABLE `daily_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `internship_id` INT NOT NULL,
  `log_date` DATE NOT NULL,
  `check_in` TIME DEFAULT NULL,
  `check_out` TIME DEFAULT NULL,
  `work_description` TEXT NOT NULL,
  `learning` TEXT DEFAULT NULL,
  `problem` TEXT DEFAULT NULL,
  `solution` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `status` ENUM('รอตรวจสอบ', 'ผ่าน', 'ไม่ผ่าน', 'ต้องแก้ไข') NOT NULL DEFAULT 'รอตรวจสอบ',
  `teacher_comment` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_daily_logs_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_daily_logs_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_student_log_date` (`student_id`, `log_date`),
  INDEX `idx_log_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 7. Table: attendance
-- --------------------------------------------------------------------
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `internship_id` INT NOT NULL,
  `attendance_date` DATE NOT NULL,
  `check_in` TIME DEFAULT NULL,
  `check_out` TIME DEFAULT NULL,
  `total_hours` DECIMAL(4, 2) NOT NULL DEFAULT 8.00,
  `status` ENUM('ปกติ', 'มาสาย', 'ขาด', 'ลา', 'ออกก่อนเวลา') NOT NULL DEFAULT 'ปกติ',
  `note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attendance_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_student_attendance_date` (`student_id`, `attendance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 8. Table: evaluations
-- --------------------------------------------------------------------
CREATE TABLE `evaluations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `internship_id` INT NOT NULL,
  `evaluator_type` ENUM('ครูที่ปรึกษา', 'สถานประกอบการ') NOT NULL DEFAULT 'ครูที่ปรึกษา',
  `responsibility_score` TINYINT NOT NULL DEFAULT 5, -- 1-5
  `punctuality_score` TINYINT NOT NULL DEFAULT 5,
  `hardworking_score` TINYINT NOT NULL DEFAULT 5,
  `teamwork_score` TINYINT NOT NULL DEFAULT 5,
  `communication_score` TINYINT NOT NULL DEFAULT 5,
  `creativity_score` TINYINT NOT NULL DEFAULT 5,
  `professional_skill_score` TINYINT NOT NULL DEFAULT 5,
  `problem_solving_score` TINYINT NOT NULL DEFAULT 5,
  `etiquette_score` TINYINT NOT NULL DEFAULT 5,
  `discipline_score` TINYINT NOT NULL DEFAULT 5,
  `total_score` INT NOT NULL DEFAULT 50, -- 10-50
  `average_score` DECIMAL(5, 2) NOT NULL DEFAULT 100.00, -- 0-100%
  `result` VARCHAR(50) NOT NULL DEFAULT 'ดีเยี่ยม',
  `strength` TEXT DEFAULT NULL,
  `improvement` TEXT DEFAULT NULL,
  `suggestion` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_evaluations_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_evaluations_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 9. Table: supervision
-- --------------------------------------------------------------------
CREATE TABLE `supervision` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `company_id` INT NOT NULL,
  `teacher_id` INT NOT NULL,
  `visit_date` DATE NOT NULL,
  `visit_time` TIME NOT NULL,
  `visit_type` ENUM('นิเทศที่สถานประกอบการ', 'นิเทศออนไลน์', 'โทรศัพท์', 'อื่น ๆ') NOT NULL DEFAULT 'นิเทศที่สถานประกอบการ',
  `result` TEXT NOT NULL,
  `problem` TEXT DEFAULT NULL,
  `recommendation` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('ยังไม่นิเทศ', 'นัดหมายแล้ว', 'นิเทศแล้ว', 'ต้องติดตามเพิ่มเติม') NOT NULL DEFAULT 'นิเทศแล้ว',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_supervision_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_supervision_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_supervision_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 10. Table: announcements
-- --------------------------------------------------------------------
CREATE TABLE `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `created_by` INT NOT NULL,
  `expire_at` DATE DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_announcements_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 11. Table: notifications
-- --------------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT 'info', -- info, warning, success, error
  `link` VARCHAR(255) DEFAULT NULL,
  `reference_id` INT DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- MOCK DATA INGESTION
-- Passwords hash generated for '123456':
-- $2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a
-- (The auth layer checks bcrypt hash OR fallbacks gracefully)
-- ====================================================================

-- 1. Users (Admin, 2 Teachers, 5 Students)
INSERT INTO `users` (`id`, `username`, `password`, `role`, `status`) VALUES
(1, 'admin', '$2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a', 'admin', 'active'),
(2, 'teacher1', '$2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a', 'teacher', 'active'),
(3, 'teacher2', '$2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a', 'teacher', 'active'),
(4, 'student1', '$2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a', 'student', 'active'),
(5, 'student2', '$2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a', 'student', 'active'),
(6, 'student3', '$2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a', 'student', 'active'),
(7, 'student4', '$2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a', 'student', 'active'),
(8, 'student5', '$2y$10$4.Tly7eD9xR4gOqB6FkI1uVw3h/P2qYV6d.oT4m3Zg1WnO0W6a', 'student', 'active');

-- 2. Teachers
INSERT INTO `teachers` (`id`, `user_id`, `teacher_code`, `first_name`, `last_name`, `phone`, `email`, `department`) VALUES
(1, 2, 'T001', 'สมชาย', 'ใจดี', '0812345678', 'somchai@plvc.ac.th', 'เทคโนโลยีสารสนเทศ'),
(2, 3, 'T002', 'สุภาวดี', 'แก้วใส', '0898765432', 'supawadee@plvc.ac.th', 'ดิจิทัลกราฟิก');

-- 3. Students
INSERT INTO `students` (`id`, `user_id`, `student_code`, `first_name`, `last_name`, `class_level`, `room`, `department`, `academic_year`, `phone`, `email`, `address`, `advisor_id`, `internship_status`) VALUES
(1, 4, 'STD6701', 'สิทธิกร', 'พงษ์วานิช', 'ปวส.2', 'สท.2/1', 'เทคโนโลยีสารสนเทศ', '2567', '0821112233', 'sittigon@plvc.ac.th', '60 ถ.วังจันทน์ ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000', 1, 'กำลังฝึกงาน'),
(2, 5, 'STD6702', 'พิมพ์ชนก', 'แสงทอง', 'ปวส.2', 'สท.2/1', 'เทคโนโลยีสารสนเทศ', '2567', '0832223344', 'pimchanok@plvc.ac.th', '15/2 ถ.บรมไตรโลกนารถ ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000', 1, 'กำลังฝึกงาน'),
(3, 6, 'STD6703', 'ธนกร', 'มีสุข', 'ปวส.2', 'สท.2/2', 'เทคโนโลยีสารสนเทศ', '2567', '0843334455', 'thanakorn@plvc.ac.th', '88/9 ถ.สิงหวัฒน์ ต.พลายชุมพล อ.เมือง จ.พิษณุโลก 65000', 1, 'กำลังฝึกงาน'),
(4, 7, 'STD6704', 'ณัฐชา', 'บุญมี', 'ปวส.2', 'ดก.2/1', 'ดิจิทัลกราฟิก', '2567', '0854445566', 'nattacha@plvc.ac.th', '99/1 ถ.มิตรภาพ ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000', 2, 'มีปัญหา'),
(5, 8, 'STD6705', 'ภูมิพัฒน์', 'คงดี', 'ปวส.2', 'บช.2/1', 'การบัญชี', '2567', '0865556677', 'phumiphat@plvc.ac.th', '12/3 ถ.สนามบิน ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000', 2, 'ฝึกงานเสร็จแล้ว');

-- 4. Companies
INSERT INTO `companies` (`id`, `company_name`, `business_type`, `description`, `address`, `province`, `district`, `subdistrict`, `postal_code`, `latitude`, `longitude`, `phone`, `email`, `website`, `contact_name`, `contact_position`, `contact_phone`, `contact_email`) VALUES
(1, 'บริษัท พิษณุโลก ซอฟต์แวร์ โซลูชั่น จำกัด', 'พัฒนาซอฟต์แวร์และไอที', 'บริษัทผู้นำด้านการพัฒนาเว็บแอปพลิเคชันและโมบายแอปพลิเคชันในภาคเหนือตอนล่าง', '99/9 ถ.สิงหวัฒน์ ต.พลายชุมพล', 'พิษณุโลก', 'เมืองพิษณุโลก', 'พลายชุมพล', '65000', 16.8211000, 100.2658000, '055123456', 'contact@plksoft.co.th', 'https://www.plksoft.co.th', 'คุณวิชัย เทคโนโลยี', 'HR Manager', '0812345678', 'vichai@plksoft.co.th'),
(2, 'บริษัท นเรศวร ดิจิทัล เอเจนซี่ จำกัด', 'ดิจิทัลมาร์เก็ตติ้งและมีเดีย', 'ผู้ให้บริการโซลูชันด้านการตลาดดิจิทัลและกราฟิกดีไซน์', '15/2 ถ.บรมไตรโลกนารถ ต.ในเมือง', 'พิษณุโลก', 'เมืองพิษณุโลก', 'ในเมือง', '65000', 16.8154000, 100.2612000, '055654321', 'info@naresuanmedia.com', 'https://www.naresuanmedia.com', 'คุณสมศักดิ์ ดิจิทัล', 'Managing Director', '0828887777', 'somsak@naresuanmedia.com'),
(3, 'บริษัท ไอที โกลบอล อินโนเวชั่น จำกัด', 'ออกแบบและพัฒนาระบบเครือข่าย', 'บริการติดตั้งระบบเครือข่ายและพัฒนาโซลูชันไอทีสำหรับองค์กร', '60 ถ.วังจันทน์ ต.ในเมือง', 'พิษณุโลก', 'เมืองพิษณุโลก', 'ในเมือง', '65000', 16.8285000, 100.2635000, '055987654', 'hello@itglobal.co.th', 'https://www.itglobal.co.th', 'คุณนภา สุขใส', 'Lead Designer', '0837776666', 'napa@itglobal.co.th'),
(4, 'ศูนย์บริการคอมพิวเตอร์และระบบสารสนเทศพิษณุโลก', 'ซ่อมบำรุงและดูแลระบบไอที', 'ศูนย์บริการและติดตั้งระบบคอมพิวเตอร์และเครือข่ายครบวงจร', '88/5 ถ.มิตรภาพ ต.ในเมือง', 'พิษณุโลก', 'เมืองพิษณุโลก', 'ในเมือง', '65000', 16.8235000, 100.2710000, '055333444', 'support@plkcomp.co.th', 'https://www.plkcomp.co.th', 'คุณเกรียงไกร ขยัน', 'IT Director', '0846665555', 'kriengkrai@plkcomp.co.th'),
(5, 'สตูดิโอดิจิทัลมาร์เก็ตติ้งแอนด์ดีไซน์ พิษณุโลก', 'การตลาดออนไลน์และกราฟิก', 'เอเจนซี่โฆษณาออนไลน์และสร้างสรรค์คอนเทนต์ดิจิทัล', '12/3 ถ.สนามบิน ต.ในเมือง', 'พิษณุโลก', 'เมืองพิษณุโลก', 'ในเมือง', '65000', 16.7850000, 100.2780000, '055222333', 'contact@plkstudio.com', 'https://www.plkstudio.com', 'คุณวรวุฒิ มีไอเดีย', 'Creative Director', '0855554444', 'worawut@plkstudio.com');

-- 5. Internships
INSERT INTO `internships` (`id`, `student_id`, `company_id`, `advisor_id`, `supervisor_name`, `supervisor_position`, `supervisor_phone`, `supervisor_email`, `position`, `department`, `job_description`, `start_date`, `end_date`, `working_hours_per_day`, `total_hours`, `status`) VALUES
(1, 1, 1, 1, 'คุณประเสริฐ งานดี', 'Senior Full-Stack Developer', '0891234567', 'prasert@plksoft.co.th', 'Full-Stack Developer Intern', 'พัฒนาซอฟต์แวร์', 'เขียนโค้ดพัฒนาเว็บด้วย PHP/MySQL และ Tailwind CSS', '2026-08-01', '2026-09-30', 8.00, 320.00, 'กำลังฝึกงาน'),
(2, 2, 2, 1, 'คุณชนิดา ไบรท์', 'Digital Content Lead', '0892345678', 'chanida@naresuanmedia.com', 'Web Marketing Intern', 'การตลาดดิจิทัล', 'ดูแล UX/UI เว็บไซต์และวิเคราะห์ทราฟฟิกด้วย Google Analytics', '2026-08-01', '2026-09-30', 8.00, 320.00, 'กำลังฝึกงาน'),
(3, 3, 3, 2, 'คุณธีระ พัฒนา', 'Front-End Lead', '0893456789', 'theera@itglobal.co.th', 'Front-End Developer Intern', 'ไอทีและดีไซน์', 'พัฒนา UI คอนโพเนนต์และปรับปรุง Responsive Design', '2026-08-01', '2026-09-30', 8.00, 320.00, 'กำลังฝึกงาน'),
(4, 4, 4, 1, 'คุณอนุชา สายช่าง', 'Network Admin', '0894567890', 'anucha@plkcomp.co.th', 'System Support Intern', 'ดูแลระบบ', 'บำรุงรักษาเครื่องคอมพิวเตอร์และดูแลระบบ LAN ในองค์กร', '2026-08-01', '2026-09-30', 8.00, 320.00, 'มีปัญหา'),
(5, 5, 5, 2, 'คุณกมลวรรณ ชาญฉลาด', 'Media Specialist', '0895678901', 'kamonwan@plkstudio.com', 'Graphic & Web Intern', 'สื่อดิจิทัล', 'ออกแบบแบนเนอร์และกราฟิกสำหรับเว็บไซต์', '2026-06-01', '2026-07-31', 8.00, 320.00, 'ฝึกงานเสร็จแล้ว');

-- 6. Daily Logs
INSERT INTO `daily_logs` (`id`, `student_id`, `internship_id`, `log_date`, `check_in`, `check_out`, `work_description`, `learning`, `problem`, `solution`, `status`, `teacher_comment`) VALUES
(1, 1, 1, '2026-08-01', '08:25:00', '17:30:00', 'ปฐมนิเทศและติดตั้งสภาพแวดล้อมระบบการทำงาน VS Code, Docker, Git', 'เรียนรู้โครงสร้างโปรเจกต์ของบริษัทและขั้นตอน Workflow', 'มีปัญหาในการตั้งค่า Environment เล็กน้อย', 'พี่เลี้ยงช่วยแนะนำแก้ไขไฟล์ configuration', 'ผ่าน', 'ดีมากครับ เริ่มต้นได้สมบูรณ์'),
(2, 1, 1, '2026-08-04', '08:20:00', '17:25:00', 'ออกแบบหน้าจอ Dashboard สำหรับผู้ใช้งานระบบบริหารจัดการ', 'ได้เรียนรู้ CSS Flexbox และ Grid System', 'ไม่มีปัญหา', 'ดำเนินงานได้เรียบร้อย', 'ผ่าน', 'การออกแบบสวยงามและเป็นระบบดี'),
(3, 1, 1, '2026-08-05', '08:28:00', '17:35:00', 'เขียนคำสั่ง SQL Query และ PDO Prepared Statements เชื่อมต่อฐานข้อมูล', 'เข้าใจเรื่อง SQL Injection Prevention และ Session Guarding', 'Query ซับซ้อนใช้เวลานานในการ debug', 'ใช้คำสั่ง EXPLAIN วิเคราะห์ดัชนี Index', 'รอตรวจสอบ', NULL),
(4, 2, 2, '2026-08-01', '08:30:00', '17:30:00', 'รับมอบหมายงานวิเคราะห์ Keyword และ SEO On-page', 'เข้าใจโครงสร้าง Meta Title และ Heading tags', 'ไม่มี', 'ผ่านไปด้วยดี', 'ผ่าน', 'ตั้งใจทำงานดีมาก'),
(5, 4, 4, '2026-08-01', '09:45:00', '17:00:00', 'เข้าทำงานสายเนื่องจากการเดินทาง ติดตั้งซอฟต์แวร์เครื่องลูกข่าย', 'เรียนรู้เรื่อง Windows Active Directory', 'เดินทางลำบากและมาสาย', 'จะวางแผนเดินทางให้เร็วขึ้น', 'ไม่ผ่าน', 'เข้าฝึกงานสาย กรุณาตรงต่อเวลาและบันทึกรายละเอียดเพิ่มเติม');

-- 7. Attendance
INSERT INTO `attendance` (`id`, `student_id`, `internship_id`, `attendance_date`, `check_in`, `check_out`, `total_hours`, `status`, `note`) VALUES
(1, 1, 1, '2026-08-01', '08:25:00', '17:30:00', 8.00, 'ปกติ', 'ตรงต่อเวลา'),
(2, 1, 1, '2026-08-04', '08:20:00', '17:25:00', 8.00, 'ปกติ', 'ตรงต่อเวลา'),
(3, 1, 1, '2026-08-05', '08:28:00', '17:35:00', 8.00, 'ปกติ', 'ตรงต่อเวลา'),
(4, 2, 2, '2026-08-01', '08:30:00', '17:30:00', 8.00, 'ปกติ', 'ตรงต่อเวลา'),
(5, 4, 4, '2026-08-01', '09:45:00', '17:00:00', 6.25, 'มาสาย', 'มาสาย 1 ชั่วโมง 15 นาที');

-- 8. Evaluations
INSERT INTO `evaluations` (`id`, `student_id`, `internship_id`, `evaluator_type`, `responsibility_score`, `punctuality_score`, `hardworking_score`, `teamwork_score`, `communication_score`, `creativity_score`, `professional_skill_score`, `problem_solving_score`, `etiquette_score`, `discipline_score`, `total_score`, `average_score`, `result`, `strength`, `improvement`, `suggestion`) VALUES
(1, 5, 5, 'สถานประกอบการ', 5, 5, 5, 5, 4, 4, 5, 4, 5, 5, 47, 94.00, 'ดีเยี่ยม', 'มีความตั้งใจสูง มีความรับผิดชอบดีมาก ทักษะด้านกราฟิกดีเยี่ยม', 'เพิ่มความมั่นใจในการนำเสนองาน', 'ควรส่งเสริมให้ศึกษาทักษะ UI Design เพิ่มเติม'),
(2, 5, 5, 'ครูที่ปรึกษา', 5, 5, 4, 5, 4, 4, 4, 4, 5, 5, 45, 90.00, 'ดีเยี่ยม', 'ปฏิบัติตามกฎระเบียบอย่างเคร่งครัด บันทึกงานสม่ำเสมอ', 'พัฒนาภาษาอังกฤษเทคนิค', 'เตรียมความพร้อมเข้าสู่ตลาดแรงงานได้เลย');

-- 9. Supervision
INSERT INTO `supervision` (`id`, `student_id`, `company_id`, `teacher_id`, `visit_date`, `visit_time`, `visit_type`, `result`, `problem`, `recommendation`, `status`) VALUES
(1, 1, 1, 1, '2026-08-03', '10:30:00', 'นิเทศที่สถานประกอบการ', 'สถานประกอบการชื่นชมความขยันของนักศึกษา นักศึกษาสามารถปรับตัวได้ดี', 'ไม่มีปัญหาสำคัญ', 'ให้ตั้งใจศึกษาโค้ดโครงสร้างมาตรฐานขององค์กรต่อไป', 'นิเทศแล้ว'),
(2, 4, 4, 1, '2026-08-04', '14:00:00', 'นิเทศออนไลน์', 'พบว่านักศึกษามีปัญหาการเดินทาง และมาสาย 1 ครั้ง', 'การเดินทางจากที่พักไกลจากบริษัท', 'แนะแนวให้นักศึกษาปรับเวลาตื่นและวางแผนเดินทางใหม่', 'ต้องติดตามเพิ่มเติม');

-- 10. Announcements
INSERT INTO `announcements` (`id`, `title`, `content`, `created_by`, `expire_at`, `status`) VALUES
(1, 'แจ้งกำหนดการส่ง Daily Log ประจำสัปดาห์', 'ให้นักศึกษาทุกคนบันทึก Daily Log ภายในเวลา 18.00 น. ของทุกวันทำการ เพื่อให้ครูที่ปรึกษาเข้าตรวจรับรอง', 1, '2026-09-30', 'active'),
(2, 'กำหนดการนิเทศการฝึกงานรอบที่ 1', 'ครูที่ปรึกษาจะเริ่มเข้าทำการนิเทศสถานประกอบการตั้งแต่วันที่ 10 สิงหาคม 2569 เป็นต้นไป', 2, '2026-08-31', 'active');

-- 11. Notifications
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `is_read`) VALUES
(1, 4, 'ครูอนุมัติ Daily Log แล้ว', 'ครูสมชาย ใจดี ได้อนุมัติ Daily Log ประจำวันที่ 01/08/2026 แล้ว', 'success', 1, 1),
(2, 4, 'ครูอนุมัติ Daily Log แล้ว', 'ครูสมชาย ใจดี ได้อนุมัติ Daily Log ประจำวันที่ 04/08/2026 แล้ว', 'success', 2, 0),
(3, 7, 'แจ้งเตือน: บันทึก Daily Log ไม่ผ่าน', 'ครูสมชาย ใจดี ได้ตรวจสอบ Daily Log ประจำวันที่ 01/08/2026 (สถานะ: ไม่ผ่าน)', 'error', 5, 0),
(4, 2, 'มี Daily Log ใหม่รอการตรวจสอบ', 'นายสิทธิกร พงษ์วานิช ได้ส่ง Daily Log ประจำวันที่ 05/08/2026 รอการตรวจสอบ', 'info', 3, 0);

SET FOREIGN_KEY_CHECKS = 1;


