<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Preview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #eef2ff;
            --secondary-color: #3f37c9;
            --accent-color: #7d51ff;
            --success-color: #38b000;
            --warning-color: #ff9e00;
            --danger-color: #e5383b;
            --text-dark: #2d3748;
            --text-light: #718096;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --card-shadow: 0 10px 20px rgba(0,0,0,0.05), 0 6px 6px rgba(0,0,0,0.04);
            --hover-shadow: 0 15px 30px rgba(0,0,0,0.1), 0 5px 15px rgba(0,0,0,0.07);
            --modal-backdrop: rgba(0,0,0,0.7);
            --modal-border-radius: 16px;
            --modal-header-height: 70px;
            --modal-footer-height: 70px;
        }
        
        /* 模态框整体样式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: var(--modal-backdrop);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            padding: 0;
            border-radius: var(--modal-border-radius);
            width: 90%;
            max-width: 1000px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            transform: translateY(-20px);
            animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        
        @keyframes slideUp {
            to { transform: translateY(0); }
        }
        
        /* 模态框头部 */
        .modal-header {
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            color: white;
            padding: 0 25px;
            height: var(--modal-header-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top-left-radius: var(--modal-border-radius);
            border-top-right-radius: var(--modal-border-radius);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .modal-title {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .modal-title i {
            font-size: 26px;
        }
        
        .close {
            cursor: pointer;
            font-size: 32px;
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .close:hover {
            color: white;
            background: rgba(255,255,255,0.2);
            transform: rotate(90deg);
        }
        
        /* 模态框控制栏 */
        .modal-controls {
            padding: 15px 25px;
            background: var(--light-bg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .week-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .week-btn {
            background: white;
            border: 1px solid var(--border-color);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .week-btn:hover {
            background: var(--primary-light);
            border-color: var(--accent-color);
            color: var(--accent-color);
        }
        
        .week-display {
            font-weight: 600;
            color: var(--text-dark);
            min-width: 200px;
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
        }
        
        .modal-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .print-btn {
            background: var(--primary-light);
            color: var(--accent-color);
            border: 1px solid var(--border-color);
        }
        
        .print-btn:hover {
            background: var(--accent-color);
            color: white;
            box-shadow: 0 4px 12px rgba(125, 81, 255, 0.3);
        }
        
        .export-btn {
            background: linear-gradient(135deg, var(--success-color), #2f855a);
            color: white;
        }
        
        .export-btn:hover {
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.3);
            transform: translateY(-2px);
        }
        
        /* 时间表内容区域 */
        .modal-body {
            padding: 20px;
            overflow: auto;
            max-height: calc(85vh - var(--modal-header-height) - var(--modal-footer-height) - 100px);
        }
        
        .time-grid {
            display: grid;
            grid-template-columns: 100px repeat(5, 1fr);
            gap: 8px;
            width: 100%;
            min-width: 800px;
        }
        
        .time-label, .day-header {
            font-weight: 600;
            padding: 12px;
            text-align: center;
            background: var(--light-bg);
            border-radius: 8px;
            color: var(--text-dark);
        }
        
        .day-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 15px;
            box-shadow: 0 2px 5px rgba(67, 97, 238, 0.2);
        }
        
        .day-header .day-name {
            font-size: 16px;
            font-weight: 700;
        }
        
        .time-slot {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 10px;
            min-height: 80px;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }
        
        .time-slot:hover {
            transform: translateY(-3px);
            box-shadow: var(--hover-shadow);
            border-color: var(--accent-color);
            z-index: 2;
        }
        
        .time-slot.lecture {
            background: linear-gradient(135deg, #eef2ff, #dbeafe);
            border-left: 4px solid var(--accent-color);
        }
        
        .time-slot.lab {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-left: 4px solid var(--success-color);
        }
        
        .time-slot.free {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-left: 4px solid #cbd5e1;
        }
        
        .slot-content {
            font-size: 13px;
            line-height: 1.4;
        }
        
        .slot-title {
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-dark);
        }
        
        .slot-details {
            color: var(--text-light);
            font-size: 12px;
        }
        
        .slot-time {
            position: absolute;
            top: 6px;
            right: 8px;
            font-size: 11px;
            font-weight: 600;
            color: var(--accent-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 2px 6px;
            border-radius: 10px;
        }
        
        /* 模态框底部 */
        .modal-footer {
            padding: 15px 25px;
            background: var(--light-bg);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .legend {
            display: flex;
            gap: 15px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }
        
        .lecture-legend {
            background: linear-gradient(135deg, #eef2ff, #dbeafe);
            border-left: 3px solid var(--accent-color);
        }
        
        .lab-legend {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-left: 3px solid var(--success-color);
        }
        
        .free-legend {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-left: 3px solid #cbd5e1;
        }
        
        /* 加载状态 */
        .loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px;
            text-align: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(67, 97, 238, 0.2);
            border-top: 5px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                max-height: 90vh;
            }
            
            .modal-controls {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: flex-end;
            }
            
            .modal-body {
                padding: 10px;
                overflow-x: auto;
            }
            
            .modal-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .legend {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- 示例按钮用于演示 -->
    <div style="text-align:center; padding:50px;">
        <button onclick="openModal()" style="padding:15px 30px; background:var(--accent-color); color:white; border:none; border-radius:8px; font-size:18px; cursor:pointer;">
            Open Timetable Preview
        </button>
    </div>

    <!-- 时间表模态框 -->
    <div id="timetable-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="timetable-title">Timetable: T0001</span>
                </div>
                <span class="close" onclick="closeTimetableModal()">&times;</span>
            </div>
            
            <div class="modal-body">
                <div class="time-grid">
                    <!-- Time labels column -->
                    <div class="time-label">Time</div>
                    <div class="day-header">
                        <span class="day-name">Monday</span>
                    </div>
                    <div class="day-header">
                        <span class="day-name">Tuesday</span>
                    </div>
                    <div class="day-header">
                        <span class="day-name">Wednesday</span>
                    </div>
                    <div class="day-header">
                        <span class="day-name">Thursday</span>
                    </div>
                    <div class="day-header">
                        <span class="day-name">Friday</span>
                    </div>
                    
                    <!-- Time slots rows -->
                    <!-- Row 1: 8:00 AM -->
                    <div class="time-label">8:00 AM</div>
                    <div class="time-slot lecture">
                        <div class="slot-content">
                            <div class="slot-title">Software Engineering</div>
                            <div class="slot-details">Prof. Johnson</div>
                            <div class="slot-details">Room: CS-201</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    <div class="time-slot lab">
                        <div class="slot-content">
                            <div class="slot-title">Database Lab</div>
                            <div class="slot-details">Dr. Smith</div>
                            <div class="slot-details">Lab: DB-102</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    <div class="time-slot lecture">
                        <div class="slot-content">
                            <div class="slot-title">Algorithms</div>
                            <div class="slot-details">Prof. Williams</div>
                            <div class="slot-details">Room: CS-301</div>
                        </div>
                    </div>
                    
                    <!-- Row 2: 9:30 AM -->
                    <div class="time-label">9:30 AM</div>
                    <div class="time-slot"></div>
                    <div class="time-slot lecture">
                        <div class="slot-content">
                            <div class="slot-title">Web Development</div>
                            <div class="slot-details">Dr. Anderson</div>
                            <div class="slot-details">Room: CS-105</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    <div class="time-slot free">
                        <div class="slot-content">
                            <div class="slot-title">Free Period</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    
                    <!-- Row 3: 11:00 AM -->
                    <div class="time-label">11:00 AM</div>
                    <div class="time-slot lab">
                        <div class="slot-content">
                            <div class="slot-title">Networking Lab</div>
                            <div class="slot-details">Prof. Davis</div>
                            <div class="slot-details">Lab: NET-205</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    <div class="time-slot lecture">
                        <div class="slot-content">
                            <div class="slot-title">Data Structures</div>
                            <div class="slot-details">Dr. Roberts</div>
                            <div class="slot-details">Room: CS-202</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    <div class="time-slot free">
                        <div class="slot-content">
                            <div class="slot-title">Free Period</div>
                        </div>
                    </div>
                    
                    <!-- Row 4: 1:00 PM -->
                    <div class="time-label">1:00 PM</div>
                    <div class="time-slot free">
                        <div class="slot-content">
                            <div class="slot-title">Lunch Break</div>
                        </div>
                    </div>
                    <div class="time-slot free">
                        <div class="slot-content">
                            <div class="slot-title">Lunch Break</div>
                        </div>
                    </div>
                    <div class="time-slot free">
                        <div class="slot-content">
                            <div class="slot-title">Lunch Break</div>
                        </div>
                    </div>
                    <div class="time-slot free">
                        <div class="slot-content">
                            <div class="slot-title">Lunch Break</div>
                        </div>
                    </div>
                    <div class="time-slot free">
                        <div class="slot-content">
                            <div class="slot-title">Lunch Break</div>
                        </div>
                    </div>
                    
                    <!-- Row 5: 2:00 PM -->
                    <div class="time-label">2:00 PM</div>
                    <div class="time-slot lecture">
                        <div class="slot-content">
                            <div class="slot-title">AI & Machine Learning</div>
                            <div class="slot-details">Dr. Thompson</div>
                            <div class="slot-details">Room: CS-401</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    <div class="time-slot lab">
                        <div class="slot-content">
                            <div class="slot-title">Programming Lab</div>
                            <div class="slot-details">Prof. Wilson</div>
                            <div class="slot-details">Lab: PROG-103</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    <div class="time-slot lecture">
                        <div class="slot-content">
                            <div class="slot-title">Operating Systems</div>
                            <div class="slot-details">Dr. Martinez</div>
                            <div class="slot-details">Room: CS-302</div>
                        </div>
                    </div>
                    
                    <!-- Row 6: 3:30 PM -->
                    <div class="time-label">3:30 PM</div>
                    <div class="time-slot"></div>
                    <div class="time-slot lecture">
                        <div class="slot-content">
                            <div class="slot-title">Cybersecurity</div>
                            <div class="slot-details">Prof. Brown</div>
                            <div class="slot-details">Room: CS-203</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                    <div class="time-slot free">
                        <div class="slot-content">
                            <div class="slot-title">Free Period</div>
                        </div>
                    </div>
                    <div class="time-slot"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color lecture-legend"></div>
                        <span>Lecture</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color lab-legend"></div>
                        <span>Lab Session</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color free-legend"></div>
                        <span>Free Period</span>
                    </div>
                </div>
                <div class="last-update">
                    Last updated: 2024-04-10 14:30
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('timetable-modal').style.display = 'flex';
        }
        
        function closeTimetableModal() {
            document.getElementById('timetable-modal').style.display = 'none';
        }
        
        function changeWeek(direction) {
            // In a real app, this would load the new week's data
            alert(`Week navigation would load new data in a real application (Direction: ${direction})`);
        }
        
        function printTimetable() {
            alert('Print functionality would be implemented in a real application');
        }
        
        function exportTimetable() {
            alert('Export functionality would be implemented in a real application');
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('timetable-modal');
            if (event.target === modal) {
                closeTimetableModal();
            }
        }
    </script>
</body>
</html>