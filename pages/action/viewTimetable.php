<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Timetable System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="../../stylesheets/style_header.css">
    <link rel="stylesheet" href="../../stylesheets/style_all.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1450px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .action-bar {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #1a3a6c 0%, #2c5282 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
        }
        
        .action-btn:active {
            transform: translateY(0);
        }
        
        .university-header {
            background: linear-gradient(135deg, #1a3a6c 0%, #2c5282 100%);
            color: white;
            padding: 35px 25px 25px;
            text-align: center;
            position: relative;
        }
        
        .university-name {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .faculty-name {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #cbd5e0;
        }
        
        .program-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .academic-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 15px;
            font-size: 16px;
        }
        
        .academic-info div {
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 20px;
            border-radius: 20px;
        }
        
        .content-wrapper {
            display: flex;
            padding: 25px;
            gap: 25px;
        }
        
        .timetable-section {
            flex: 3;
        }
        
        .term-dates-section {
            flex: 1;
            min-width: 380px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-title span {
            font-size: 16px;
            font-weight: 400;
            color: #4a5568;
        }
        
        .timetable-container {
            overflow-x: auto;
            margin-bottom: 30px;
        }
        
        .timetable {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            min-width: 800px;
        }
        
        .timetable th, .timetable td {
            border: 1px solid #e2e8f0;
            padding: 12px 10px;
            text-align: center;
            font-size: 14px;
            height: 50px;
        }
        
        .timetable th {
            background-color: #edf2f7;
            font-weight: 600;
            color: #2d3748;
        }
        
        .time-cell {
            background-color: #f7fafc;
            font-weight: 500;
            color: #4a5568;
            width: 120px;
        }
        
        .class-slot {
            background-color: #ebf8ff;
            color: #2b6cb0;
            font-weight: 500;
            border-radius: 4px;
            position: relative;
            overflow: hidden;
            vertical-align: top;
            padding: 10px 5px;
            transition: all 0.3s ease;
        }
        
        .class-slot:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .class-slot::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: #3182ce;
        }
        
        .class-slot .course-code {
            font-weight: 700;
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }
        
        .class-slot .class-type {
            font-size: 12px;
            background: rgba(255, 255, 255, 0.7);
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        .class-slot .class-room {
            font-size: 13px;
            display: block;
            margin-bottom: 5px;
        }
        
        .class-slot .date-range {
            font-size: 11px;
            color: #4a5568;
        }
        
        .module-info {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-top: 25px;
        }
        
        .module-info th {
            background-color: #2c5282;
            color: white;
            padding: 14px;
            text-align: left;
            font-weight: 500;
        }
        
        .module-info td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .module-info tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .term-dates {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        
        .term-dates th, .term-dates td {
            border: 1px solid #e2e8f0;
            padding: 12px 8px;
            text-align: center;
            font-size: 14px;
        }
        
        .term-dates th {
            background-color: #edf2f7;
            font-weight: 600;
        }
        
        .term-dates tr:nth-child(odd) {
            background-color: #f8fafc;
        }
        
        .term-dates tr:hover {
            background-color: #ebf8ff;
        }
        
        .term-dates th:nth-child(1), .term-dates td:nth-child(1) {
            width: 15%;
        }
        
        .term-dates th:nth-child(2), .term-dates td:nth-child(2) {
            width: 22%;
        }
        
        .term-dates th:nth-child(3), .term-dates td:nth-child(3) {
            width: 22%;
        }
        
        .term-dates th:nth-child(4), .term-dates td:nth-child(4) {
            width: 41%;
            text-align: left;
            padding-left: 12px;
        }
        
        .colored {
            color: #c53030;
            font-weight: 500;
        }
        
        .semester-break {
            background-color: #f0fff4;
            font-style: italic;
            color: #2f855a;
        }
        
        .footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 15px;
            color: #4a5568;
            border-top: 1px solid #e2e8f0;
        }
        
        .students-list {
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .dynamic-placeholder {
            background-color: #e2e8f0;
            color: #4a5568;
            border-radius: 4px;
            padding: 2px 6px;
            display: inline-block;
            min-width: 100px;
        }
        
        .highlight {
            background-color: #fff9db;
            box-shadow: 0 0 0 2px #ffd43b;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            flex-direction: column;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .timetable-selector {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
            background: white;
            padding: 8px 15px;
            border-radius: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .timetable-selector select {
            border: none;
            background: transparent;
            font-weight: 600;
            outline: none;
            color: #1a3a6c;
        }
        
        @media (max-width: 1100px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .term-dates-section {
                min-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .university-header {
                padding: 15px;
            }
            
            .academic-info {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            
            .content-wrapper {
                padding: 15px;
            }
            
            .action-bar {
                position: static;
                justify-content: center;
                margin: 15px 0;
            }
            
            .term-dates th:nth-child(4), .term-dates td:nth-child(4) {
                min-width: 180px;
            }
        }
    </style>
</head>
<body>

<?php include("../page_all/header_page_action.php"); ?>
    <div class="loading-overlay">
        <div class="spinner"></div>
        <p>Loading timetable data...</p>
    </div>
    
    <!-- 添加ID用于PDF导出 -->
    <div class="container" id="contentToExport">
        
        <div class="action-bar">
            <button class="action-btn" id="exportBtn">
                <i class="fas fa-file-pdf"></i> Export as PDF
            </button>
        </div>
        <div class="university-header">
            <div class="university-name" id="universityName">FIRST CITY UNIVERSITY COLLEGE</div>
            <div class="faculty-name" id="facultyName">Faculty of Computer Science and Technology</div>
            <div class="program-name" id="programName">Diploma in Information Technology</div>
            
            <div class="academic-info">
                <div>Academic Year <span class="dynamic-placeholder" id="academicYear">2025</span></div>
                <div>Year <span class="dynamic-placeholder" id="yearOfStudy">1</span>, Semester <span class="dynamic-placeholder" id="semester">1</span> (<span class="dynamic-placeholder" id="intake">Jun 2025</span> intakes)</div>
            </div>
        </div>
        
        <div class="content-wrapper">
            <div class="timetable-section">
                <div class="section-title">
                    <div>Timetable</div>
                    <span>Effective Date: <span class="dynamic-placeholder" id="effectiveDate">Aug 18, 2025</span></span>
                </div>
                
                <div class="timetable-container">
                    <table class="timetable" id="timetable">
                        <thead>
                            <tr>
                                <th class="time-cell">Time</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                            </tr>
                        </thead>
                        <tbody id="timetableBody">
                            <!-- Timetable will be populated here dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <table class="module-info" id="moduleTable">
                    <tr>
                        <th>Module</th>
                        <th>ShortName</th>
                        <th>Code</th>
                        <th>Lecturer</th>
                        <th>Lecturing Hours</th>
                        <th>Lab/Tutorial</th>
                    </tr>
                    <!-- Module info will be populated here dynamically -->
                </table>
            </div>
            
            <div class="term-dates-section">
                <div class="section-title">
                    <div>Term Dates <span class="dynamic-placeholder" id="termDatesRange">(Jul 2025 - Oct 2025)</span></div>
                </div>
                
                <table class="term-dates" id="termDatesTable">
                    <tr>
                        <th>Week</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                    </tr>
                    <!-- Term dates will be populated here dynamically -->
                </table>
                <div style="font-size: 14px; color: #718096; margin-top: 15px; font-style: italic;">
                    * The term dates are subject to change without prior notice
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="students-list">
                Timetable for: <span class="dynamic-placeholder" id="studentsList">Non Student</span>
            </div>
            <div>
                © 2025 First City University College - Faculty of Computer Science and Technology
            </div>
        </div>
    </div>

    <script>

        // get timetable data
        async function getTimetableData(timetableId) {
            const res = await fetch(`../get_data/get_timetable.php?id=${timetableId}`);
            const data = await res.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            return data;
        }
        

        // Get timetable ID from URL
        function getTimetableIdFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('timetable') || 'T0001';
        }

        // Format date for display
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        // Generate time slots for timetable
        function generateTimeSlots() {
            const times = [];
            for (let hour = 9; hour < 17; hour++) {
                for (let min = 0; min < 60; min += 30) {
                    const startHour = hour;
                    const startMin = min;
                    let endHour = hour;
                    let endMin = min + 30;

                    if (endMin >= 60) {
                        endMin = 0;
                        endHour += 1;
                    }

                    const formatTime = (h, m) => {
                        const hour12 = h > 12 ? h - 12 : h;
                        const ampm = h >= 12 ? 'PM' : 'AM';
                        return `${hour12}:${m === 0 ? '00' : m} ${ampm}`;
                    };

                    const timeStr = `${formatTime(startHour, startMin)} ~ ${formatTime(endHour, endMin)}`;
                    times.push(timeStr);
                }
            }
            return times;
        }


        // Find class slot position
        function findSlotPosition(timeSlot) {
            // Each time slot represents 30 minutes
            // Our time slots start at 8:00 AM (slot 0)
            // Each row represents a 30-minute interval
            return timeSlot - 1;
        }

        // Generate term dates
        function generateTermDates(startDate, durationWeeks) {
            const dates = [];
            const start = new Date(startDate);
            
            for (let i = 1; i <= durationWeeks; i++) {
                const from = new Date(start);
                from.setDate(from.getDate() + (i - 1) * 7);
                
                const to = new Date(from);
                to.setDate(to.getDate() + 4); // Monday to Friday
                
                dates.push({
                    week: i,
                    from: formatDate(from),
                    to: formatDate(to),
                    status: `Active week ${i}`
                });
            }
            
            // Add examination weeks
            dates.push({
                week: durationWeeks + 1,
                from: formatDate(new Date(start).setDate(start.getDate() + durationWeeks * 7)),
                to: formatDate(new Date(start).setDate(start.getDate() + durationWeeks * 7 + 4)),
                status: 'Final examination/Marking week'
            });
            
            dates.push({
                week: durationWeeks + 2,
                from: formatDate(new Date(start).setDate(start.getDate() + (durationWeeks + 1) * 7)),
                to: formatDate(new Date(start).setDate(start.getDate() + (durationWeeks + 1) * 7 + 4)),
                status: 'Final examination/Marking week'
            });
            
            // Add semester break
            dates.push({
                week: durationWeeks + 3,
                from: formatDate(new Date(start).setDate(start.getDate() + (durationWeeks + 2) * 7)),
                to: formatDate(new Date(start).setDate(start.getDate() + (durationWeeks + 2) * 7 + 4)),
                status: 'Semester Break'
            });
            
            return dates;
        }

        // Render timetable
        async function renderTimetable(timetableId) {

            timetableOriginalData = await getTimetableData(timetableId);

            const timetableData = timetableOriginalData.timetable;
            const batchData = timetableOriginalData.batch;
            const slots = timetableOriginalData.slots;
            const students = timetableOriginalData.students;
            const subjects = timetableOriginalData.subjects;
            const lecturers = timetableOriginalData.lecturers;
            const venues = timetableOriginalData.venues;
            
            // Update header information
            document.getElementById('universityName').textContent = 'FIRST CITY UNIVERSITY COLLEGE';
            document.getElementById('facultyName').textContent = batchData.Faculty;
            document.getElementById('programName').textContent = batchData.course_name;
            document.getElementById('academicYear').textContent = batchData.year;
            document.getElementById('yearOfStudy').textContent = batchData.year_of_study;
            document.getElementById('semester').textContent = batchData.semester;
            document.getElementById('intake').textContent = batchData.intake_name;
            document.getElementById('effectiveDate').textContent = formatDate(timetableData.lastModifyTime);
            
            // Update students list
            const studentNames = students.map(student => student.name).join(', ');
            document.getElementById('studentsList').textContent = studentNames;
            
            // Update term dates
            const termDates = generateTermDates(timetableData.start_date, timetableData.duration_weeks);
            document.getElementById('termDatesRange').textContent = `(${formatDate(timetableData.start_date)} - ${termDates[termDates.length-1].to})`;
            
            // Clear existing content
            const termDatesTable = document.getElementById('termDatesTable');
            while (termDatesTable.rows.length > 1) {
                termDatesTable.deleteRow(1);
            }
            
            // Populate term dates
            termDates.forEach(date => {
                const row = termDatesTable.insertRow();
                const cell1 = row.insertCell(0);
                const cell2 = row.insertCell(1);
                const cell3 = row.insertCell(2);
                const cell4 = row.insertCell(3);
                
                cell1.textContent = date.week;
                cell2.textContent = date.from;
                cell3.textContent = date.to;
                cell4.textContent = date.status;
                
                if (date.status.includes('examination')) {
                    cell4.className = 'colored';
                } else if (date.status.includes('Break')) {
                    row.className = 'semester-break';
                }
            });
            
            // Generate time slots
            const timeSlots = generateTimeSlots();
            const timetableBody = document.getElementById('timetableBody');
            timetableBody.innerHTML = '';
            
            // Create rows for each time slot
            for (let i = 0; i < timeSlots.length; i++) {
                const row = timetableBody.insertRow();
                const timeCell = row.insertCell(0);
                timeCell.className = 'time-cell';
                timeCell.textContent = timeSlots[i];
                
                // Add empty cells for each day
                for (let j = 0; j < 5; j++) {
                    const cell = row.insertCell(j + 1);
                    cell.id = `cell-${i}-${j}`;
                }
            }
            
            // Place class slots in timetable
            slots.forEach(slot => {
                const subject = timetableOriginalData.subjects[slot.subject_id];
                const lecturer = timetableOriginalData.lecturers[slot.lecturer_id];
                const venue = timetableOriginalData.venues[slot.venue_id];
                
                const rowIndex = findSlotPosition(slot.timeSlot); // slot.timeSlot 是 0 起点
                const dayIndex = ['MO', 'TU', 'WE', 'TH', 'FR'].indexOf(slot.day);

                const cellId = `cell-${rowIndex}-${dayIndex}`;
                const cell = document.getElementById(cellId);
                if (!cell) return;

                cell.rowSpan = slot.duration;
                cell.className = 'class-slot';
                cell.innerHTML = `
                    <span class="course-code">${subject.shortname}</span><br>
                    <span class="class-room">${venue.Name}</span><br>
                    <span class="date-range">${lecturer.name}</span>
                `;

                // 删除被占用的格子（完全移除）
                for (let i = 1; i < slot.duration; i++) {
                    const nextCellId = `cell-${rowIndex + i}-${dayIndex}`;
                    const nextCell = document.getElementById(nextCellId);
                    if (nextCell) {
                        nextCell.remove(); // ❗彻底移除
                    }
                }

            });
            
            // Populate module information
            const moduleTable = document.getElementById('moduleTable');
            while (moduleTable.rows.length > 1) {
                moduleTable.deleteRow(1);
            }
            
            // Get unique subjects for this timetable
            const uniqueSubjects = {};
            slots.forEach(slot => {
                const subjectId = slot.subject_id;
                if (!uniqueSubjects[subjectId]) {
                    uniqueSubjects[subjectId] = timetableOriginalData.subjects[subjectId];
                }
            });
            
            // Add module rows
            Object.values(uniqueSubjects).forEach(subject => {
                const row = moduleTable.insertRow();
                const cell1 = row.insertCell(0); // Full Name
                const cell2 = row.insertCell(1); // Short Name
                const cell3 = row.insertCell(2); // Code
                const cell4 = row.insertCell(3); // Lecturer
                const cell5 = row.insertCell(4); // Credit Hours
                const cell6 = row.insertCell(5); // Contact Hours
                
                cell1.textContent = subject.fullname;
                cell2.textContent = subject.shortname;
                cell3.textContent = subject.code;
                
                // Find lecturer for this subject
                const slot = slots.find(s => s.subject_id === subject.ID);
                if (slot) {
                    cell4.textContent = timetableOriginalData.lecturers[slot.lecturer_id].name;
                }
                
                // Set default hours
                cell5.textContent = '2';
                cell6.textContent = '2';
            });
        }

        // Export to PDF
        function exportToPDF() {
            const element = document.getElementById('contentToExport');
            const btn = document.getElementById('exportBtn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
            btn.disabled = true;
            
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                logging: false
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jspdf.jsPDF('p', 'mm', 'a4');
                const imgWidth = 210;
                const imgHeight = canvas.height * imgWidth / canvas.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);

                const faculty = document.getElementById('facultyName').textContent;
                const program = document.getElementById('programName').textContent;
                const year = document.getElementById('yearOfStudy').textContent;
                const semester = document.getElementById('semester').textContent;

                const fileName = `First-City-University-College_TimeTable_${faculty}_${program}_Year-${year}-Semester-${semester}.pdf`;

                pdf.save(fileName);
                
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(error => {
                console.error('Error generating PDF:', error);
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Error generating PDF. Please try again.');
            });
        }

        // Initialize the application
        function init() {
            // Hide loading overlay
            document.querySelector('.loading-overlay').style.display = 'none';
            
            // Get timetable ID from URL
            const timetableId = getTimetableIdFromUrl();
            // Render the timetable
            renderTimetable(timetableId);
            
            // Setup event listeners
            document.getElementById('exportBtn').addEventListener('click', exportToPDF);
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate loading delay
            setTimeout(init, 1500);
        });
    </script>
</body>
</html>