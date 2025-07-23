<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lecturer - Timetable System</title>
    <link rel="shortcut icon" href="../../src/ico/ico_logo_001.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../stylesheets/style_header.css">
    <link rel="stylesheet" href="../../stylesheets/style_all.css">
    <style>
        /* 继承原有样式并添加新样式 */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 40px;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .form-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .form-title i {
            color: var(--accent-color);
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 25px;
            gap: 25px;
        }
        
        .form-group {
            flex: 1;
            min-width: 300px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.15);
        }
        
        .profile-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e2e8f0;
            margin-top: 10px;
            display: block;
        }
        
        .upload-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: var(--light-bg);
            border: 1px dashed #cbd5e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            color: var(--text-light);
        }
        
        .upload-btn:hover {
            background: #f8fafc;
            border-color: var(--accent-color);
            color: var(--accent-color);
        }
        
        /* 时间表选择器样式 */
        .time-selector-container {
            margin-top: 30px;
            overflow-x: auto;
        }
        
        .time-selector-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .time-selector {
            border-collapse: collapse;
            width: 100%;
            min-width: 800px;
        }
        
        .time-selector th {
            background: var(--light-bg);
            padding: 12px;
            text-align: center;
            font-weight: 600;
            color: var(--text-dark);
            border: 1px solid #e2e8f0;
        }
        
        .time-selector td {
            padding: 10px;
            border: 1px solid #e2e8f0;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .time-selector td:hover {
            background: #f0f7ff;
        }
        
        .time-selector td.selected {
            background: var(--accent-color);
            color: white;
        }
        
        .time-cell {
            position: relative;
            height: 50px;
        }
        
        .time-label {
            position: absolute;
            left: 5px;
            top: 5px;
            font-size: 12px;
            color: var(--text-light);
        }
        
        /* 按钮组 */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }
        
        .btn-primary {
            background: var(--accent-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: #3b5fdb;
            box-shadow: 0 4px 10px rgba(78, 115, 223, 0.3);
        }
        
        .btn-secondary {
            background: var(--light-bg);
            color: var(--text-dark);
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 20px;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="../../index.php#">
            <div class="logo-container">
                <img src="../../src/ico/ico_logo_001.png" alt="logo">
                <div class="logo-text">Ecalpal</div>
            </div>
        </a>
        <div class="nav-links">
            <a href="../../index.php#" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="../dashboardTimetable.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i>
                <span>Timetable</span>
            </a>
            <div class="nav-link dropdown active">
                <i class="fas fa-database"></i>
                <span>Database</span>
                <div class="dropdown-content">
                    <a href="../dashboardBatch.php"><i class="fas fa-users"></i> Batch</a>
                    <a href="../dashboardLecturer.php"><i class="fas fa-chalkboard-teacher"></i> Lecturer</a>
                    <a href="../dashboardStudent.php"><i class="fas fa-user-graduate"></i> Student</a>
                    <a href="../dashboardSubject.php"><i class="fas fa-book"></i> Subject</a>
                    <a href="../dashboardVenue.php"><i class="fas fa-building"></i> Venue</a>
                </div>
            </div>
            <a href="../genaralSetting.php" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Setting</span>
            </a>
        </div>
    </header>
    
    <!-- Main Content -->
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1 class="form-title">
                    <i class="fas fa-user-plus"></i>
                    Add New Lecturer
                </h1>
            </div>
            
            <form id="lecturerForm" action="./processCreateLecturer.php" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="lecturerId">Lecturer ID</label>
                        <input type="text" id="lecturerId" name="lecturerId" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="lecturerName">Full Name</label>
                        <input type="text" id="lecturerName" name="lecturerName" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="position">Position</label>
                        <input type="text" id="position" name="position" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="department">Department</label>
                        <select id="department" name="department" class="form-control">
                            <option value="Computer Science">Computer Science</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Software Engineering">Software Engineering</option>
                            <option value="Data Science">Data Science</option>
                            <option value="Cyber Security">Cyber Security</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Profile Photo</label>
                        <img id="profilePreview" src="../src/img/default_avatar.png" class="profile-preview" alt="Profile Preview">
                        <label for="profileImage" class="upload-btn">
                            <i class="fas fa-upload"></i> Upload Photo
                        </label>
                        <input type="file" id="profileImage" name="profileImage" accept="image/*" style="display: none;">
                    </div>
                </div>
                
                <!-- 时间表选择器 -->
                <div class="time-selector-container">
                    <h3 class="time-selector-title">
                        <i class="fas fa-clock"></i>
                        Select Availability
                    </h3>
                    
                    <div class="table-responsive">
                        <table class="time-selector" id="availabilityTable">
                            <thead>
                                <tr>
                                    <th>Time / Day</th>
                                    <!-- 日期列将由JavaScript动态生成 -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- 时间行将由JavaScript动态生成 -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="dashboardLecturer.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Lecturer
                    </button>
                </div>
                
                <!-- 隐藏字段用于存储选中的时间 -->
                <input type="hidden" id="selectedSlots" name="selectedSlots" value="">
            </form>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <p>© 2023 First City University College - Faculty of Computer Science and Technology</p>
        <p>Timetable Management System v2.0</p>
    </footer>
    
    <script>
        // 加载时间配置并生成时间表
        async function loadTimeConfig() {
            try {
                const response = await fetch('../../settings/setting_001.json');
                if (!response.ok) throw new Error('Failed to load time config');
                
                const config = await response.json();
                generateTimeTable(config);
            } catch (error) {
                console.error('Error loading time config:', error);
                // 使用默认配置作为后备
                const defaultConfig = {
                    time_slots: [
                        "08:00-09:00", "09:00-10:00", "10:00-11:00", 
                        "11:00-12:00", "12:00-13:00", "13:00-14:00",
                        "14:00-15:00", "15:00-16:00", "16:00-17:00",
                        "17:00-18:00", "18:00-19:00", "19:00-20:00"
                    ],
                    days: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
                };
                generateTimeTable(defaultConfig);
            }
        }
        
        // 生成时间表
        function generateTimeTable(config) {
            const table = document.getElementById('availabilityTable');
            const thead = table.querySelector('thead tr');
            const tbody = table.querySelector('tbody');
            
            // 清空现有内容
            thead.innerHTML = '<th>Time / Day</th>';
            tbody.innerHTML = '';
            
            // 创建日期列标题
            config.days.forEach(day => {
                const th = document.createElement('th');
                th.textContent = day;
                thead.appendChild(th);
            });
            
            // 创建时间行
            config.time_slots.forEach(timeSlot => {
                const row = document.createElement('tr');
                
                // 添加时间标签单元格
                const timeCell = document.createElement('td');
                timeCell.innerHTML = `<div class="time-cell"><span class="time-label">${timeSlot}</span></div>`;
                row.appendChild(timeCell);
                
                // 为每个日期添加选择单元格
                config.days.forEach(day => {
                    const cell = document.createElement('td');
                    cell.dataset.day = day;
                    cell.dataset.time = timeSlot;
                    cell.innerHTML = '<div class="time-cell"></div>';
                    
                    cell.addEventListener('click', function() {
                        this.classList.toggle('selected');
                        updateSelectedSlots();
                    });
                    
                    row.appendChild(cell);
                });
                
                tbody.appendChild(row);
            });
        }
        
        // 更新选中的时间槽
        function updateSelectedSlots() {
            const selectedCells = document.querySelectorAll('.time-selector td.selected');
            const slots = [];
            
            selectedCells.forEach(cell => {
                slots.push({
                    day: cell.dataset.day,
                    time: cell.dataset.time
                });
            });
            
            document.getElementById('selectedSlots').value = JSON.stringify(slots);
        }
        
        // 图片上传预览
        document.getElementById('profileImage').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // 表单提交验证
        document.getElementById('lecturerForm').addEventListener('submit', function(e) {
            const lecturerId = document.getElementById('lecturerId').value;
            const lecturerName = document.getElementById('lecturerName').value;
            
            if (!lecturerId || !lecturerName) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
        });
        
        // 页面加载时初始化时间表
        window.addEventListener('DOMContentLoaded', loadTimeConfig);
    </script>
</body>
</html>