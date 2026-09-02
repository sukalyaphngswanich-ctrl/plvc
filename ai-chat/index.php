<?php
// ====================================================================
// AI Assistant Interactive Chatbot Page (ai-chat/index.php)
// Phitsanulok Vocational College (PLVC)
// ====================================================================

$pageTitle = 'ผู้ช่วย AI อัจฉริยะการฝึกงาน (PLVC AI Assistant)';
$activePage = 'ai_chat';
$activeGroup = 'ai_chat';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$currentUser = getCurrentUser();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-robot text-primary me-2"></i> PLVC AI Assistant ผู้ช่วยอัจฉริยะการฝึกงาน
        </h3>
        <p class="text-muted small m-0">สอบถามข้อมูลสถานประกอบการ กฎระเบียบ การบันทึก Daily Log และเกณฑ์การประเมินได้ตลอด 24 ชั่วโมง</p>
    </div>
    <div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
            <i class="bi bi-patch-check-fill me-1"></i> เชื่อมต่อฐานข้อมูลวิทยาลัยอาชีวศึกษาพิษณุโลก
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Main Chat Window (Left Side) -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white d-flex flex-column" style="height: 650px;">
            <!-- Chat Header -->
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="user-avatar" style="width: 44px; height: 44px; font-size: 1.3rem; background: linear-gradient(135deg, #2563eb, #0ea5e9);">
                        🤖
                    </div>
                    <div>
                        <div class="fw-bold text-dark mb-0">ผู้ช่วย AI การฝึกงาน (PLVC Bot)</div>
                        <small class="text-success d-flex align-items-center gap-1"><span class="spinner-grow spinner-grow-sm" style="width:8px; height:8px;" role="status"></span> พร้อมตอบคำถามทันที (Online)</small>
                    </div>
                </div>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearChatHistory()" title="ล้างการสนทนา"><i class="bi bi-trash"></i> ล้างแชท</button>
            </div>

            <!-- Messages Window -->
            <div class="card-body p-4 overflow-auto flex-grow-1" id="chatMessagesBox" style="background-color: #f8fafc;">
                <!-- Initial Welcome Message -->
                <div class="d-flex gap-3 mb-4">
                    <div class="user-avatar flex-shrink-0" style="width: 40px; height: 40px; font-size: 1.1rem; background: linear-gradient(135deg, #2563eb, #0ea5e9);">
                        🤖
                    </div>
                    <div class="bg-white p-3 rounded-4 shadow-sm border" style="max-width: 85%;">
                        <div class="fw-semibold text-primary mb-1">PLVC AI Assistant</div>
                        <div class="text-secondary small lh-base">
                            สวัสดีครับคุณ <strong><?= htmlspecialchars($currentUser['profile']['first_name'] ?? 'นักศึกษา') ?></strong> 👋 ผมคือผู้ช่วย AI อัจฉริยะประจำระบบการฝึกงาน วิทยาลัยอาชีวศึกษาพิษณุโลกครับ<br><br>
                            ท่านสามารถถามคำถามเกี่ยวกับสถานประกอบการ การบันทึก Daily Log เกณฑ์การประเมิน หรือข้อปฏิบัติต่างๆ ได้ทันทีเลยครับ!
                        </div>
                        <div class="mt-3 d-flex flex-wrap gap-1" id="welcomeQuickChips">
                            <button class="btn btn-sm btn-light border text-primary rounded-pill px-3" onclick="sendQuickPrompt('แนะนำสถานประกอบการสาขาไอที')">🏢 แนะนำสถานประกอบการไอที</button>
                            <button class="btn btn-sm btn-light border text-primary rounded-pill px-3" onclick="sendQuickPrompt('เกณฑ์การประเมิน 10 ข้อมีอะไรบ้าง')">⭐ เกณฑ์การประเมิน 10 ด้าน</button>
                            <button class="btn btn-sm btn-light border text-primary rounded-pill px-3" onclick="sendQuickPrompt('แนวทางการบันทึก Daily Log')">📝 แนวทางการบันทึก Daily Log</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Input Bar -->
            <div class="p-3 bg-white border-top rounded-bottom-4">
                <form id="chatForm" onsubmit="handleChatSubmit(event)">
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control form-control-lg border-primary-subtle" placeholder="พิมพ์คำถามของคุณที่นี่... (เช่น แนะนำสถานประกอบการ, เกณฑ์การประเมิน)" autocomplete="off" required style="font-size:0.95rem;">
                        <button type="submit" class="btn btn-primary px-4 d-flex align-items-center gap-2" id="sendBtn">
                            <span>ส่งคำถาม</span> <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Suggested Topics & Sidebar Info (Right Side) -->
    <div class="col-12 col-lg-4">
        <!-- Quick Prompts Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h6 class="fw-bold text-slate-900 mb-3 border-bottom pb-2">
                <i class="bi bi-lightbulb-fill text-warning me-2"></i> คำถามยอดฮิตที่พบบ่อย
            </h6>
            <div class="d-grid gap-2">
                <button class="btn btn-outline-primary btn-sm text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between" onclick="sendQuickPrompt('แนะนำสถานประกอบการพิษณุโลก')">
                    <span>🏢 ค้นหาสถานประกอบการในพิษณุโลก</span>
                    <i class="bi bi-chevron-right small text-muted"></i>
                </button>
                <button class="btn btn-outline-primary btn-sm text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between" onclick="sendQuickPrompt('ต้องบันทึก Daily Log เมื่อไหร่')">
                    <span>📝 ต้องส่ง Daily Log วันไหน/กี่โมง</span>
                    <i class="bi bi-chevron-right small text-muted"></i>
                </button>
                <button class="btn btn-outline-primary btn-sm text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between" onclick="sendQuickPrompt('เกณฑ์การประเมิน 10 ข้อมีอะไรบ้าง')">
                    <span>⭐ เกณฑ์การประเมินผลการฝึกงาน 10 ด้าน</span>
                    <i class="bi bi-chevron-right small text-muted"></i>
                </button>
                <button class="btn btn-outline-primary btn-sm text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between" onclick="sendQuickPrompt('ฝึกงานกี่ชั่วโมง')">
                    <span>⏱️ กำหนดชั่วโมงและเวลาการฝึกงาน</span>
                    <i class="bi bi-chevron-right small text-muted"></i>
                </button>
                <button class="btn btn-outline-primary btn-sm text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between" onclick="sendQuickPrompt('แนวทางการแต่งกายและการปฏิบัติตน')">
                    <span>👔 การแต่งกายและข้อปฏิบัติในการฝึกงาน</span>
                    <i class="bi bi-chevron-right small text-muted"></i>
                </button>
                <button class="btn btn-outline-primary btn-sm text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between" onclick="sendQuickPrompt('เบอร์ติดต่อวิทยาลัยอาชีวศึกษาพิษณุโลก')">
                    <span>🏛️ ข้อมูลติดต่อวิทยาลัย (PLVC)</span>
                    <i class="bi bi-chevron-right small text-muted"></i>
                </button>
            </div>
        </div>

        <!-- College Info Box -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white position-relative overflow-hidden">
            <div class="position-absolute end-0 bottom-0 opacity-10 pe-3 pb-3">
                <i class="bi bi-mortarboard-fill" style="font-size: 8rem;"></i>
            </div>
            <h6 class="fw-bold mb-2"><i class="bi bi-building me-1"></i> วิทยาลัยอาชีวศึกษาพิษณุโลก</h6>
            <p class="small opacity-90 mb-3">มุ่งผลิตกำลังคนอาชีวศึกษาคุณภาพสู่ตลาดแรงงานสากล</p>
            <div class="small opacity-75">
                <div class="mb-1"><i class="bi bi-geo-alt me-1"></i> 60 ถ.วังจันทน์ ต.ในเมือง อ.เมือง จ.พิษณุโลก</div>
                <div class="mb-1"><i class="bi bi-telephone me-1"></i> 055-258570</div>
                <div><i class="bi bi-globe me-1"></i> www.plvc.ac.th</div>
            </div>
        </div>
    </div>
