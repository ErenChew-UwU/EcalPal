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

// 判断模式
$mode = '';
$calendars = [];
$sourceInfo = '';

if (isset($_GET['from']) && $_GET['from'] === 'generate') {
    $sourceInfo = '从时间表生成工具导入';
    // 从 temp 文件读取 GA 生成的结果
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
                    
                    // 获取完整名称
                    $subjectName = $subjects[$g['subjectId']] ?? $g['subjectId'];
                    $venueName = $venues[$g['venueId']] ?? $g['venueId'];
                    $lecturerName = $lecturers[$g['lecturerId']] ?? $g['lecturerId'];
                    
                    $title = "{$subjectName} · {$venueName} · {$lecturerName}";
                    
                    $events[] = [
                        'id' => $g['UUID'],
                        'title' => $title,
                        'start' => $date . "T" . substr($start,0,8),
                        'end' => $date . "T" . substr($end,0,8),
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
                            'duration_slots' => intval($g['duration'])
                        ]
                    ];
                }
                $calendars[] = [
                    'id' => $batchId,
                    'title' => "批次 {$batchId}",
                    'events' => $events,
                    'batchId' => $batchId
                ];
            }
        }
    }
} elseif (!empty($_GET['timetable_id'])) {
    $sourceInfo = '从数据库加载的时间表';
    // 从数据库读取已有时间表
    $mode = 'db';
    $timetable_id = $conn->real_escape_string($_GET['timetable_id']);
    $sql = "SELECT * FROM timetableslot WHERE timetable_id = '$timetable_id'";
    $res = $conn->query($sql);
    $events = [];
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
        
        $title = "{$subjectName} · {$venueName} · {$lecturerName}";
        
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
                'duration_slots' => intval($g['duration'])
            ]
        ];
    }
    $calendars[] = [
        'id' => $timetable_id,
        'title' => "时间表 #{$timetable_id}",
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
  <title>编辑时间表</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>
  <style>
    :root {
      --primary-color: #4361ee;
      --primary-light: #eef2ff;
      --secondary-color: #3f37c9;
      --text-dark: #2d3748;
      --text-light: #718096;
      --light-bg: #f8fafc;
      --border-color: #e2e8f0;
      --card-shadow: 0 6px 16px rgba(0,0,0,0.08);
      --card-radius: 12px;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background: #f0f4f8;
      color: var(--text-dark);
      line-height: 1.6;
    }
    
    .header {
      padding: 16px 24px;
      background: #fff;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .logo-icon {
      width: 40px;
      height: 40px;
      background: var(--primary-light);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary-color);
      font-size: 20px;
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
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    
    .external-event:hover {
      transform: translateX(4px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }
    
    .event-subject {
      font-weight: 600;
      color: var(--text-dark);
    }
    
    .event-details {
      display: flex;
      gap: 12px;
      font-size: 14px;
      color: var(--text-light);
    }
    
    .event-detail {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    
    .event-actions {
      display: flex;
      gap: 8px;
      margin-top: 8px;
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
      padding: 4px 6px;
      font-size: 14px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    
    .fc .fc-event-title {
      font-weight: 500;
      margin-bottom: 2px;
    }
    
    .fc .fc-event-detail {
      font-size: 12px;
      display: flex;
      gap: 6px;
    }
    
    .fc .fc-daygrid-day-frame {
      background: #fff;
    }
  </style>
</head>
<body>
<div class="header">
  <div class="logo">
    <div class="logo-icon">
      <i class="fas fa-calendar-alt"></i>
    </div>
    <div>
      <strong>时间表编辑系统</strong>
      <div class="source-info">
        <?php echo $sourceInfo; ?>
      </div>
    </div>
  </div>
  <div>
    <button onclick="window.location.href='./dashboardTimetable.php'" class="btn btn-outline">
      <i class="fas fa-arrow-left"></i> 返回仪表盘
    </button>
  </div>
</div>

<div class="page-wrap">
  <div class="left" id="leftCalendars">
    <?php if ($mode === 'none'): ?>
      <div class="card">
        <div class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-calendar-times"></i>
          </div>
          <div class="empty-text">没有时间表数据</div>
          <p>请从仪表盘打开已有时间表或生成新的时间表</p>
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
                    echo "时间表ID: " . $calendar['timetable_id'];
                  }
                ?>
              </span>
              <i class="fas fa-info-circle"></i>
              <span><?php echo count($calendar['events']); ?> 个课程</span>
            </div>
          </div>
          <div id="calendar-<?php echo $idx; ?>" style="min-height: 500px;"></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="right">
    <div class="card">
      <div class="card-title">
        <i class="fas fa-plus-circle"></i> 添加新课程
      </div>
      <div class="external-events">
        <div class="external-event">
          <div class="event-subject">数学基础</div>
          <div class="event-details">
            <div class="event-detail">
              <i class="fas fa-map-marker-alt"></i> 教室101
            </div>
            <div class="event-detail">
              <i class="fas fa-user"></i> 张教授
            </div>
          </div>
          <div class="event-actions">
            <button class="btn btn-primary" style="flex:1;">
              <i class="fas fa-plus"></i> 添加
            </button>
          </div>
        </div>
        
        <div class="external-event">
          <div class="event-subject">英语高级</div>
          <div class="event-details">
            <div class="event-detail">
              <i class="fas fa-map-marker-alt"></i> 语言实验室
            </div>
            <div class="event-detail">
              <i class="fas fa-user"></i> 李教授
            </div>
          </div>
          <div class="event-actions">
            <button class="btn btn-primary" style="flex:1;">
              <i class="fas fa-plus"></i> 添加
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card">
      <div class="card-title">
        <i class="fas fa-save"></i> 保存更改
      </div>
      <div class="save-area">
        <div class="unsaved-indicator">
          <div class="unsaved-icon">
            <i class="fas fa-exclamation"></i>
          </div>
          <div class="unsaved-text">
            <div class="unsaved-title">未保存的更改</div>
            <div><span class="unsaved-count">3</span> 个修改</div>
          </div>
        </div>
        
        <div class="save-actions">
          <button id="saveAll" class="btn btn-primary" style="flex:1;">
            <i class="fas fa-save"></i> 保存到数据库
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
const unsavedFlag = document.querySelector('.unsaved-count');

// 标记未保存更改
function markUnsaved(delta = 1) {
  unsavedCount = Math.max(0, unsavedCount + delta);
  unsavedFlag.textContent = unsavedCount;
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
  const calendarObjs = {};
  
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
      eventReceive: () => markUnsaved(1),
      eventDrop: () => markUnsaved(1),
      eventResize: () => markUnsaved(1),
      eventChange: () => markUnsaved(1),
      locale: 'zh-cn',
      eventContent: function(arg) {
        const subject = arg.event.extendedProps.subjectName || '未知科目';
        const venue = arg.event.extendedProps.venueName || '未知地点';
        const lecturer = arg.event.extendedProps.lecturerName || '未知讲师';
        
        return {
          html: `
            <div class="fc-event-title">${subject}</div>
            <div class="fc-event-detail">
              <span><i class="fas fa-map-marker-alt"></i> ${venue}</span>
              <span><i class="fas fa-user"></i> ${lecturer}</span>
            </div>
          `
        };
      }
    });
    
    calendar.render();
    calendarObjs[cal.id] = calendar;
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
</script>
</body>
</html>