# 🏫 PLVC Internship Management System
## ระบบจัดการและติดตามการฝึกงาน — วิทยาลัยอาชีวศึกษาพิษณุโลก

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

### 📋 เกี่ยวกับโปรเจค

ระบบบริหารจัดการการฝึกประสบการณ์วิชาชีพ (ฝึกงาน) สำหรับนักศึกษาระดับ ปวช./ปวส. ของวิทยาลัยอาชีวศึกษาพิษณุโลก (PLVC) พัฒนาด้วย PHP + MySQL + Bootstrap 5

### ✨ ฟีเจอร์หลัก

| โมดูล | รายละเอียด |
|-------|------------|
| 🎓 จัดการนักศึกษา | เพิ่ม/แก้ไข/ค้นหา นักศึกษา 10 สาขาวิชา |
| 👨‍🏫 จัดการอาจารย์ | ข้อมูลอาจารย์นิเทศก์และที่ปรึกษา |
| 🏢 สถานประกอบการ | ข้อมูลบริษัท + แผนที่ GPS (Leaflet) |
| 📝 การฝึกงาน | ลงทะเบียน/ติดตามสถานะการฝึกงาน |
| 📅 บันทึกประจำวัน | Daily Log เช็คอิน/เช็คเอาต์ |
| ✅ ประเมินผล | เกณฑ์ประเมิน 10 ด้าน (50 คะแนน) |
| 📊 ลงเวลา | ระบบ Attendance |
| 🤖 AI Assistant | Chatbot ตอบคำถามเกี่ยวกับการฝึกงาน |
| 📢 ประกาศ | ระบบแจ้งข่าวสาร |
| 🔔 แจ้งเตือน | Notification Center |
| 📈 รายงาน | สรุปสถิติ + Chart.js + Export CSV |
| 👥 จัดการผู้ใช้ | Admin/Teacher/Student roles |
| 🔍 นิเทศก์ | ติดตามการนิเทศ |

### 🏫 สาขาวิชา (10 สาขา)

1. เทคโนโลยีสารสนเทศ
2. ดิจิทัลกราฟิก
3. การบัญชี
4. การตลาด
5. คอมพิวเตอร์ธุรกิจ
6. การจัดการสำนักงาน
7. คหกรรมศาสตร์
8. การโรงแรมและการท่องเที่ยว
9. อาหารและโภชนาการ
10. ดีไซน์แฟชั่นและสิ่งทอ

### 🛠️ เทคโนโลยี

- **Backend:** PHP 8.0+ / PDO / MySQL 8.0
- **Frontend:** Bootstrap 5.3 / Bootstrap Icons / Chart.js
- **Map:** Leaflet.js + OpenStreetMap
- **AI:** Client-side intent matching + server fallback

### 📦 วิธีติดตั้ง

1. Clone โปรเจค:
```bash
git clone https://github.com/sukalyaphngswanich-ctrl/plvc.git
```

2. นำเข้าฐานข้อมูล:
   - เปิด phpMyAdmin → Import → เลือกไฟล์ `database/internship.sql`

3. แก้ไขไฟล์ `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'internship_db');
```

4. เปิดเว็บ: `http://localhost/plvc/`

### 👤 ข้อมูลเข้าสู่ระบบ (Demo)

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| อาจารย์ | teacher1 | teacher123 |
| นักศึกษา | student1 | student123 |

### 📍 ข้อมูลวิทยาลัย

- **ชื่อ:** วิทยาลัยอาชีวศึกษาพิษณุโลก (PLVC)
- **ที่อยู่:** 60 ถ.วังจันทน์ ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000
- **โทร:** 055-258570
- **เว็บ:** https://www.plvc.ac.th

---

> พัฒนาโดย นักศึกษาวิทยาลัยอาชีวศึกษาพิษณุโลก © 2569
