<?php
// ====================================================================
// Add Evaluation Form (evaluations/add.php)
// ====================================================================

$pageTitle = 'บันทึกการประเมินผลการฝึกงาน';
$activePage = 'evaluations';
$activeGroup = 'evaluations';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole(['admin', 'teacher']);

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id            = (int)($_POST['student_id'] ?? 0);
    $internship_id         = (int)($_POST['internship_id'] ?? 0);
    $evaluator_type        = trim($_POST['evaluator_type'] ?? 'ครูที่ปรึกษา');
    
    $responsibility_score  = (int)($_POST['responsibility_score'] ?? 5);
    $punctuality_score     = (int)($_POST['punctuality_score'] ?? 5);
    $hardworking_score     = (int)($_POST['hardworking_score'] ?? 5);
    $teamwork_score        = (int)($_POST['teamwork_score'] ?? 5);
    $communication_score   = (int)($_POST['communication_score'] ?? 5);
    $creativity_score      = (int)($_POST['creativity_score'] ?? 5);
    $professional_score    = (int)($_POST['professional_skill_score'] ?? 5);
    $problem_solving_score = (int)($_POST['problem_solving_score'] ?? 5);
    $etiquette_score       = (int)($_POST['etiquette_score'] ?? 5);
    $discipline_score      = (int)($_POST['discipline_score'] ?? 5);

    $total_score   = (int)($_POST['total_score'] ?? 50);
    $average_score = (float)($_POST['average_score'] ?? 100.00);
    $result        = trim($_POST['result'] ?? 'ดีเยี่ยม');
    $strength      = trim($_POST['strength'] ?? '');
    $improvement   = trim($_POST['improvement'] ?? '');
    $suggestion    = trim($_POST['suggestion'] ?? '');

    if ($student_id <= 0 || $internship_id <= 0) {
        $error = 'กรุณาเลือกนักศึกษาที่ต้องการประเมิน';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO evaluations (student_id, internship_id, evaluator_type, responsibility_score, punctuality_score, hardworking_score, teamwork_score, communication_score, creativity_score, professional_skill_score, problem_solving_score, etiquette_score, discipline_score, total_score, average_score, result, strength, improvement, suggestion)
                                  VALUES (:sid, :iid, :etype, :sc1, :sc2, :sc3, :sc4, :sc5, :sc6, :sc7, :sc8, :sc9, :sc10, :ts, :avg, :res, :str, :imp, :sug)");
            $stmt->execute([
                ':sid' => $student_id, ':iid' => $internship_id, ':etype' => $evaluator_type,
                ':sc1' => $responsibility_score, ':sc2' => $punctuality_score, ':sc3' => $hardworking_score, ':sc4' => $teamwork_score,
                ':sc5' => $communication_score, ':sc6' => $creativity_score, ':sc7' => $professional_score, ':sc8' => $problem_solving_score,
                ':sc9' => $etiquette_score, ':sc10' => $discipline_score,
                ':ts' => $total_score, ':avg' => $average_score, ':res' => $result,
                ':str' => $strength, ':imp' => $improvement, ':sug' => $suggestion
            ]);
            redirectUrl("index.php?msg=added");
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

$studentsList = $db->query("SELECT s.id, s.student_code, CONCAT(s.first_name, ' ', s.last_name) as name, i.id as internship_id 
                            FROM students s JOIN internships i ON s.id = i.student_id ORDER BY s.student_code ASC")->fetchAll();
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-star-fill text-warning me-2"></i> บันทึกแบบประเมินผลการฝึกงาน</h3>
        <p class="text-muted small m-0">ประเมินผลการฝึกปฏิบัติงาน 10 หัวข้อ (คะแนนเต็ม 5 ต่อข้อ รวม 50 คะแนน)</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <form action="add.php" method="POST">
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <label class="form-label fw-medium small text-secondary">เลิอกนักศึกษา *</label>
                <select name="student_id" class="form-select" required onchange="updateEvalInternshipId(this)">
                    <option value="">-- เลิอกนักศึกษา --</option>
                    <?php foreach ($studentsList as $st): ?>
                        <option value="<?=$st['id']?>" data-iid="<?=$st['internship_id']?>"><?=$st['student_code']?> - <?= htmlspecialchars($st['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="internship_id" id="eval_internship_id" value="">
            </div>
            <script>
            function updateEvalInternshipId(sel) {
                const opt = sel.options[sel.selectedIndex];
                document.getElementById('eval_internship_id').value = opt.getAttribute('data-iid') || '';
            }
            </script>

            <div class="col-12 col-md-6">
                <label class="form-label fw-medium small text-secondary">ผู้ประเมิน *</label>
                <select name="evaluator_type" class="form-select" required>
                    <option value="ครูที่ปรึกษา">ครูที่ปรึกษา</option>
                    <option value="สถานประกอบการ">สถานประกอบการ</option>
                </select>
            </div>
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-check2-all me-1"></i> หัวข้อการประเมิน (ให้คะแนน 1 - 5)</h6>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60%;">หัวข้อเกณฑ์การประเมิน (10 ด้าน)</th>
                        <th class="text-center" style="width: 40%;">คะแนนที่ได้ (1 - 5)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>1. ความรับผิดชอบต่อหน้าที่และงานที่ได้รับมอบหมาย</td><td class="text-center"><input type="number" name="responsibility_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>2. การตรงต่อเวลา (การเข้า-ออกงานและการส่งงาน)</td><td class="text-center"><input type="number" name="punctuality_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>3. ความขยัน อดทน และเอาใจใส่ในการทำงาน</td><td class="text-center"><input type="number" name="hardworking_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>4. การทำงานร่วมกับผู้อื่น การปรับตัว และมนุษยสัมพันธ์</td><td class="text-center"><input type="number" name="teamwork_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>5. ทักษะการสื่อสารและการนำเสนองาน</td><td class="text-center"><input type="number" name="communication_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>6. ความคิดสร้างสรรค์และการพัฒนางาน</td><td class="text-center"><input type="number" name="creativity_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>7. ทักษะฝีมือทางวิชาชีพและเทคโนโลยี</td><td class="text-center"><input type="number" name="professional_skill_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>8. การแก้ไขปัญหาเฉพาะหน้าและการตัดสินใจ</td><td class="text-center"><input type="number" name="problem_solving_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>9. มารยาท สัมมาคารวะ และสัมพันธภาพในองค์กร</td><td class="text-center"><input type="number" name="etiquette_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                    <tr><td>10. การปฏิบัติตามกฎระเบียบและนโยบายความปลอดภัย</td><td class="text-center"><input type="number" name="discipline_score" class="form-control text-center eval-score-input mx-auto" style="width:90px;" min="1" max="5" value="5" required></td></tr>
                </tbody>
            </table>
        </div>

        <!-- Calculated Summary Box -->
        <div class="p-3 bg-light rounded-3 mb-4 d-flex justify-content-around text-center">
            <div>
                <div class="small text-muted">คะแนนรวม</div>
                <div class="fs-3 fw-bold text-primary" id="totalScoreDisplay">50 / 50</div>
                <input type="hidden" name="total_score" id="total_score" value="50">
            </div>
            <div class="border-start"></div>
            <div>
                <div class="small text-muted">คิดเป็นเปอร์เซ็นต์</div>
                <div class="fs-3 fw-bold text-success" id="avgScoreDisplay">100.00%</div>
                <input type="hidden" name="average_score" id="average_score" value="100.00">
            </div>
            <div class="border-start"></div>
            <div>
                <div class="small text-muted">ระดับผลการประเมิน</div>
                <div class="fs-3 fw-bold text-dark" id="gradeResultDisplay">ดีเยี่ยม</div>
                <input type="hidden" name="result" id="result" value="ดีเยี่ยม">
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <label class="form-label fw-medium small text-secondary">จุดเด่นของนักศึกษา</label>
                <textarea name="strength" class="form-control" rows="2" placeholder="จุดเด่นที่ควรส่งเสริม..."></textarea>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-medium small text-secondary">จุดที่ควรปรับปรุง</label>
                <textarea name="improvement" class="form-control" rows="2" placeholder="สิ่งที่ต้องพัฒนาเพิ่มเติม..."></textarea>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-medium small text-secondary">ข้อเสนอแนะเพิ่มเติม</label>
                <textarea name="suggestion" class="form-control" rows="2" placeholder="ข้อเสนอแนะสำหรับสถานศึกษา..."></textarea>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-warning text-white px-4"><i class="bi bi-save me-1"></i> บันทึกผลการประเมิน</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
