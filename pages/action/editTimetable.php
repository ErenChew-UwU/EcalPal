<?php
require_once __DIR__ . "/../../dbconnect.php"; // 数据库连接

// 将 timeslot 转为 HH:MM:SS
function timeslot_to_time($slot) {
    $slot = intval($slot);
    $start_minutes = (8 * 60) + ($slot * 30);
    $h = intdiv($start_minutes, 60);
    $m = $start_minutes % 60;
    return sprintf("%02d:%02d:00", $h, $m);
}

// 获取辅助名称映射
$subjects = [];
$venues = [];
$lecturers = [];
$batches =[];

$res = $conn->query("SELECT ID, fullname FROM subject");
while ($r = $res->fetch_assoc()) { 
    $subjects[$r['ID']] = $r['fullname']; 
}

$res = $conn->query("SELECT ID, Name FROM venue");
while ($r = $res->fetch_assoc()) { 
    $venues[$r['ID']] = $r['Name']; 
}

$res = $conn->query("SELECT ID, name FROM lecturer");
while ($r = $res->fetch_assoc()) { 
    $lecturers[$r['ID']] = $r['name']; 
}

$res = $conn->query("SELECT ID, course_name FROM batch");
while ($r = $res->fetch_assoc()) { 
    $batches[$r['ID']] = $r['course_name']; 
}

// 判断模式
$mode = '';
$calendars = [];
$sourceInfo = '';

// ... 前面的代码保持不变 ...

if (isset($_GET['from']) && $_GET['from'] === 'generate') {
    $sourceInfo = 'Import from the Timetable Generator';
    $tempFile = __DIR__ . "/temp/timetable.json";
    if (file_exists($tempFile)) {
        $json = file_get_contents($tempFile);
        $genes = json_decode($json, true);
        if (is_array($genes)) {
            $mode = 'generated';
            $by_batch = [];
            foreach ($genes as $g) {
                $batch = $g['batchId'] ?? 'UNKNOWN';
                $by_batch[$batch][] = $g;
            }
            foreach ($by_batch as $batchId => $list) {
                $events = [];
                foreach ($list as $g) {
                    $start = timeslot_to_time($g['timeSlot']);
                    $end_slot = intval($g['timeSlot']) + intval($g['duration']);
                    $end = timeslot_to_time($end_slot);
                    $day_map = [
                        'MO' => '2025-08-11', 
                        'TU' => '2025-08-12', 
                        'WE' => '2025-08-13', 
                        'TH' => '2025-08-14', 
                        'FR' => '2025-08-15'
                    ];
                    $date = $day_map[$g['day']] ?? $day_map['MO'];
                    
                    // 获取完整名称 - 修复这里的变量名
                    $subjectName = $subjects[$g['subjectId']] ?? $g['subjectId'];
                    $venueName = $venues[$g['venueId']] ?? $g['venueId'];
                    $lecturerName = $lecturers[$g['lecturerId']] ?? $g['lecturerId'];
                    
                    $events[] = [
                        'id' => $g['UUID'],
                        'title' => $subjectName, // 修复这里只显示科目名
                        'start' => $date . "T" . $start,
                        'end' => $date . "T" . $end,
                        'backgroundColor' => getColorForSubject($g['subjectId']),
                        'borderColor' => '#fff',
                        'textColor' => '#fff',
                        'extendedProps' => [
                            'batchId' => $g['batchId'],
                            'subjectId' => $g['subjectId'],
                            'subjectName' => $subjectName,
                            'venueId' => $g['venueId'],
                            'venueName' => $venueName,
                            'lecturerId' => $g['lecturerId'],
                            'lecturerName' => $lecturerName,
                            'duration_slots' => intval($g['duration']),
                            'day' => $g['day'],
                            'timeSlot' => $g['timeSlot']
                        ]
                    ];
                }
                $calendars[] = [
                    'id' => $batchId,
                    'title' => $batches[$batchId],
                    'events' => $events,
                    'batch_id' => $batchId
                ];
            }
        }
    }
} elseif (!empty($_GET['timetable'])) {
    $sourceInfo = 'Timetable loaded from database';
    // 从数据库读取已有时间表
    $mode = 'db';
    $timetable_id = $conn->real_escape_string($_GET['timetable']);
    $sql = "SELECT * FROM timetableslot WHERE timetable_id = '$timetable_id'";
    $res = $conn->query($sql);
    $events = [];
    $timetable_info_sql = "SELECT batch_id FROM timetable WHERE ID = '$timetable_id'";
    $timetable_info_res = $conn->query($timetable_info_sql);
    $timetable_info = $timetable_info_res->fetch_assoc();
    $batch_id = $timetable_info['batch_id'] ?? null;
    while ($g = $res->fetch_assoc()) {
        $start = timeslot_to_time($g['timeSlot']);
        $end_slot = intval($g['timeSlot']) + intval($g['duration']);
        $end = timeslot_to_time($end_slot);
        $day_map = [
            'MO' => '2025-08-11',
            'TU' => '2025-08-12',
            'WE' => '2025-08-13',
            'TH' => '2025-08-14',
            'FR' => '2025-08-15'
        ];
        $date = $day_map[$g['day']] ?? $day_map['MO'];
        
        // 获取完整名称
        $subjectName = $subjects[$g['subject_id']] ?? $g['subject_id'];
        $venueName = $venues[$g['venue_id']] ?? $g['venue_id'];
        $lecturerName = $lecturers[$g['lecturer_id']] ?? $g['lecturer_id'];
        
        $title = "{$subjectName}";
        
        $events[] = [
            'id' => 'db-' . $g['id'],
            'title' => $title,
            'start' => $date . "T" . substr($start,0,8),
            'end' => $date . "T" . substr($end,0,8),
            'backgroundColor' => getColorForSubject($g['subject_id']),
            'borderColor' => '#fff',
            'textColor' => '#fff',
            'extendedProps' => [
                'batchId' => $g['batch_id'],
                'subjectId' => $g['subject_id'],
                'subjectName' => $subjectName,
                'venueId' => $g['venue_id'],
                'venueName' => $venueName,
                'lecturerId' => $g['lecturer_id'],
                'lecturerName' => $lecturerName,
                'duration_slots' => intval($g['duration']),
                'day' => $g['day'],
                'timeSlot' => $g['timeSlot']
            ]
        ];
    }
    $calendars[] = [
        'batch_id' => $batch_id,
        'title' => $batches[$batch_id],
        'events' => $events,
        'timetable_id' => $timetable_id
    ];
} else {
    $mode = 'none';
}