</div>

<script>
function formatMarkdown(text) {
    if (!text) return '';
    let formatted = text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n/g, '<br>');
    return formatted;
}

function appendUserMessage(text) {
    const box = document.getElementById('chatMessagesBox');
    const html = `
        <div class="d-flex justify-content-end mb-4">
            <div class="bg-primary text-white p-3 rounded-4 shadow-sm" style="max-width: 80%;">
                <div class="small fw-semibold text-white-50 mb-1 text-end">คุณ</div>
                <div>${escapeHtml(text)}</div>
            </div>
        </div>
    `;
    box.insertAdjacentHTML('beforeend', html);
    box.scrollTop = box.scrollHeight;
}

function appendAiMessage(replyText, quickReplies = []) {
    const box = document.getElementById('chatMessagesBox');
    let quickChipsHtml = '';
    if (quickReplies && quickReplies.length > 0) {
        quickChipsHtml = '<div class="mt-3 d-flex flex-wrap gap-1">';
        quickReplies.forEach(chip => {
            quickChipsHtml += `<button class="btn btn-sm btn-light border text-primary rounded-pill px-3" onclick="sendQuickPrompt('${escapeHtml(chip)}')">${escapeHtml(chip)}</button>`;
        });
        quickChipsHtml += '</div>';
    }

    const html = `
        <div class="d-flex gap-3 mb-4">
            <div class="user-avatar flex-shrink-0" style="width: 40px; height: 40px; font-size: 1.1rem; background: linear-gradient(135deg, #2563eb, #0ea5e9);">
                🤖
            </div>
            <div class="bg-white p-3 rounded-4 shadow-sm border" style="max-width: 85%;">
                <div class="fw-semibold text-primary mb-1">PLVC AI Assistant</div>
                <div class="text-secondary small lh-base">${formatMarkdown(replyText)}</div>
                ${quickChipsHtml}
            </div>
        </div>
    `;
    box.insertAdjacentHTML('beforeend', html);
    box.scrollTop = box.scrollHeight;
}

