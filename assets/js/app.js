/**
 * Student Internship Management & Tracking System - Core UI Logic
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Responsive Toggle
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const appSidebar = document.getElementById('appSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    if (sidebarToggleBtn && appSidebar) {
        sidebarToggleBtn.addEventListener('click', function () {
            appSidebar.classList.toggle('show');
            if (sidebarBackdrop) {
                sidebarBackdrop.classList.toggle('show');
            }
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
            appSidebar.classList.remove('show');
            sidebarBackdrop.classList.remove('show');
        });
    }

    // 2. Submenu Expand/Collapse Toggle
    const menuParents = document.querySelectorAll('.has-submenu');
    menuParents.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;
            if (submenu) {
                submenu.classList.toggle('show');
                const arrow = this.querySelector('.submenu-arrow');
                if (arrow) {
                    arrow.classList.toggle('bi-chevron-down');
                    arrow.classList.toggle('bi-chevron-up');
                }
            }
        });
    });

    // 3. Auto-Calculate Working Hours for Daily Log / Attendance
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const totalHoursInput = document.getElementById('calculated_hours');

    function calculateHours() {
        if (checkInInput && checkOutInput && totalHoursInput && checkInInput.value && checkOutInput.value) {
            const start = new Date("1970-01-01T" + checkInInput.value + "Z");
            const end = new Date("1970-01-01T" + checkOutInput.value + "Z");
            let diff = (end - start) / (1000 * 60 * 60); // Hours
            if (diff > 0) {
                // Subtract 1 hour for standard lunch break if working > 5 hours
                if (diff >= 5) diff -= 1.0;
                totalHoursInput.value = diff.toFixed(2);
            } else {
                totalHoursInput.value = "0.00";
            }
        }
    }

    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', calculateHours);
        checkOutInput.addEventListener('change', calculateHours);
    }

    // 4. Auto-Calculate Evaluation Scores (10 criteria * 5 points max = 50 total)
    const evalScoreInputs = document.querySelectorAll('.eval-score-input');
    const totalScoreDisplay = document.getElementById('totalScoreDisplay');
    const avgScoreDisplay = document.getElementById('avgScoreDisplay');
    const gradeResultDisplay = document.getElementById('gradeResultDisplay');
    const totalScoreHidden = document.getElementById('total_score');
    const avgScoreHidden = document.getElementById('average_score');
    const resultHidden = document.getElementById('result');

    function calculateEvaluation() {
        if (evalScoreInputs.length > 0) {
            let total = 0;
            evalScoreInputs.forEach(input => {
                let val = parseInt(input.value) || 0;
                if (val < 1) val = 1;
                if (val > 5) val = 5;
                total += val;
            });

            // Convert to 100% scale (50 max score -> 100%)
            let avgPercentage = (total / 50) * 100;
            let grade = 'ควรปรับปรุง';

            if (avgPercentage >= 90) grade = 'ดีเยี่ยม';
            else if (avgPercentage >= 80) grade = 'ดีมาก';
            else if (avgPercentage >= 70) grade = 'ดี';
            else if (avgPercentage >= 60) grade = 'พอใช้';

            if (totalScoreDisplay) totalScoreDisplay.textContent = total + ' / 50';
            if (avgScoreDisplay) avgScoreDisplay.textContent = avgPercentage.toFixed(2) + '%';
            if (gradeResultDisplay) gradeResultDisplay.textContent = grade;

            if (totalScoreHidden) totalScoreHidden.value = total;
            if (avgScoreHidden) avgScoreHidden.value = avgPercentage.toFixed(2);
            if (resultHidden) resultHidden.value = grade;
        }
    }

    if (evalScoreInputs.length > 0) {
        evalScoreInputs.forEach(input => {
            input.addEventListener('input', calculateEvaluation);
            input.addEventListener('change', calculateEvaluation);
        });
        calculateEvaluation();
    }
});

/**
 * Client-Side Table Export to CSV Function
 */
function exportTableToCSV(tableId, filename = 'internship-report.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll("tr");

    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length - 1; j++) { // Exclude action column
            let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/"/g, '""');
            row.push('"' + text.trim() + '"');
        }
        csv.push(row.join(","));
    }

    // Download CSV with UTF-8 BOM for Thai Language in Excel
    const csvFile = new Blob(["\ufeff" + csv.join("\n")], { type: "text/csv;charset=utf-8;" });
    const downloadLink = document.createElement("a");
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

/**
 * Print Report Function
 */
function printReport() {
    window.print();
}

/**
 * Confirm Delete Action Modal
 */
function confirmDelete(url, itemName = 'รายการนี้') {
    if (confirm(`คุณแน่ใจหรือไม่ว่าต้องการลบ ${itemName} ?\nการดำเนินการนี้ไม่สามารถย้อนกลับได้`)) {
        window.location.href = url;
    }
}
