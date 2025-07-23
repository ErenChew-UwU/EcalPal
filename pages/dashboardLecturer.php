<?php
include_once("../dbconnect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - Timetable System</title>
    <link rel="shortcut icon" href="../src/ico/ico_logo_001.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../stylesheets/style_header.css">
    <link rel="stylesheet" href="../stylesheets/style_all.css">
    <style>
        
        /* Main Content Styles */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dashboard-title i {
            color: var(--accent-color);
        }
        
        .btn {
            padding: 10px 20px;
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
        
        .btn-success {
            background: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background: #2f855a;
            box-shadow: 0 4px 10px rgba(56, 161, 105, 0.3);
        }
        
        /* Table Styles */
        .lecturer-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 40px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: var(--light-bg);
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .search-container {
            display: flex;
            gap: 10px;
        }
        
        .search-box {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            min-width: 250px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .lecturer-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        
        .lecturer-table th {
            background: var(--light-bg);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 2px solid #e2e8f0;
        }
        
        .lecturer-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .lecturer-table tr:hover {
            background-color: #f9fbfd;
        }
        
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .edit-btn {
            background: rgba(236, 201, 75, 0.15);
            color: var(--warning-color);
        }
        
        .edit-btn:hover {
            background: rgba(236, 201, 75, 0.25);
        }
        
        .delete-btn {
            background: rgba(229, 62, 62, 0.15);
            color: var(--danger-color);
        }
        
        .delete-btn:hover {
            background: rgba(229, 62, 62, 0.25);
        }
        
        /* Stats Section */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.total { background: rgba(78, 115, 223, 0.15); color: var(--accent-color); }
        .stat-icon.full-time { background: rgba(56, 161, 105, 0.15); color: var(--success-color); }
        .stat-icon.part-time { background: rgba(236, 201, 75, 0.15); color: var(--warning-color); }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 16px;
            color: var(--text-light);
        }
        
        /* Footer */
        .footer {
            background: white;
            padding: 20px;
            text-align: center;
            margin-top: 50px;
            border-top: 1px solid #e2e8f0;
            color: var(--text-light);
            font-size: 14px;
        }

        /* 可用时间模态框 */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            width: 80%;
            max-width: 900px;
            max-height: 80%;
            overflow-y: auto;
        }

        .modal-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
        }

        .time-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 5px;
        }

        .time-label {
            font-weight: bold;
            text-align: center;
            width: 140px;
        }

        .day-header {
            font-weight: bold;
            text-align: center;
            background: #f0f0f0;
        }

        .time-cell {
            height: 30px;
            background: #eee;
            cursor: pointer;
        }

        .time-cell.available {
            background: #4CAF50;
            color: white;
        }

        .time-cell:hover {
            filter: brightness(1.2);
        }

        /* 添加加载和错误状态样式 */
        .loading, .error {
            grid-column: 1 / span 8;
            text-align: center;
            padding: 30px;
            font-size: 18px;
        }

        .error {
            color: var(--danger-color);
        }

        /* 关闭按钮样式 */
        .close {
            cursor: pointer;
            font-size: 28px;
            color: #aaa;
            padding: 0 10px;
        }

        .close:hover {
            color: #333;
        }

        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .nav-links {
                width: 100%;
                justify-content: space-between;
                gap: 5px;
            }
            
            .nav-link span {
                font-size: 12px;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-container {
                width: 100%;
            }
            
            .search-box {
                width: 100%;
            }
            
            .dropdown-content {
                left: auto;
                right: 0;
            }
            .time-label {
                width: 65px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include("./page_all/header_page.php") ?>
    
    <!-- Main Content -->
    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fas fa-chalkboard-teacher"></i>
                Lecturer Dashboard
            </h1>
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search lecturers...">
                <a href="./action/createLecturer.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Lecturer
                </a>
            </div>
        </div>
        
        <!-- Stats Section -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php
                        // 查询讲师总数
                        $sql = "SELECT COUNT(*) AS total FROM Lecturer";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        echo $row['total'];
                        ?>
                    </div>
                    <div class="stat-label">Total Lecturers</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon full-time">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php
                        // 查询全职讲师数量
                        $sql = "SELECT COUNT(*) AS full_time FROM Lecturer WHERE position LIKE '%full time%'";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        echo $row['full_time'];
                        ?>
                    </div>
                    <div class="stat-label">Full-Time Lecturers</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon part-time">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php
                        // 查询兼职讲师数量
                        $sql = "SELECT COUNT(*) AS part_time FROM Lecturer WHERE position LIKE '%part time%'";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        echo $row['part_time'];
                        ?>
                    </div>
                    <div class="stat-label">Part-Time Lecturers</div>
                </div>
            </div>
        </div>
        
        <!-- Lecturer Table -->
        <div class="lecturer-table-container">
            <div class="table-header">
                <div class="table-title">Lecturers List</div>
            </div>
            
            <div class="table-responsive">
                <table class="lecturer-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Faculty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 查询讲师数据
                        $sql = "SELECT l.*, 
                                (SELECT COUNT(*) FROM Lecturer_Availability la WHERE la.lecturer_id = l.ID) AS total_slots
                                FROM Lecturer l";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // 修复1：使用正确的imagePath字段
                                echo "<tr>";
                                echo "<td>{$row['ID']}</td>";
                                echo "<td><img src='{$row['image']}' class='profile-img' alt='{$row['name']}'></td>";
                                echo "<td>{$row['name']}</td>";
                                echo "<td>{$row['position']}</td>";
                                echo "<td>{$row['Faculty']}</td>";
                                echo "<td class='actions'>";
                                
                                // 修复2：正确传递参数给JavaScript函数
                                echo "<button class='action-btn view-btn' onclick=\"showAvailability('lecturer','{$row['ID']}','{$row['name']}')\">";
                                echo "<i class='fas fa-calendar-alt'></i> View";
                                echo "</button>";
                                
                                echo "<button class='action-btn edit-btn'>";
                                echo "<i class='fas fa-edit'></i> Edit";
                                echo "</button>";
                                echo "<button class='action-btn delete-btn' onclick='confirmDelete(\"{$row['ID']}\", \"{$row['name']}\")'>";
                                echo "<i class='fas fa-trash'></i> Delete";
                                echo "</button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center; padding: 30px;'>No lecturers found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <p>Copyright © 2025 Ecalpal</p>
        <p>Timetable Management System v2.0</p>
    </footer>



    
    <script>     
        // 确认删除函数
        function confirmDelete(id, name) {
            if (confirm(`Are you sure you want to delete lecturer "${name}" (ID: ${id})? This action cannot be undone.`)) {
                // 在实际应用中，这里应该发送AJAX请求或重定向到删除脚本
                alert(`Lecturer ${name} (${id}) would be deleted in a real application.`);
                // window.location.href = `delete_lecturer.php?id=${id}`;
            }
        }
        
        // 搜索功能
        const searchBox = document.querySelector('.search-box');
        searchBox.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.lecturer-table tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const id = row.querySelector('td:first-child').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || id.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // 获取可用性数据的函数
        function fetchAvailability(type, id) {
            return fetch(`get_availability.php?type=${type}&id=${id}`)
                .then(response => response.json())
                .catch(error => {
                    console.error('Error fetching availability:', error);
                    return { error: 'Failed to fetch availability data' };
                });
        }

        // 显示可用时间模态框
        async function showAvailability(type, id, name) {
            const modal = document.getElementById('availability-modal');
            const modalTitle = document.getElementById('modal-title');
            
            // 设置标题
            modalTitle.textContent = `${name} Availability`;
            
            // 显示加载状态
            const timeGrid = document.getElementById('time-grid');
            timeGrid.innerHTML = '<div class="loading">Loading availability data...</div>';
            modal.style.display = 'flex';
            
            try {
                // 获取可用性数据
                const response = await fetchAvailability(type, id);
                
                if (response.error) {
                    timeGrid.innerHTML = `<div class="error">${response.error}</div>`;
                    return;
                }
                
                // 填充时间网格
                populateTimeGrid(response.availability);
            } catch (error) {
                console.error('Error:', error);
                timeGrid.innerHTML = '<div class="error">Error loading availability data</div>';
            }
        }
        
        // 填充时间网格
        function populateTimeGrid(availabilityData) {
            const timeGrid = document.getElementById('time-grid');
            timeGrid.innerHTML = '';
            
            // 确保网格列数正确（7天 + 时间列）
            timeGrid.style.gridTemplateColumns = 'repeat(8, 1fr)';
            
            // 创建网格标题
            const timeLabel = document.createElement('div');
            timeLabel.className = 'time-label';
            timeLabel.textContent = 'Time';
            timeGrid.appendChild(timeLabel);
            
            // 添加星期标题
            const days = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];
            days.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.className = 'day-header';
                dayHeader.textContent = day;
                timeGrid.appendChild(dayHeader);
            });
            
            // 创建时间单元格 - 16个时间段（半小时间隔）
            for (let i = 0; i < 16; i++) {
                // 计算时间：从9:00开始，每30分钟增加
                const startHour = 9 + Math.floor(i / 2);
                const startMinutes = (i % 2) * 30;
                const endHour = 9 + Math.floor((i + 1) / 2);
                const endMinutes = ((i + 1) % 2) * 30;
                const timeString = `${startHour}:${startMinutes === 0 ? '00' : startMinutes} ~ ${endHour}:${endMinutes === 0 ? '00' : endMinutes}`;
                
                // 时间标签
                const timeLabel = document.createElement('div');
                timeLabel.className = 'time-label';
                timeLabel.textContent = timeString;
                timeGrid.appendChild(timeLabel);
                
                // 每天的时间单元格
                days.forEach(day => {
                    const timeCell = document.createElement('div');
                    timeCell.className = 'time-cell';
                    
                    // 检查该时间段是否可用
                    if (availabilityData[day] && (availabilityData[day] >> i) & 1) {
                        timeCell.classList.add('available');
                    }
                    
                    timeGrid.appendChild(timeCell);
                });
            }
        }
        
        // 关闭模态框
        function closeModal() {
            document.getElementById('availability-modal').style.display = 'none';
        }
    </script>

    <!-- 可用时间模态框 -->
    <div id="availability-modal" class="modal">
        <div class="modal-content">
            <div id="modal-header" class="modal-header">
                <span id="modal-title">Availability</span>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="time-grid" class="time-grid"></div>
        </div>
    </div>
</body>
</html>