// 为不同科目生成不同颜色
function getColorForSubject($subjectId) {
    $colors = [
        '#4361ee', '#3f37c9', '#4cc9f0', '#4895ef', '#560bad',
        '#7209b7', '#b5179e', '#f72585', '#e63946', '#f77f00',
        '#fcbf49', '#2a9d8f', '#588157', '#3a5a40', '#8ac926'
    ];
    $index = crc32($subjectId) % count($colors);
    return $colors[$index];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Ecalpal | Edit Timetable</title>
  <link rel="shortcut icon" href="../../src/ico/ico_logo_001.png">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>
  <link rel="stylesheet" href="../../stylesheets/style_header.css">
  <link rel="stylesheet" href="../../stylesheets/style_all.css">
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
            --card-radius: 12px;
            --card-shadow: 0 10px 20px rgba(0,0,0,0.05), 0 6px 6px rgba(0,0,0,0.04);
            --hover-shadow: 0 15px 30px rgba(0,0,0,0.1), 0 5px 15px rgba(0,0,0,0.07);
        }
        
        .page-wrap {
            display: flex;
            gap: 24px;
            padding: 24px;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .left {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .calendar-card {
            background: #fff;
            padding: 20px;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
        }
        
        .calendar-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .batch-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            color: var(--text-light);
        }
        
        .batch-id {
            background: var(--primary-light);
            color: var(--primary-color);
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .right {
            width: 360px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .card {
            background: #fff;
            padding: 20px;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card-title i {
            color: var(--primary-color);
        }
        
        .save-area {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .unsaved-indicator {
            padding: 12px;
            background: #fffbeb;
            border-radius: 8px;
            border: 1px solid #fde68a;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .unsaved-icon {
            width: 36px;
            height: 36px;
            background: #fbbf24;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
        }
        
        .unsaved-text {
            flex: 1;
        }
        
        .unsaved-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .unsaved-count {
            font-weight: 700;
            color: #d97706;
            font-size: 18px;
        }
        
        .save-actions {
            display: flex;
            gap: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }
        
        .empty-icon {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }
        
        .empty-text {
            font-size: 18px;
            margin-bottom: 24px;
        }
        
        .source-info {
            background: var(--primary-light);
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        /* 时间表内容区域 */
        .time-table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--card-radius);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .time-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            table-layout: fixed;
        }
        
        .time-table th, .time-table td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid var(--border-color);
            height: 80px;
            vertical-align: top;
        }
        
        .time-table th {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .time-table th.time-header {
            background: var(--light-bg);
            color: var(--text-dark);
            font-weight: 700;
            width: 100px;
        }
        
        .time-table tr:nth-child(even) {
            background-color: #f9fbfd;
        }
        
        .time-table tr:hover {
            background-color: #f0f7ff;
        }
        
        .time-cell {
            min-height: 80px;
            vertical-align: top;
            position: relative;
            transition: all 0.2s;
        }
        
        .time-cell:hover {
            background-color: #f0f7ff;
        }
        
        .time-cell.dragging-over {
            background-color: rgba(67, 97, 238, 0.2);
            box-shadow: inset 0 0 0 2px var(--primary-color);
        }
        
        .time-cell-content {
            padding: 10px;
            border-radius: 6px;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            height: 100%;
        }
        
        .time-cell.lecture .time-cell-content {
            background: linear-gradient(135deg, #eef2ff, #dbeafe);
            border-left: 4px solid var(--accent-color);
        }
        
        .time-cell.lab .time-cell-content {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-left: 4px solid var(--success-color);
        }
        
        .time-cell.free .time-cell-content {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-left: 4px solid #cbd5e1;
        }
        
        .cell-title {
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-dark);
            font-size: 14px;
        }
        
        .cell-details {
            color: var(--text-light);
            font-size: 12px;
            line-height: 1.4;
        }
        
        .cell-time {
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
        
        .drag-handle {
            position: absolute;
            bottom: 8px;
            right: 8px;
            color: var(--primary-color);
            cursor: grab;
            opacity: 0.5;
            transition: opacity 0.2s;
        }
        
        .drag-handle:hover {
            opacity: 1;
        }
        
        .controls-panel {
            background: var(--light-bg);
            padding: 15px;
            border-radius: var(--card-radius);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .controls-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .drag-hint {
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            color: #6c757d;
            padding: 15px;
            margin-top: 15px;
            text-align: center;
        }
        
        .drag-hint i {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
            color: var(--primary-color);
        }
        
        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: #fff;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
        }
        
        .btn-outline:hover {
            background: var(--light-bg);
        }
        
        .btn-danger {
            background: #e63946;
            color: #fff;
        }
        
        .btn-danger:hover {
            background: #d00000;
        }
        
        .status-indicator {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .status-saving {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-unsaved {
            background: #fffbeb;
            color: #854d0e;
        }

        .conflict-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(229, 56, 59, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #e5383b;
        font-weight: bold;
        font-size: 12px;
        z-index: 10;
        border-radius: 6px;
    }
    
    .toast {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #e5383b;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .toast.show {
        opacity: 1;
    }

    .btn-saving {
        background-color: #6c757d !important;
        cursor: not-allowed;
    }
    
    .saved-indicator {
        padding: 12px;
        background: #dcfce7;
        border-radius: 8px;
        border: 1px solid #bbf7d0;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
    }
    
    .saved-icon {
        width: 36px;
        height: 36px;
        background: #22c55e;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
    }
        
        @media (max-width: 1024px) {
            .page-wrap {
                flex-direction: column;
            }
            
            .right {
                width: 100%;
            }
            
            .time-table {
                min-width: 600px;
            }
        }
  </style>
</head>
<body>
  <?php include("../page_all/header_page_action.php"); ?>

<div class="page-wrap">
        <div class="left" id="leftCalendars">
            <?php if ($mode === 'none'): ?>
                <div class="card">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="empty-text">Not have Timetable Data</div>
                        <p>Please open an existing timetable from the dashboard or generate a new timetable</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($calendars as $idx => $calendar): ?>
                    <div class="calendar-card" 
                        data-batch-id="<?= $calendar['batch_id'] ?? '' ?>" 
                        data-timetable-id="<?= $calendar['timetable_id'] ?? '' ?>">

                        <div class="calendar-title">
                            <div><?php echo $calendar['title']; ?></div>
                            <div class="batch-info">
                                <span class="batch-id">
                                    <?php 
                                        if (!empty($calendar['batch_id'])) {
                                            echo $calendar['batch_id'];
                                        } elseif (!empty($calendar['timetable_id'])) {
                                            echo $calendar['timetable_id'];
                                        }
                                    ?>
                                </span>
                                <i class="fas fa-info-circle"></i>
                                <span><?php echo count($calendar['events']); ?> Subjects</span>
                            </div>
                        </div>
        
                        <div class="time-table-container">
                            <table class="time-table">
                                <thead>
                                    <tr>
                                        <th class="time-header">Time</th>
                                        <th>Monday</th>
                                        <th>Tuesday</th>
                                        <th>Wednesday</th>
                                        <th>Thursday</th>
                                        <th>Friday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // 生成 16 个时间段（9:00 到 17:00，每 30 分钟一格）
                                    for ($slot = 1; $slot < 17; $slot++) {
                                        $startTime = date("H:i", strtotime("09:00") + ($slot - 1) * 30 * 60);
                                        $endTime = date("H:i", strtotime("09:00") + ($slot) * 30 * 60);
                                        echo "<tr data-slot='{$slot}'>";
                                        echo "<td class='time-header'>{$startTime}<br>{$endTime}</td>";
        
                                        // 循环 5 天（MO, TU, WE, TH, FR）
                                        $days = ['MO', 'TU', 'WE', 'TH', 'FR'];
                                        foreach ($days as $dayCode) {
                                            // 找出该时间段的课程
                                            $cellContent = '';
                                            $typeClass = 'free';
                                            $details = '';
                                            $isStartCell = false;
                                            $eventId = '';
                                            
                                            foreach ($calendar['events'] as $event) {
                                                if ($event['extendedProps']['day'] === $dayCode) {
                                                    // 转成时间槽编号
                                                    $eventSlot = intval($event['extendedProps']['timeSlot']);
                                                    $eventDuration = intval($event['extendedProps']['duration_slots']);
                                                    if ($slot >= $eventSlot && $slot < $eventSlot + $eventDuration) {
                                                        $subjectId = $event['extendedProps']['subjectId'];
                                                        $subjectName = $event['extendedProps']['subjectName'];
                                                        $venueId = $event['extendedProps']['venueId'];
                                                        $venueName = $event['extendedProps']['venueName'];
                                                        $lecturerId = $event['extendedProps']['lecturerId'];
                                                        $lecturerName = $event['extendedProps']['lecturerName'];
                                                        $eventId = $event['id'];
                                                        
                                                        $cellContent = "<div class='cell-title'>{$subjectName}</div>";
                                                        $cellContent .= "<div class='cell-details'>{$venueName}</div>";
                                                        $cellContent .= "<div class='cell-details'>{$lecturerName}</div>";
                                                        
                                                        // 添加拖动手柄（仅限第一格）
                                                        if ($slot === $eventSlot) {
                                                            $cellContent .= "<div class='drag-handle'><i class='fas fa-grip-lines'></i></div>";
                                                            $isStartCell = true;
                                                        }
                                                        
                                                        $details = "data-subject-id='{$subjectId}' data-subject='{$subjectName}' data-venue-id='{$venueId}' data-venue='{$venueName}' data-lecturer-id='{$lecturerId}'  data-lecturer='{$lecturerName}' data-eventid='{$eventId}' data-start-slot='{$eventSlot}' data-duration='{$eventDuration}'";
                                                        $typeClass = 'lecture';
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            $dragClass = $isStartCell ? 'draggable' : '';
                                            echo "<td class='time-cell {$typeClass} {$dragClass}' {$details} data-slot='{$slot}' data-day='{$dayCode}'>";
                                            echo "<div class='time-cell-content'>";
                                            echo $cellContent ?: '<div class="cell-details">Free Period</div>';
                                            echo "</div>";
                                            echo "</td>";
                                        }
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    
        <div class="right">
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-save"></i> Save Timetable
                </div>
                <div class="save-area">
                    <div class="unsaved-indicator">
                        <div class="unsaved-icon">
                            <i class="fas fa-exclamation"></i>
                        </div>
                        <div class="unsaved-text">
                            <div class="unsaved-title">Unsaved changes</div>
                            <div><span class="unsaved-count" id="unsavedCountRight">0</span> modifications</div>
                        </div>
                    </div>
                    
                    <div class="save-actions">
                        <button id="saveAll" class="btn btn-primary" style="flex:1;">
                            <i class="fas fa-save"></i> Save to database
                        </button>
                        <button id="resetBtn" class="btn btn-outline">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-info-circle"></i> Editing Instructions
                </div>
                <div class="instruction-list">
                    <div class="instruction-item">
                        <i class="fas fa-grip-lines text-primary"></i>
                        <span>Drag classes by the handle icon to move them</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
            let unsavedCount = 0;
            const unsavedElements = [
                document.getElementById('unsavedCount'),
                document.getElementById('unsavedCountRight')
            ];
            
            // 更新未保存计数
            function updateUnsavedCount(delta = 1) {
                unsavedCount = Math.max(0, unsavedCount + delta);
                unsavedElements.forEach(el => {
                    if (el) el.textContent = unsavedCount;
                });
            }
            
            // 拖拽功能实现
            let draggedEvent = null;
            
            // 设置可拖动单元格
            document.querySelectorAll('.time-cell.draggable').forEach(cell => {
                cell.setAttribute('draggable', true);
                
                cell.addEventListener('dragstart', function(e) {
                    const calendarCard = this.closest('.calendar-card');
                    const batchId = calendarCard.dataset.batchId;
                    
                    draggedEvent = {
                        id: this.dataset.eventid,
                        subject: this.dataset.subject,
                        subjectId: this.dataset.subjectId,
                        venue: this.dataset.venue,
                        venueId: this.dataset.venueId,
                        lecturer: this.dataset.lecturer,
                        lecturerId: this.dataset.lecturerId,
                        day: this.dataset.day,
                        startSlot: parseInt(this.dataset.startSlot),
                        duration: parseInt(this.dataset.duration),
                        originalCell: this,
                        batchId: batchId
                    };
                    
                    e.dataTransfer.setData('text/plain', 'dragging');
                    this.style.opacity = '0.4';
                });
                
                cell.addEventListener('dragend', function() {
                    this.style.opacity = '1';
                    document.querySelectorAll('.time-cell').forEach(c => {
                        c.classList.remove('dragging-over');
                    });
                });
            });
            
            // 设置可放置目标
            document.querySelectorAll('.time-cell').forEach(cell => {
                cell.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragging-over');
                });
                
                cell.addEventListener('dragleave', function() {
                    this.classList.remove('dragging-over');
                });
                
                cell.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragging-over');
                    
                    if (!draggedEvent) return;
                    
                    // 获取目标单元格所属的批次ID
                    const targetCalendarCard = this.closest('.calendar-card');
                    const targetBatchId = targetCalendarCard.dataset.batchId;
                    
                    // 检查是否在同一批次内
                    if (draggedEvent.batchId !== targetBatchId) {
                        alert("Cannot move to another batch!");
                        draggedEvent = null;
                        return;
                    }
                    
                    const targetDay = this.dataset.day;
                    const targetSlot = parseInt(this.dataset.slot);
                    
                    // 检查目标位置是否有效
                    if (!targetDay || isNaN(targetSlot)) return;
                    
                    // 直接移动事件到新位置（不再检查冲突）
                    moveEvent(draggedEvent, targetDay, targetSlot);
                    updateUnsavedCount();
                    
                    // 清除拖拽状态
                    draggedEvent = null;
                });
            });

            // 根据日期和时间槽获取单元格
            function getCellByDayAndSlot(batchId, day, slot) {
                const dayMap = { 'MO': 1, 'TU': 2, 'WE': 3, 'TH': 4, 'FR': 5 };
                const colIndex = dayMap[day];
                if (!colIndex) return null;
                
                // 只在当前批次内查找
                const calendarCard = document.querySelector(`.calendar-card[data-batch-id="${batchId}"]`);
                if (!calendarCard) return null;
                
                const row = calendarCard.querySelector(`tr[data-slot="${slot}"]`);
                if (!row) return null;
                
                return row.cells[colIndex];
            }
            
            // 移动事件函数
            function moveEvent(eventData, newDay, newSlot) {
                // 1. 清除原始位置的事件
                clearEvent(eventData.id, eventData.batchId);
                
                // 2. 在新位置创建事件
                createEvent({
                    id: eventData.id,
                    subject: eventData.subject,
                    subjectId: eventData.subjectId,
                    venue: eventData.venue,
                    venueId: eventData.venueId,
                    lecturer: eventData.lecturer,
                    lecturerId: eventData.lecturerId,
                    day: newDay,
                    startSlot: newSlot,
                    duration: eventData.duration,
                    batchId: eventData.batchId
                });
            }
            
            // 清除事件函数
            function clearEvent(eventId, batchId) {
                const calendarCard = document.querySelector(`.calendar-card[data-batch-id="${batchId}"]`);
                if (!calendarCard) return;

                calendarCard.querySelectorAll(`[data-eventid="${eventId}"]`).forEach(cell => {
                    cell.classList.remove('lecture');
                    cell.classList.add('free');
                    cell.classList.remove('draggable');
                    cell.removeAttribute('draggable');
                    cell.removeAttribute('data-subject-id');
                    cell.removeAttribute('data-subject');
                    cell.removeAttribute('data-venue-id');
                    cell.removeAttribute('data-venue');
                    cell.removeAttribute('data-lecturer-id');
                    cell.removeAttribute('data-lecturer');
                    cell.removeAttribute('data-eventid');
                    cell.removeAttribute('data-start-slot');
                    cell.removeAttribute('data-duration');
                    
                    cell.innerHTML = '<div class="time-cell-content"><div class="cell-details">Free Period</div></div>';
                });
            }
            
            // 创建事件函数
            function createEvent(eventData) {
                const calendarCard = document.querySelector(`.calendar-card[data-batch-id="${eventData.batchId}"]`);
                if (!calendarCard) return;
                
                const days = ['MO', 'TU', 'WE', 'TH', 'FR'];
                const dayIndex = days.indexOf(eventData.day) + 1;
                
                for (let i = 0; i < eventData.duration; i++) {
                    const slot = eventData.startSlot + i;
                    const row = calendarCard.querySelector(`tr[data-slot="${slot}"]`);
                    
                    if (!row) continue;
                    
                    const targetCell = row.cells[dayIndex];
                    
                    if (!targetCell) continue;
                    
                    // 设置单元格内容
                    targetCell.classList.remove('free');
                    targetCell.classList.add('lecture');
                    
                    // 仅第一个单元格设置为可拖动
                    if (i === 0) {
                        targetCell.classList.add('draggable');
                        targetCell.setAttribute('draggable', 'true');
                        targetCell.innerHTML = `
                            <div class="time-cell-content">
                                <div class="cell-title">${eventData.subject}</div>
                                <div class="cell-details">${eventData.venue}</div>
                                <div class="cell-details">${eventData.lecturer}</div>
                                <div class="drag-handle"><i class="fas fa-grip-lines"></i></div>
                            </div>
                        `;
                    } else {
                        targetCell.innerHTML = `
                            <div class="time-cell-content">
                                <div class="cell-title">${eventData.subject}</div>
                                <div class="cell-details">${eventData.venue}</div>
                                <div class="cell-details">${eventData.lecturer}</div>
                            </div>
                        `;
                    }
                    
                    // 设置数据属性
                    targetCell.dataset.subjectId = eventData.subjectId;
                    targetCell.dataset.venueId = eventData.venueId;
                    targetCell.dataset.lecturerId = eventData.lecturerId;
                    targetCell.dataset.subject = eventData.subject;
                    targetCell.dataset.venue = eventData.venue;
                    targetCell.dataset.lecturer = eventData.lecturer;
                    targetCell.dataset.eventid = eventData.id;
                    targetCell.dataset.day = eventData.day;
                    targetCell.dataset.startSlot = eventData.startSlot;
                    targetCell.dataset.duration = eventData.duration;
                    targetCell.dataset.slot = slot;
                }
                
                // 重新添加事件监听器
                const newDraggableCell = document.querySelector(`[data-eventid="${eventData.id}"].draggable`);
                if (newDraggableCell) {
                    newDraggableCell.addEventListener('dragstart', function(e) {
                        const calendarCard = this.closest('.calendar-card');
                        const batchId = calendarCard.dataset.batchId;
                        
                        draggedEvent = {
                            id: this.dataset.eventid,
                            subject: this.dataset.subject,
                            subjectId: this.dataset.subjectId,
                            venue: this.dataset.venue,
                            venueId: this.dataset.venueId,
                            lecturer: this.dataset.lecturer,
                            lecturerId: this.dataset.lecturerId,
                            day: this.dataset.day,
                            startSlot: parseInt(this.dataset.startSlot),
                            duration: parseInt(this.dataset.duration),
                            originalCell: this,
                            batchId: batchId
                        };
                        
                        e.dataTransfer.setData('text/plain', 'dragging');
                        this.style.opacity = '0.4';
                    });
                    
                    newDraggableCell.addEventListener('dragend', function() {
                        this.style.opacity = '1';
                        document.querySelectorAll('.time-cell').forEach(c => {
                            c.classList.remove('dragging-over');
                        });
                    });
                }
            }
            
            document.getElementById('saveAll').addEventListener('click', function() {
                const saveBtn = this;
                const originalHTML = saveBtn.innerHTML;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                saveBtn.disabled = true;
                
                // 收集所有修改
                const allChanges = [];
                
                // 遍历所有批次的时间表
                document.querySelectorAll('.calendar-card').forEach(card => {
                    const batchId = card.dataset.batchId;
                    const timetableId = card.dataset.timetableId;

                    card.querySelectorAll('.time-cell.lecture[data-eventid]').forEach(cell => {
                        if (cell.classList.contains('draggable')) {
                            allChanges.push({
                                eventId: cell.dataset.eventid,
                                batchId: batchId || null,       // generate 模式用 batchId
                                timetableId: timetableId || null, // db 模式用 timetableId
                                subjectId: cell.dataset.subjectId,
                                venueId: cell.dataset.venueId,
                                lecturerId: cell.dataset.lecturerId,
                                day: cell.dataset.day,
                                timeSlot: parseInt(cell.dataset.startSlot),
                                duration: parseInt(cell.dataset.duration)
                            });
                        }
                    });
                });

                console.log("即将发送的数据 allChanges：", JSON.stringify(allChanges, null, 2));


                
                // 发送到服务器
                fetch('saveTimetable.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        changes: allChanges,
                        source: '<?php echo $mode; ?>'  // 'generated' 或 'db'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Timetable saved successfully!');
                        unsavedCount = 0;
                        updateUnsavedCount(0);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving.');
                })
                .finally(() => {
                    saveBtn.innerHTML = originalHTML;
                    saveBtn.disabled = false;
                });

            });
            
            // 重置按钮事件
            document.getElementById('resetBtn').addEventListener('click', function() {
                if (unsavedCount > 0) {
                    if (confirm('You have unsaved changes. Are you sure you want to reset?')) {
                        location.reload();
                    }
                } else {
                    location.reload();
                }
            });
        });
</script>
</body>
</html>
