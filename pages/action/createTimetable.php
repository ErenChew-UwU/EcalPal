<?php
require_once __DIR__ . "/../../dbconnect.php";

// 统计总数
$total_batches = $conn->query("SELECT COUNT(*) AS cnt FROM batch")->fetch_assoc()['cnt'];

// 统计已有时间表的批次数
$total_with_timetable = $conn->query("
    SELECT COUNT(DISTINCT b.ID) AS cnt
    FROM batch b
    JOIN timetable t ON b.ID = t.batch_id
    WHERE t.Active = 1
")->fetch_assoc()['cnt'];

// 统计有 current 科目的批次数
$total_with_current_subject = $conn->query("
    SELECT COUNT(DISTINCT b.ID) AS cnt
    FROM batch b
    JOIN batch_subject bs ON b.ID = bs.batch_id
    WHERE bs.status = 'current'
")->fetch_assoc()['cnt'];

// 查询批次详细信息
$sql = "
    SELECT 
        b.ID, 
        b.course_name, 
        b.intake_name,
        CASE WHEN EXISTS (
            SELECT 1 
            FROM timetable t 
            WHERE t.batch_id = b.ID AND t.Active = 1
        ) THEN 1 ELSE 0 END AS has_timetable,
        CASE WHEN EXISTS (
            SELECT 1 
            FROM batch_subject bs 
            WHERE bs.batch_id = b.ID AND bs.status = 'current'
        ) THEN 1 ELSE 0 END AS has_current_subject
    FROM batch b
    ORDER BY b.course_name, b.intake_name
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecalpal | Generate Timetable</title>
    <link rel="shortcut icon" href="../../src/ico/ico_logo_001.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../stylesheets/style_header.css">
    <link rel="stylesheet" href="../../stylesheets/style_all.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #eef2ff;
            --secondary-color: #3f37c9;
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f0f4f8, #e2e8f0);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-title {
            margin-bottom: 30px;
            text-align: center;
            padding: 20px 0;
        }
        
        .page-title h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--text-dark);
            position: relative;
            display: inline-block;
        }
        
        .page-title h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .page-title p {
            color: var(--text-light);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 20px auto 0;
        }
        
        /* 统计卡片样式 */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .stat-card.total::before { background: var(--primary-color); }
        .stat-card.with-timetable::before { background: var(--success-color); }
        .stat-card.with-subject::before { background: var(--warning-color); }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.total { background: rgba(67, 97, 238, 0.15); color: var(--primary-color); }
        .stat-icon.with-timetable { background: rgba(56, 176, 0, 0.15); color: var(--success-color); }
        .stat-icon.with-subject { background: rgba(255, 158, 0, 0.15); color: var(--warning-color); }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-dark);
        }
        
        .stat-label {
            font-size: 16px;
            color: var(--text-light);
            font-weight: 500;
        }
        
        /* 表格样式 */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .table-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table-header h2 {
            color: var(--text-dark);
            font-size: 1.4rem;
        }
        
        .batch-count {
            background: var(--primary-light);
            color: var(--primary-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        
        thead {
            background-color: var(--light-bg);
        }
        
        th {
            padding: 16px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 1px solid var(--border-color);
        }
        
        td {
            padding: 14px 15px;
        }
        
        /* 批次卡片样式 */
        .batch-card {
            background: white;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .batch-card.selectable:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .batch-card.selected {
            border: 2px solid var(--primary-color);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.3);
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .batch-card.not-selectable {
            background-color: #f8f9fa;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .batch-card.selected .selection-indicator {
            display: flex;
        }
        
        .selection-indicator {
            position: absolute;
            top: -1px;
            right: -1px;
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            border-bottom-left-radius: 12px;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .yes { 
            color: var(--success-color); 
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .no { 
            color: var(--danger-color); 
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        /* 按钮样式 */
        .form-actions {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            position: relative;
        }
        
        .selected-count {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-light);
            color: var(--primary-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn {
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(1px);
        }
        
        /* 加载动画 */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            backdrop-filter: blur(5px);
        }
        
        .spinner {
            width: 70px;
            height: 70px;
            border: 5px solid rgba(67, 97, 238, 0.2);
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        .loading-text {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* 响应式调整 */
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .page-title h1 {
                font-size: 2rem;
            }
            
            .form-actions {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            
            .selected-count {
                position: static;
                transform: none;
            }
        }
    </style>
</head>
<body>
    <?php include("../page_all/header_page_action.php"); ?>

    <div class="container">
        <div class="page-title">
            <h1>Select Generate Time Table</h1>
            <p>Click to select Batch, A blue border indicates it is selected</p>
        </div>

        <!-- 统计卡片 -->
        <div class="stats-container">
            <div class="stat-card total">
                <div class="stat-icon total">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php echo $total_batches; ?>
                    </div>
                    <div class="stat-label">Total Batch</div>
                </div>
            </div>
            
            <div class="stat-card with-timetable">
                <div class="stat-icon with-timetable">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php echo $total_with_timetable; ?>
                    </div>
                    <div class="stat-label">Total Batch Existing Time Table</div>
                </div>
            </div>
            
            <div class="stat-card with-subject">
                <div class="stat-icon with-subject">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php echo $total_with_current_subject; ?>
                    </div>
                    <div class="stat-label">Total Batch Have Current Subjects</div>
                </div>
            </div>
        </div>

        <!-- 批次表格 -->
        <div class="table-container">
            <div class="table-header">
                <h2>Batch List</h2>
                <div class="batch-count"><?php echo $total_batches; ?> Batch</div>
            </div>
            
            <form id="generateForm" action="generateTimetable.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Intake Date</th>
                            <th>Timetable Status</th>
                            <th>Subject Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { 
                            $isSelectable = $row['has_current_subject'];
                            $isSelected = false; // 初始未选中
                        ?>
                        <tr>
                            <td colspan="4" style="padding: 0 8px;">
                                <div class="batch-card <?php echo $isSelectable ? 'selectable' : 'not-selectable'; ?> <?php echo $isSelected ? 'selected' : ''; ?>" 
                                     data-id="<?php echo $row['ID']; ?>"
                                     data-selectable="<?php echo $isSelectable ? 'true' : 'false'; ?>">
                                     
                                    <div class="selection-indicator">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    
                                    <input type="hidden" name="batch_ids[]" value="" disabled>
                                    
                                    <table style="width: 100%; border-collapse: collapse; border-spacing: 0;">
                                        <tr>
                                            <td style="width: 30%; padding: 16px;">
                                                <div style="font-weight: 600; font-size: 18px;">
                                                    <?php echo htmlspecialchars($row['course_name']); ?>
                                                </div>
                                            </td>
                                            <td style="width: 25%; padding: 16px;">
                                                <div>
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <?php echo htmlspecialchars($row['intake_name']); ?>
                                                </div>
                                            </td>
                                            <td style="width: 25%; padding: 16px;">
                                                <?php if ($row['has_timetable']) { ?>
                                                    <span class="yes"><i class="fas fa-check-circle"></i> Generated (Overwritten)</span>
                                                <?php } else { ?>
                                                    <span class="no"><i class="fas fa-times-circle"></i> Not generated</span>
                                                <?php } ?>
                                            </td>
                                            <td style="width: 20%; padding: 16px;">
                                                <?php if ($row['has_current_subject']) { ?>
                                                    <span class="yes"><i class="fas fa-check-circle"></i> Exist Current Subject</span>
                                                <?php } else { ?>
                                                    <span class="no"><i class="fas fa-times-circle"></i> Not Current Subject</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </form>
        </div>

        <div class="form-actions">
            <div class="selected-count">
                <i class="fas fa-check-circle"></i>
                <span id="selected-counter">0</span> batch selected
            </div>
            <button type="button" class="btn btn-primary" onclick="submitForm()">
                <i class="fas fa-magic"></i> Generate Time Table
            </button>
        </div>
    </div>

    <!-- 加载层 -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Generating Timetable, Please wait...</div>
    </div>

    <script>
        // 存储选中状态的数组
        let selectedBatches = [];
        
        // 页面加载完成后初始化事件监听
        document.addEventListener('DOMContentLoaded', function() {
            // 添加批次卡片点击事件
            document.querySelectorAll('.batch-card.selectable').forEach(card => {
                card.addEventListener('click', function() {
                    const batchId = this.getAttribute('data-id');
                    
                    // 切换选中状态
                    if (this.classList.contains('selected')) {
                        this.classList.remove('selected');
                        selectedBatches = selectedBatches.filter(id => id !== batchId);
                    } else {
                        this.classList.add('selected');
                        selectedBatches.push(batchId);
                    }
                    
                    // 更新选中计数器
                    updateSelectedCounter();
                });
            });
        });
        
        // 更新选中批次计数器
        function updateSelectedCounter() {
            document.getElementById('selected-counter').textContent = selectedBatches.length;
        }
        
        // 提交表单
        function submitForm() {
            if (selectedBatches.length === 0) {
                alert('请至少选择一个批次来生成时间表');
                return false;
            }
            
            // 设置隐藏字段的值
            const hiddenInputs = document.querySelectorAll('input[type="hidden"][name="batch_ids[]"]');
            hiddenInputs.forEach(input => {
                input.disabled = true;
                input.removeAttribute('name');
            });
            
            // 为选中的批次创建隐藏字段
            selectedBatches.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'batch_ids[]';
                input.value = id;
                document.getElementById('generateForm').appendChild(input);
            });
            
            // 显示加载动画
            document.getElementById('loading-overlay').style.display = 'flex';
            
            // 提交表单
            document.getElementById('generateForm').submit();
        }
    </script>
</body>
</html>