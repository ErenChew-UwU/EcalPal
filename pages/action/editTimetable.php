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
                    'batchId' => $batchId
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
        'id' => $timetable_id,
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
        --card-shadow: 0 10px 20px rgba(0,0,0,0.05), 0 6px 6px rgba(0,0,0,0.04);
        --hover-shadow: 0 15px 30px rgba(0,0,0,0.1), 0 5px 15px rgba(0,0,0,0.07);
    }
    
    .source-info {
      background: var(--primary-light);
      color: var(--primary-color);
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 500;
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
    
    .external-events {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    
    .external-event {
      padding: 12px;
      margin: 4px 0;
      background: var(--primary-light);
      border-radius: 8px;
      cursor: grab;
      border-left: 3px solid var(--primary-color);
      transition: all 0.2s;
    }
    
    .external-event:hover {
      transform: translateX(4px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }
    
    .event-subject {
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 8px;
    }
    
    .event-details {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      font-size: 14px;
      color: var(--text-light);
    }
    
    .event-detail {
      display: flex;
      align-items: center;
      gap: 4px;
      background: rgba(255,255,255,0.7);
      padding: 4px 8px;
      border-radius: 4px;
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
    
    .fc .fc-timegrid-slot {
      height: 50px;
    }
    
    .fc .fc-event {
      border-radius: 6px;
      padding: 8px;
      font-size: 14px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      border: none;
    }
    
    .fc-event-content {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    
    .fc-event-title {
      font-weight: 600;
      margin-bottom: 2px;
    }
    
    .fc-event-details {
      font-size: 12px;
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    
    .fc-event-detail {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    
    .fc .fc-daygrid-day-frame {
      background: #fff;
    }
    
    .event-tooltip {
      position: absolute;
      background: rgba(0,0,0,0.8);
      color: white;
      padding: 8px;
      border-radius: 4px;
      font-size: 12px;
      z-index: 100;
      pointer-events: none;
      max-width: 300px;
    }
    
    @media (max-width: 1024px) {
      .page-wrap {
        flex-direction: column;
      }
      
      .right {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <?php include("../page_all/header_page_action.php"); ?>
<!-- <div class="header">
  <div class="logo">
    <div class="logo-icon">
      <i class="fas fa-calendar-alt"></i>
    </div>
    <div>
      <strong>Ecalpal</strong>
      <div class="source-info">
        <?php echo $sourceInfo; ?>
      </div>
    </div>
  </div>
  <div>
    <button onclick="window.location.href='./dashboardTimetable.php'" class="btn btn-outline">
      <i class="fas fa-arrow-left"></i> Back to Dashborad
    </button>
  </div>
</div> -->

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
        <div class="calendar-card">
        <div class="calendar-title">
          <div><?php echo $calendar['title']; ?></div>
          <div class="batch-info">
            <span class="batch-id">
              <?php 
                if (!empty($calendar['batchId'])) {
                  echo $calendar['batchId'];
                } elseif (!empty($calendar['timetable_id'])) {
                  echo $calendar['timetable_id'];
                }
              ?>
            </span>
            <i class="fas fa-info-circle"></i>
            <span><?php echo count($calendar['events']); ?> Subjects</span>
          </div>
        </div>

        <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="width:80px;">Time</th>
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
                echo "<tr>";
                echo "<td>{$startTime} - {$endTime}</td>";

                // 循环 5 天（MO, TU, WE, TH, FR）
                $days = ['MO', 'TU', 'WE', 'TH', 'FR'];
                foreach ($days as $dayCode) {
                    // 找出该时间段的课程
                    $cellContent = '';
                    foreach ($calendar['events'] as $event) {
                        if ($event['extendedProps']['day'] === $dayCode) {
                            // 转成时间槽编号
                            $eventSlot = intval($event['extendedProps']['timeSlot']);
                            $eventDuration = intval($event['extendedProps']['duration_slots']);
                            if ($slot >= $eventSlot && $slot < $eventSlot + $eventDuration) {
                                $cellContent = $event['extendedProps']['subjectName'] . "<br>"
                                              . "<small>" . $event['extendedProps']['venueName'] . "</small><br>"
                                              . "<small>" . $event['extendedProps']['lecturerName'] . "</small>";
                                break;
                            }
                        }
                    }
                    echo "<td>" . ($cellContent ?: '') . "</td>";
                }
                echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="right">
    <!-- <div class="card">
      <div class="card-title">
        <i class="fas fa-plus-circle"></i> 添加新课程
      </div>
      <div class="external-events" id="externalEvents">
        <?php 
        // 为每个科目创建可拖动事件
        $uniqueSubjects = [];
        foreach($subjects as $id => $name) {
          if(!in_array($id, $uniqueSubjects)) {
            $uniqueSubjects[] = $id;
            $color = getColorForSubject($id);
            echo '<div class="external-event" data-subject-id="'.$id.'" data-subject-name="'.$name.'" style="border-left-color: '.$color.';">';
            echo '<div class="event-subject">'.$name.'</div>';
            echo '<div class="event-details">';
            echo '<div class="event-detail"><i class="fas fa-clock"></i> 2 课时</div>';
            echo '</div>';
            echo '</div>';
          }
        }
        ?>
      </div>
    </div> -->
    
    <div class="card">
      <div class="card-title">
        <i class="fas fa-save"></i> Save
      </div>
      <div class="save-area">
        <div class="unsaved-indicator">
          <div class="unsaved-icon">
            <i class="fas fa-exclamation"></i>
          </div>
          <div class="unsaved-text">
            <div class="unsaved-title">Unsaved changes</div>
            <div><span class="unsaved-count" id="unsavedCount">0</span> Modifications</div>
          </div>
        </div>
        
        <div class="save-actions">
          <button id="saveAll" class="btn btn-primary" style="flex:1;">
            <i class="fas fa-save"></i> Save to database
          </button>
          <button id="resetBtn" class="btn btn-outline">
            <i class="fas fa-undo"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const calendarsData = <?php echo json_encode($calendars, JSON_UNESCAPED_UNICODE); ?>;
let unsavedCount = 0;
const unsavedElement = document.getElementById('unsavedCount');
let calendarObjs = {};

// 标记未保存更改
function markUnsaved(delta = 1) {
  unsavedCount = Math.max(0, unsavedCount + delta);
  unsavedElement.textContent = unsavedCount;
  
  // 更新顶部指示器
  document.querySelector('.unsaved-indicator').style.display = 
    unsavedCount > 0 ? 'flex' : 'none';
}

// 离开页面警告
window.addEventListener('beforeunload', e => {
  if (unsavedCount > 0) {
    e.preventDefault();
    e.returnValue = '您有未保存的更改，确定要离开吗？';
  }
});

// 初始化日历
document.addEventListener('DOMContentLoaded', function() {
  // 创建日历实例
  calendarsData.forEach((cal, idx) => {
    const calendarEl = document.getElementById(`calendar-${idx}`);
    
    if (!calendarEl) return;
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'timeGridWeek',
      firstDay: 1,
      slotDuration: '00:30:00',
      allDaySlot: false,
      expandRows: true,
      headerToolbar: {
        left: 'title',
        center: '',
        right: 'today prev,next'
      },
      initialDate: '2025-08-11',
      slotMinTime: '08:00:00',
      slotMaxTime: '18:30:00',
      editable: true,
      droppable: true,
      events: cal.events || [],
      eventReceive: (info) => {
        // 设置新事件的属性
        const subjectId = info.draggedEl.dataset.subjectId;
        const subjectName = info.draggedEl.dataset.subjectName;
        
        info.event.setExtendedProp('subjectId', subjectId);
        info.event.setExtendedProp('subjectName', subjectName);
        info.event.setProp('title', subjectName);
        info.event.setProp('backgroundColor', getColorForSubject(subjectId));
        
        markUnsaved(1);
      },
      eventDrop: () => markUnsaved(1),
      eventResize: () => markUnsaved(1),
      eventChange: () => markUnsaved(1),
      eventClick: (info) => {
        // 显示事件详情
        const event = info.event;
        const props = event.extendedProps;
        
        const tooltip = document.createElement('div');
        tooltip.className = 'event-tooltip';
        tooltip.innerHTML = `
          <div><strong>${props.subjectName}</strong></div>
          <div>地点: ${props.venueName || '未设置'}</div>
          <div>讲师: ${props.lecturerName || '未设置'}</div>
          <div>时间: ${formatTime(event.start)} - ${formatTime(event.end)}</div>
          <div>时长: ${props.duration_slots} 课时</div>
        `;
        
        tooltip.style.left = `${info.jsEvent.pageX + 10}px`;
        tooltip.style.top = `${info.jsEvent.pageY + 10}px`;
        document.body.appendChild(tooltip);
        
        // 5秒后移除提示
        setTimeout(() => {
          if (document.body.contains(tooltip)) {
            document.body.removeChild(tooltip);
          }
        }, 5000);
      },
      locale: 'zh-cn',
      eventContent: function(arg) {
        const subject = arg.event.extendedProps.subjectName || '未知科目';
        const venue = arg.event.extendedProps.venueName || '未设置地点';
        const lecturer = arg.event.extendedProps.lecturerName || '未设置讲师';
        
        return {
          html: `
            <div class="fc-event-content">
              <div class="fc-event-title">${subject}</div>
              <div class="fc-event-details">
                <div class="fc-event-detail"><i class="fas fa-map-marker-alt"></i> ${venue}</div>
                <div class="fc-event-detail"><i class="fas fa-user"></i> ${lecturer}</div>
              </div>
            </div>
          `
        };
      }
    });
    
    calendar.render();
    calendarObjs[cal.id] = calendar;
  });

  // 使外部事件可拖动
  document.querySelectorAll('.external-event').forEach(el => {
    el.draggable = true;
    
    el.addEventListener('dragstart', (ev) => {
      ev.dataTransfer.setData('text/plain', JSON.stringify({
        subjectId: el.dataset.subjectId,
        subjectName: el.dataset.subjectName
      }));
    });
  });

  // 保存按钮事件
  document.getElementById('saveAll').addEventListener('click', async () => {
    if (unsavedCount === 0) {
      alert('没有需要保存的更改');
      return;
    }
    
    const payload = [];
    
    calendarsData.forEach(cal => {
      const calendar = calendarObjs[cal.id];
      if (!calendar) return;
      
      const events = calendar.getEvents().map(event => {
        const dowMap = {'1': 'MO', '2': 'TU', '3': 'WE', '4': 'TH', '5': 'FR'};
        const dow = event.start.getDay();
        const day = dowMap[String(dow)] || 'MO';
        
        // 计算时间槽（从8:00开始，每30分钟一个槽）
        const startMinutes = (event.start.getHours() * 60 + event.start.getMinutes()) - (8 * 60);
        const slot = Math.round(startMinutes / 30);
        
        const durationSlots = event.extendedProps.duration_slots || 
          Math.max(1, Math.round(((event.end - event.start) / (1000 * 60)) / 30));
        
        return {
          uuid: event.id,
          batchId: cal.batchId || null,
          timetable_id: cal.timetable_id || null,
          subjectId: event.extendedProps.subjectId || null,
          venueId: event.extendedProps.venueId || null,
          lecturerId: event.extendedProps.lecturerId || null,
          day: day,
          timeSlot: slot,
          duration: durationSlots
        };
      });
      
      payload.push({
        calendar: cal.id,
        batchId: cal.batchId || null,
        timetable_id: cal.timetable_id || null,
        events: events
      });
    });
    
    try {
      // 显示加载状态
      const saveBtn = document.getElementById('saveAll');
      const originalText = saveBtn.innerHTML;
      saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中...';
      saveBtn.disabled = true;
      
      const res = await fetch('saveTimetable.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({payload})
      });
      
      const data = await res.json();
      
      if (data.status === 'success') {
        alert('保存成功！');
        unsavedCount = 0;
        markUnsaved(0);
      } else {
        alert('保存失败: ' + (data.message || '未知错误'));
      }
    } catch (error) {
      alert('保存失败: ' + error.message);
    } finally {
      saveBtn.innerHTML = originalText;
      saveBtn.disabled = false;
    }
  });

  // 重置按钮事件
  document.getElementById('resetBtn').addEventListener('click', () => {
    if (unsavedCount > 0) {
      if (confirm('您有未保存的更改，确定要重置吗？')) {
        location.reload();
      }
    } else {
      location.reload();
    }
  });
});

// 辅助函数：格式化时间
function formatTime(date) {
  return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// 获取科目颜色
function getColorForSubject(subjectId) {
  const colors = [
    '#4361ee', '#3f37c9', '#4cc9f0', '#4895ef', '#560bad',
    '#7209b7', '#b5179e', '#f72585', '#e63946', '#f77f00',
    '#fcbf49', '#2a9d8f', '#588157', '#3a5a40', '#8ac926'
  ];
  const index = [...subjectId].reduce((sum, char) => sum + char.charCodeAt(0), 0) % colors.length;
  return colors[index];
}
</script>
</body>
</html>