function appendTypingIndicator() {
    const box = document.getElementById('chatMessagesBox');
    const html = `
        <div class="d-flex gap-3 mb-4" id="aiTypingIndicator">
            <div class="user-avatar flex-shrink-0" style="width: 40px; height: 40px; font-size: 1.1rem; background: linear-gradient(135deg, #2563eb, #0ea5e9);">
                🤖
            </div>
            <div class="bg-white p-3 rounded-4 shadow-sm border text-muted small d-flex align-items-center gap-2">
                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                <span>กำลังค้นหาข้อมูลจากฐานข้อมูล...</span>
            </div>
        </div>
    `;
    box.insertAdjacentHTML('beforeend', html);
    box.scrollTop = box.scrollHeight;
}

function removeTypingIndicator() {
    const el = document.getElementById('aiTypingIndicator');
    if (el) el.remove();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

async function handleChatSubmit(e) {
    if (e) e.preventDefault();
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;

    appendUserMessage(message);
    input.value = '';
    document.getElementById('sendBtn').disabled = true;

    appendTypingIndicator();

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message })
        });
        
        const text = await response.text();
        let data = null;
        try {
            data = JSON.parse(text);
        } catch(pErr) {
            data = null;
        }

        removeTypingIndicator();

        if (data && data.reply) {
            appendAiMessage(data.reply, data.quick_replies);
        } else {
            // Client-side AI fallback engine
            const fallbackRes = generateClientSideAiResponse(message);
            appendAiMessage(fallbackRes.reply, fallbackRes.quick_replies);
        }
    } catch (err) {
        removeTypingIndicator();
        const fallbackRes = generateClientSideAiResponse(message);
        appendAiMessage(fallbackRes.reply, fallbackRes.quick_replies);
    } finally {
        document.getElementById('sendBtn').disabled = false;
    }
}

