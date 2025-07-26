<?php
require_once '../../dbconnect.php';

// 获取基础数据
$subjects = [];
$lecturers = [];
$venues = [];

// 获取课程数据
$result = $conn->query("SELECT * FROM subject");
while ($row = $result->fetch_assoc()) {
    $subjects[$row['ID']] = $row;
}

// 获取讲师数据
$result = $conn->query("SELECT * FROM lecturer");
while ($row = $result->fetch_assoc()) {
    $lecturers[$row['ID']] = $row;
}

// 获取场地数据
$result = $conn->query("SELECT * FROM venue");
while ($row = $result->fetch_assoc()) {
    $venues[$row['ID']] = $row;
}

// 获取批次数据
$batches = [];
$result = $conn->query("SELECT * FROM batch");
while ($row = $result->fetch_assoc()) {
    $batches[$row['ID']] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>大学时间表生成器</title>
    <style>
        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .timetable th, .timetable td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            vertical-align: top;
            height: 100px;
        }
        
        .timetable th {
            background-color: #f2f2f2;
        }
        
        .timetable-cell {
            position: relative;
            height: 100%;
        }
        
        .timetable-slot {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 4px;
            padding: 5px;
            cursor: move;
            height: 95%;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .timetable-slot.dragging {
            opacity: 0.5;
        }
        
        .timetable-cell.drop-target {
            background-color: #fff8e1;
        }
        
        .subject {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .controls {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        button {
            padding: 10px 15px;
            margin-right: 10px;
            background-color: #4285f4;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #3367d6;
        }
        
        #save-btn {
            background-color: #0f9d58;
        }
        
        #save-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        select {
            padding: 10px;
            margin-right: 15px;
            width: 250px;
            font-size: 16px;
        }
        
        .undo-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>大学时间表生成器</h1>
    
    <div class="controls">
        <select id="batch-select">
            <?php foreach ($batches as $id => $batch): ?>
                <option value="<?= $id ?>"><?= $batch['intake_name'] ?> - <?= $batch['course_name'] ?></option>
            <?php endforeach; ?>
        </select>
        
        <button id="generate-btn">生成时间表</button>
        <button id="save-btn" disabled>保存时间表</button>
    </div>
    
    <div id="timetable-container">
        <!-- 时间表将在这里渲染 -->
    </div>
    
    <script>
        // 预加载数据
        const subjects = <?= json_encode($subjects) ?>;
        const lecturers = <?= json_encode($lecturers) ?>;
        const venues = <?= json_encode($venues) ?>;
    </script>
    
    <script src="../../javascripts/timetable-ga.js"></script>
</body>
</html>