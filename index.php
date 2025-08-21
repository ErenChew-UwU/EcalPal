<?php
include_once("./dbconnect.php");

// 获取最后修改的时间表及对应的 batch 名称
$sql = "
    SELECT timetable.ID AS timetable_id, timetable.batch_id, timetable.lastModifyTime, batch.course_name 
    FROM timetable 
    JOIN batch ON timetable.batch_id = batch.ID 
    ORDER BY timetable.lastModifyTime DESC 
    LIMIT 1
";

$result = $conn->query($sql);
$timetable = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecalpal | Index</title>
    <link rel="shortcut icon" href="./src/ico/ico_logo_001.png">
    <link rel="stylesheet" href="./stylesheets/style_header.css">
    <link rel="stylesheet" href="./stylesheets/style_all.css">
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
        }
        /* Main Content Styles */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .dashboard-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: var(--light-bg);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .btn {
            padding: 8px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--accent-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: #3b5fdb;
            box-shadow: 0 4px 10px rgba(78, 115, 223, 0.3);
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Timetable Card */
        .timetable-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .timetable-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 4px;
        }
        
        .meta-value {
            font-size: 18px;
            font-weight: 500;
        }
        
        .timetable-actions {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        
        .btn-secondary {
            background: var(--light-bg);
            color: var(--text-dark);
            border: 1px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #edf2f7;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .btn-view {
            background: var(--success-color);
            color: white;
        }
        
        .btn-view:hover {
            background: #2f855a;
            box-shadow: 0 4px 10px rgba(56, 161, 105, 0.3);
        }
        
        .btn-edit {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-edit:hover {
            background: #d69e2e;
            box-shadow: 0 4px 10px rgba(236, 201, 75, 0.3);
        }
        
        /* Database Card */
        .database-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .db-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: var(--light-bg);
            border-radius: 10px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-dark);
        }
        
        .db-item:hover:not(.future) {
            background: #edf2f7;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .db-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }
        
        .db-icon.lecturer { color: var(--accent-color); }
        .db-icon.student { color: var(--success-color); }
        .db-icon.subject { color: var(--warning-color); }
        .db-icon.venue { color: var(--danger-color); }
        .db-icon.batch { color: #805ad5; }
        
        .db-title {
            font-weight: 600;
            font-size: 16px;
        }
        
        /* Quick Function Section */
        .section-divider {
            display: flex;
            align-items: center;
            margin: 40px 0;
        }
        
        .divider-line {
            flex-grow: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider-text {
            padding: 0 20px;
            font-weight: 600;
            color: var(--text-light);
            font-size: 18px;
        }
        
        .quick-functions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }
        
        .function-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
        }

        .future {
            background: #e0e0e0; /* 灰色背景 */
            color: #999; /* 灰色文字 */
            cursor: not-allowed;
            opacity: 0.6; /* 整体变暗 */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        
        .function-card:hover:not(.future) {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .function-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        
        .function-icon.generate { background: rgba(78, 115, 223, 0.15); color: var(--accent-color); }
        .function-icon.export { background: rgba(56, 161, 105, 0.15); color: var(--success-color); }
        .function-icon.import { background: rgba(236, 201, 75, 0.15); color: var(--warning-color); }
        .function-icon.manage { background: rgba(229, 62, 62, 0.15); color: var(--danger-color); }
        .function-icon.report { background: rgba(128, 90, 213, 0.15); color: #805ad5; }
        .function-icon.setting { background: rgba(66, 153, 225, 0.15); color: #4299e1; }
        
        .function-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .function-desc {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.5;
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
        
        /* Responsive Design */
        @media (max-width: 1100px) {
            .dashboard-section {
                grid-template-columns: 1fr;
            }
            
            .quick-functions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
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
            
            .database-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-functions {
                grid-template-columns: 1fr;
            }
            
            .timetable-meta {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include("./pages/page_all/header_index.php"); ?>
    
    <!-- Main Content -->
    <div class="container">
        <div class="dashboard-section">
            <!-- Timetable Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-calendar-alt"></i>  Timetable</h2>
                    <a href="./pages/dashboardTimetable.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="timetable-info">
                        <h3>Last Modified Timetable</h3>
                        <div class="timetable-meta">
                            <div class="meta-item">
                                <div class="meta-label">Batch Name of Timetable</div>
                                <div class="meta-value"><?php echo htmlspecialchars($timetable['course_name']); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Last Modified</div>
                                <div class="meta-value"><?php echo date("M d, Y H:i", strtotime($timetable['lastModifyTime'])); ?></div>
                            </div>
                        </div>
                        <div class="timetable-actions">
                            <a href="./pages/action/viewTimetable.php?timetable=<?php echo $timetable['timetable_id']; ?>" class="btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="./pages/action/editTimetable.php?timetable=<?php echo $timetable['timetable_id']; ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Database Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-database"></i>  Database</h2>
                </div>
                <div class="card-body">
                    <div class="database-grid">
                        <!-- <a href="./pages/dashboardBatch.php" class="db-item future">
                            <div class="db-icon batch">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="db-title">Batch</div>
                        </a> -->
                        <a href="./pages/dashboardLecturer.php" class="db-item">
                            <div class="db-icon lecturer">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="db-title">Lecturer</div>
                        </a>
                        <!-- <a href="./pages/dashboardStudent.php" class="db-item future">
                            <div class="db-icon student">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="db-title">Student</div>
                        </a> -->
                        <!-- <a href="./pages/dashboardSubject.php" class="db-item future">
                            <div class="db-icon subject">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="db-title">Subject</div>
                        </a> -->
                        <!-- <a href="./pages/dashboardVenue.php" class="db-item future">
                            <div class="db-icon venue">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="db-title">Venue</div>
                        </a> -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Function Section -->
        <div class="section-divider">
            <div class="divider-line"></div>
            <div class="divider-text">Quick Function</div>
            <div class="divider-line"></div>
        </div>
        
        <div class="quick-functions">
            <div class="function-card">
                <div class="function-icon generate">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <h3 class="function-title">Generate Timetable</h3>
                <p class="function-desc">Automatically create optimized schedules based on current data</p>
            </div>
            
            <!-- <div class="function-card future">
                <div class="function-icon export">
                    <i class="fas fa-file-export"></i>
                </div>
                <h3 class="function-title">Export Data</h3>
                <p class="function-desc">Export timetable data to Excel, PDF or other formats</p>
            </div> -->
            
            <!-- <div class="function-card future">
                <div class="function-icon import">
                    <i class="fas fa-file-import"></i>
                </div>
                <h3 class="function-title">Import Data</h3>
                <p class="function-desc">Import course data from external sources</p>
            </div> -->
            
            <!-- <div class="function-card future">
                <div class="function-icon manage">
                    <i class="fas fa-user-cog"></i>
                </div>
                <h3 class="function-title">Manage Users</h3>
                <p class="function-desc">Add, edit or remove system users and permissions</p>
            </div> -->
            
            <!-- <div class="function-card future">
                <div class="function-icon report">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="function-title">Generate Reports</h3>
                <p class="function-desc">Create detailed reports on resource utilization</p>
            </div> -->
            
            <!-- <div class="function-card future">
                <div class="function-icon setting">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <h3 class="function-title">System Settings</h3>
                <p class="function-desc">Configure system preferences and options</p>
            </div> -->
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <p>Copyright © 2025 Ecalpal</p>
        <p>Timetable Management System v2.0</p>
    </footer>
</body>
<script>
document.querySelector('.function-card .function-icon.generate').parentElement
    .addEventListener('click', function() {
        window.location.href = './pages/action/createTimetable.php';
    });
</script>
</html>