function generateClientSideAiResponse(rawMsg) {
    const msg = rawMsg.toLowerCase();
    let reply = '';
    let quickReplies = [];

    if (/(แนะนำ|หา|มี|สถานที่|สถานประกอบการ|บริษัท|ไอที|กราฟิก|บัญชี|ตลาด|ที่ฝึก|พิกัด|พิษณุโลก)/i.test(msg)) {
        reply = "🏢 **แนะนำสถานประกอบการพันธมิตรของวิทยาลัยอาชีวศึกษาพิษณุโลก:**\n\n"
              + "**1. บริษัท พิษณุโลก ซอฟต์แวร์ โซลูชั่น จำกัด**\n• ประเภท: พัฒนาเว็บแอปพลิเคชัน & โมบายแอป\n• ที่อยู่: 99/9 ถ.สิงหวัฒน์ ต.พลายชุมพล อ.เมือง (พิษณุโลก)\n• โทรศัพท์: 055-123456\n\n"
              + "**2. บริษัท นเรศวร ดิจิทัล เอเจนซี่ จำกัด**\n• ประเภท: สื่อดิจิทัล & กราฟิกดีไซน์\n• ที่อยู่: 15/2 ถ.บรมไตรโลกนารถ ต.ในเมือง อ.เมือง (พิษณุโลก)\n• โทรศัพท์: 055-654321\n\n"
              + "**3. บริษัท ไอที โกลบอล อินโนเวชั่น จำกัด**\n• ประเภท: ระบบเครือข่าย & ฮาร์ดแวร์ไอที\n• ที่อยู่: 60 ถ.วังจันทน์ ต.ในเมือง อ.เมือง (พิษณุโลก)\n• โทรศัพท์: 055-987654\n\n"
              + "💡 นักศึกษาสามารถกดเมนู **[ข้อมูลสถานประกอบการ]** ด้านบนเพื่อดูตำแหน่งพิกัดบนแผนที่ Google Maps ได้เลยครับ!";
        quickReplies = ['ดูตำแหน่งบนแผนที่', 'วิธีสมัครสถานประกอบการใหม่', 'เกณฑ์การประเมิน 10 ด้าน'];
    } else if (/(daily|log|บันทึกประจำวัน|ส่งงาน|เขียนบันทึก|กี่โมง|เวลาส่ง)/i.test(msg)) {
        reply = "📝 **แนวทางการบันทึก Daily Log (บันทึกประจำวัน):**\n\n"
              + "1. **กำหนดเวลาส่ง:** ให้นักศึกษาบันทึกรายละเอียดการปฏิบัติงานภายในเวลา **18.00 น.** ของทุกวันทำการ\n"
              + "2. **หัวข้อที่ต้องระบุ:**\n"
              + "   • เวลาเข้า-ออกงานจริง\n"
              + "   • งานที่ได้รับมอบหมายและปฏิบัติตามจริง\n"
              + "   • สิ่งที่ได้เรียนรู้หรือทักษะใหม่ที่ได้รับ\n"
              + "   • ปัญหาที่พบและวิธีแก้ไข (ถ้ามี)\n"
              + "3. **การตรวจสอบ:** ครูที่ปรึกษาจะเข้าตรวจและให้คะแนน/ข้อเสนอแนะในระบบเป็นประจำทุกสัปดาห์\n\n"
              + "💡 หากวันใดมีการลากิจ/ลาป่วย ให้ระบุในหมายเหตุและแจ้งพี่เลี้ยงในสถานประกอบการให้รับทราบด้วยครับ";
        quickReplies = ['ลากิจ/ลาป่วยต้องทำยังไง', 'เกณฑ์การประเมิน 10 ด้าน', 'เบอร์ติดต่อวิทยาลัย'];
    } else if (/(ประเมิน|คะแนน|เกณฑ์|10 ข้อ|ตัดเกรด|ผลการประเมิน)/i.test(msg)) {
        reply = "⭐ **เกณฑ์การประเมินผลการฝึกงาน 10 ด้าน (เต็ม 50 คะแนน / คิดเป็น 100%):**\n\n"
              + "1. **ความรับผิดชอบต่อหน้าที่** และงานที่ได้รับมอบหมาย (1-5 คะแนน)\n"
              + "2. **การตรงต่อเวลา** การเข้า-ออกงานและการส่งงาน (1-5 คะแนน)\n"
              + "3. **ความขยัน อดทน** และเอาใจใส่ในการทำงาน (1-5 คะแนน)\n"
              + "4. **มนุษยสัมพันธ์และการทำงานร่วมกับผู้อื่น** (1-5 คะแนน)\n"
              + "5. **ทักษะการสื่อสาร** และการนำเสนองาน (1-5 คะแนน)\n"
              + "6. **ความคิดสร้างสรรค์** และการพัฒนางาน (1-5 คะแนน)\n"
              + "7. **ทักษะฝีมือทางวิชาชีพ** และเทคโนโลยี (1-5 คะแนน)\n"
              + "8. **การแก้ไขปัญหาเฉพาะหน้า** และการตัดสินใจ (1-5 คะแนน)\n"
              + "9. **มารยาท สัมมาคารวะ** และสัมพันธภาพในองค์กร (1-5 คะแนน)\n"
              + "10. **การปฏิบัติตามกฎระเบียบ** และนโยบายความปลอดภัย (1-5 คะแนน)\n\n"
              + "📊 โดยครูที่ปรึกษาและพี่เลี้ยงในสถานประกอบการจะทำการประเมินร่วมกันในระบบครับ";
        quickReplies = ['แนวทางการแต่งกาย', 'จำนวนชั่วโมงฝึกงาน', 'ติดต่อครูที่ปรึกษา'];
    } else if (/(ชั่วโมง|เวลา|กี่วัน|กี่ชั่วโมง|เช็คชื่อ|ขาด|สาย|ลา)/i.test(msg)) {
        reply = "⏱️ **ข้อกำหนดเวลาและชั่วโมงการฝึกงาน:**\n\n"
              + "• **เวลาปฏิบัติงาน:** ปกติวันละ 8 ชั่วโมง (ตามเวลาทำการของสถานประกอบการ)\n"
              + "• **เป้าหมายชั่วโมงรวม (ปวส.):** ไม่น้อยกว่า **320 ชั่วโมง** (ประมาณ 8 สัปดาห์)\n"
              + "• **การเช็คชื่อเข้า-ออก:** ลงเวลาผ่านเมนู **[บันทึกเวลาเข้า-ออกงาน]** ในระบบทุกวัน\n"
              + "• **การขาด/สาย/ลา:** หากสายเกิน 15 นาที หรือมีการลา จะต้องบันทึกเหตุผลในระบบ และแจ้งครูที่ปรึกษาเพื่อพิจารณาชดเชยชั่วโมงฝึกงาน";
        quickReplies = ['วิธีบันทึก Daily Log', 'เกณฑ์การประเมิน 10 ด้าน', 'ค้นหาสถานประกอบการ'];
    } else if (/(แต่งกาย|ชุด|เสื้อ|ปฏิบัติตน|มารยาท|เตรียมตัว|ระเบียบ|ข้อห้าม)/i.test(msg)) {
        reply = "👔 **ข้อปฏิบัติและการแต่งกายในการฝึกงาน (วิทยาลัยอาชีวศึกษาพิษณุโลก):**\n\n"
              + "1. **การแต่งกาย:**\n"
              + "   • สวมชุดนักศึกษาอาชีวศึกษาพิษณุโลกถูกต้องตามระเบียบ หรือชุดยูนิฟอร์มที่สถานประกอบการกำหนดอย่างเคร่งครัด\n"
              + "   • สวมรองเท้าหุ้มส้น สภาพเรียบร้อย\n"
              + "2. **การปฏิบัติตน:**\n"
              + "   • มีสัมมาคารวะ ยิ้มไหว้ทักทายพี่เลี้ยงและบุคลากรในองค์กร\n"
              + "   • รักษาความลับทางธุรกิจและข้อมูลของสถานประกอบการ ห้ามนำออกภายนอก\n"
              + "   • ไม่เล่นโทรศัพท์มือถือในเวลางาน ยกเว้นได้รับอนุญาตเพื่อการปฏิบัติงาน\n"
              + "   • หากพบปัญหาเรื่องงานหรือเพื่อนร่วมงาน ให้รีบปรึกษาครูที่ปรึกษาทันที";
        quickReplies = ['เบอร์ติดต่อวิทยาลัย', 'วิธีลงทะเบียนฝึกงาน', 'ค้นหาสถานประกอบการ'];
    } else if (/(วิทยาลัย|อาชีวศึกษา|พิษณุโลก|plvc|เบอร์โทร|ที่อยู่|แผนก)/i.test(msg)) {
        reply = "🏛️ **ข้อมูลวิทยาลัยอาชีวศึกษาพิษณุโลก (Phitsanulok Vocational College):**\n\n"
              + "• **ที่อยู่:** เลขที่ 60 ถนนวังจันทน์ ตำบลในเมือง อำเภอเมือง จังหวัดพิษณุโลก 65000\n"
              + "• **เบอร์โทรศัพท์กลาง:** 055-258570\n"
              + "• **เว็บไซต์หลัก:** https://www.plvc.ac.th\n"
              + "• **แผนกวิชาที่เปิดสอน:** เทคโนโลยีสารสนเทศ, ดิจิทัลกราฟิก, การบัญชี, การตลาด, คอมพิวเตอร์ธุรกิจ, การจัดการสำนักงาน, คหกรรมศาสตร์, การโรงแรมและการท่องเที่ยว ฯลฯ";
        quickReplies = ['ค้นหาสถานประกอบการ', 'เกณฑ์การประเมิน 10 ด้าน', 'วิธีบันทึก Daily Log'];
    } else {
        reply = "สวัสดีครับ 👋 ผมคือ **PLVC AI Assistant** ผู้ช่วยตอบคำถามการฝึกงาน วิทยาลัยอาชีวศึกษาพิษณุโลกครับ\n\n"
              + "ท่านสามารถสอบถามเรื่องต่างๆ ได้เลยครับ เช่น:\n"
              + "• 🏢 *\"แนะนำสถานประกอบการสาขาไอที\"*\n"
              + "• 📝 *\"ต้องส่ง Daily Log วันไหนบ้าง\"*\n"
              + "• ⭐ *\"เกณฑ์การประเมิน 10 ข้อมีอะไรบ้าง\"*\n"
              + "• ⏱️ *\"ฝึกงานวันละกี่ชั่วโมง\"*\n"
              + "• 👔 *\"แนวทางการแต่งกายฝึกงาน\"*\n"
              + "• 🏛️ *\"เบอร์ติดต่อวิทยาลัยอาชีวศึกษาพิษณุโลก\"*";
        quickReplies = ['แนะนำสถานประกอบการไอที', 'แนวทางการบันทึก Daily Log', 'เกณฑ์การประเมิน 10 ด้าน'];
    }

    return { reply: reply, quick_replies: quickReplies };
}

function sendQuickPrompt(promptText) {
    document.getElementById('chatInput').value = promptText;
    handleChatSubmit(null);
}

function clearChatHistory() {
    if (confirm('คุณต้องการล้างประวัติการสนทนาทั้งหมดใช่หรือไม่?')) {
        const box = document.getElementById('chatMessagesBox');
        box.innerHTML = `
            <div class="d-flex gap-3 mb-4">
                <div class="user-avatar flex-shrink-0" style="width: 40px; height: 40px; font-size: 1.1rem; background: linear-gradient(135deg, #2563eb, #0ea5e9);">🤖</div>
                <div class="bg-white p-3 rounded-4 shadow-sm border" style="max-width: 85%;">
                    <div class="fw-semibold text-primary mb-1">PLVC AI Assistant</div>
                    <div class="text-secondary small lh-base">ล้างประวัติการแชทเรียบร้อยแล้ว สามารถพิมพ์คำถามใหม่ได้เลยครับ!</div>
                </div>
            </div>
        `;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
