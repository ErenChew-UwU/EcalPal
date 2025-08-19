<?php
include_once("../dbconnect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecalpal | Timetable Dashboard</title>
    <link rel="shortcut icon" href="../src/ico/ico_logo_001.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../stylesheets/style_header.css">
    <link rel="stylesheet" href="../stylesheets/style_all.css">
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
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 14px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 1;
        }

        .action-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
            z-index: -1;
        }

        .action-btn:hover:before {
            transform: translateX(0);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .action-btn:active {
            transform: translateY(1px);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .action-btn i {
            transition: all 0.3s ease;
        }

        .preview-btn {
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            color: white;
            border: 1px solid rgba(67, 97, 238, 0.3);
        }

        .preview-btn:hover {
            background: linear-gradient(135deg, #3b5fdb 0%, #3730a8 100%);
        }

        .preview-btn:hover i {
            transform: scale(1.15) rotate(5deg);
        }

        .view-btn {
            background: linear-gradient(135deg, #43ee73 0%, #37c957 100%);
            color: white;
            border: 1px solid rgba(67, 238, 121, 0.3);
        }

        .view-btn:hover {
            background: linear-gradient(135deg, #3bdb66 0%, #30a844 100%);
        }

        .view-btn:hover i {
            transform: scale(1.15) rotate(5deg);
        }

        .edit-btn {
            background: linear-gradient(135deg, #ffb300 0%, #ff9e00 100%);
            color: #fff;
            border: 1px solid rgba(236, 201, 75, 0.3);
        }

        .edit-btn:hover {
            background: linear-gradient(135deg, #e6a100 0%, #e58f00 100%);
        }

        .edit-btn:hover i {
            transform: rotate(15deg) scale(1.15);
        }

        .delete-btn {
            background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
            color: white;
            border: 1px solid rgba(229, 62, 62, 0.3);
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
        }

        .delete-btn:hover i {
            transform: scale(1.15);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0) rotate(0); }
            20% { transform: translateX(-3px) rotate(-5deg); }
            40% { transform: translateX(3px) rotate(5deg); }
            60% { transform: translateX(-3px) rotate(-5deg); }
            80% { transform: translateX(3px) rotate(5deg); }
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
        .stat-icon.vacant { background: rgba(56, 161, 105, 0.15); color: var(--success-color); }
        
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
            max-width: 860px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            transform: translateY(-20px);
            animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            justify-content: center;
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
    <?php include("./page_all/header_page-timetable.php") ?>
    
    <!-- Main Content -->
    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fas fa-calendar-alt"></i>
                Time Table Dashboard
            </h1>
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search Timetable... (eg T0001)">
                <a href="./action/createTimetable.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Generate Timetable
                </a>
            </div>
        </div>
        
        <!-- Stats Section -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php
                        $sql = "SELECT COUNT(*) AS total FROM timetable";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc() ?? 0;
                        echo $row['total'];
                        ?>
                    </div>
                    <div class="stat-label">Total Timetable</div>
                </div>
            </div>
            <?php
            $sql = "
            SELECT COUNT(*) AS vacant_batches
            FROM batch b
            WHERE EXISTS (
                SELECT 1 FROM batch_subject bs
                WHERE bs.batch_id = b.ID AND bs.status = 'current'
            )
            AND NOT EXISTS (
                SELECT 1 FROM timetable t
                WHERE t.batch_id = b.ID
            )";
            $res = $conn->query($sql);
            $row = $res->fetch_assoc();
            $vacantBatchCount = $row['vacant_batches'] ?? 0;
            ?>
            
            <div class="stat-card" <?php if ($vacantBatchCount != 0) echo 'style="border: 2px solid #e53935; background-color: #ffeaea;"'?>>
                <div class="stat-icon vacant" <?php if ($vacantBatchCount != 0) echo 'style="background: rgba(101, 0, 0, 0.15); color: var(--danger-color);"'?>>
                    <?php
                    if ($vacantBatchCount != 0) {
                    ?>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?php } else { ?>
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <?php } ?>
                </div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php
                        echo $vacantBatchCount;
                        ?>
                    </div>
                    <div class="stat-label">Total Vacant Timetable</div>
                </div>
            </div>
        </div>
        
        <!-- Timetable Table -->
        <div class="lecturer-table-container">
            <div class="table-header">
                <div class="table-title">Timetable List</div>
            </div>
            
            <div class="table-responsive">
                <table class="lecturer-table">
                    <thead>
                        <tr>
                            <th>Timetable ID</th>
                            <th>Batch Name</th>
                            <th>Create Time</th>
                            <th>Last Modified</th>
                            <th>Class Slots</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 查询 timetable 数据及其关联的 batch 名称 + slot 数量
                        $sql = "
                            SELECT t.ID AS timetable_id, 
                                b.course_name AS batch_name, 
                                t.CreateTime, 
                                t.lastModifyTime,
                                (SELECT COUNT(*) FROM timetableslot ts WHERE ts.timetable_id = t.ID) AS slot_count
                            FROM timetable t
                            JOIN batch b ON t.batch_id = b.ID
                        ";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>{$row['timetable_id']}</td>";
                                echo "<td>{$row['batch_name']}</td>";
                                echo "<td>" . date("Y-m-d H:i", strtotime($row['CreateTime'])) . "</td>";
                                echo "<td>" . date("Y-m-d H:i", strtotime($row['lastModifyTime'])) . "</td>";
                                echo "<td>{$row['slot_count']}</td>";
                                echo "<td class='actions'>";
                                
                                // Preview 按钮
                                echo "<button class='action-btn preview-btn' onclick=\"showTable('{$row['timetable_id']}')\">";
                                echo "<i class='fas fa-calendar-alt'></i> Preview";
                                echo "</button>";

                                // View 按钮
                                echo "<a href='./action/viewTimetable.php?timetable={$row['timetable_id']}' class='action-btn view-btn'>";
                                echo "<i class='fas fa-calendar-alt'></i> View";
                                echo "</a> ";
                                
                                // Edit 按钮
                                echo "<a href='./action/editTimetable.php?timetable={$row['timetable_id']}' class='action-btn edit-btn'>";
                                echo "<i class='fas fa-edit'></i> Edit";
                                echo "</a> ";

                                // Delete 按钮（可选加入 confirm）
                                echo "<button class='action-btn delete-btn' onclick='confirmDelete(\"{$row['timetable_id']}\")'>";
                                echo "<i class='fas fa-trash'></i> Delete";
                                echo "</button>";

                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center; padding: 30px;'>No timetables found</td></tr>";
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
            if (confirm(`Some features are still under development and will be available in future updates.`)) {
                // 在实际应用中，这里应该发送AJAX请求或重定向到删除脚本
                // alert(`Lecturer ${name} (${id}) would be deleted in a real application.`);
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

        function showTable(timetableId) {
            const modal = document.getElementById('timetable-modal');
            const content = document.getElementById('timetable-content');
            const title = document.getElementById('timetable-title');

            // 设置标题
            title.textContent = `Timetable: ${timetableId}`;
            
            // 显示加载中
            content.innerHTML = '<div class="loading">Loading timetable...</div>';
            modal.style.display = 'flex';

            // 请求 PHP API 获取时间表内容
            fetch(`get_timetable_preview.php?timetable_id=${timetableId}`)
                .then(res => res.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(err => {
                    content.innerHTML = `<div class="error">Error loading timetable</div>`;
                });
        }

        function closeTimetableModal() {
            document.getElementById('timetable-modal').style.display = 'none';
        }
    </script>

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
                <div id="timetable-content" class="time-grid">
                </div>
            </div>

            <div class="modal-footer">
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color lecture-legend"></div>
                        <span>Lecture</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color free-legend"></div>
                        <span>Free Period</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>
</html>



