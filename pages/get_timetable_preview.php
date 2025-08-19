<?php
include_once("../dbconnect.php");

if (!isset($_GET['timetable_id'])) {
    echo "<p class='error'>Invalid request</p>";
    exit;
}

$timetable_id = $conn->real_escape_string($_GET['timetable_id']);

$sql = "SELECT ts.*, s.fullname AS subject_name, v.Name AS venue_name, l.name AS lecturer_name
        FROM timetableslot ts
        JOIN subject s ON ts.subject_id = s.ID
        JOIN venue v ON ts.venue_id = v.ID
        JOIN lecturer l ON ts.lecturer_id = l.ID
        WHERE ts.timetable_id = '$timetable_id'";


$res = $conn->query($sql);

if (!$res || $res->num_rows === 0) {
    echo "<p class='error'>No data found for timetable ID: $timetable_id</p>";
    exit;
}

// 构建时间格数据 [day][slot] => data[]
$slots = [];
while ($row = $res->fetch_assoc()) {
    $day = $row['day'];
    $timeSlot = intval($row['timeSlot']);
    $duration = intval($row['duration']);

    for ($i = 0; $i < $duration; $i++) {
        $slots[$timeSlot + $i][$day] = [
            'subject' => $row['subject_name'],
            'lecturer' => $row['lecturer_name'] ?? '',
            'venue' => $row['venue_name'] ?? '',
            'type' => strtolower($row['format_type'] ?? 'lecture'), // default: lecture
            'start_slot' => $timeSlot,
            'is_head' => $i === 0,
            'span' => $duration
        ];
    }
}

// 渲染为 .time-grid
$days = ['MO', 'TU', 'WE', 'TH', 'FR'];
$dayNames = ['MO' => 'Monday', 'TU' => 'Tuesday', 'WE' => 'Wednesday', 'TH' => 'Thursday', 'FR' => 'Friday'];

echo "<div class='time-grid'>";

// 第一行标题
echo "<div class='time-label'>Time</div>";
foreach ($days as $day) {
    echo "<div class='day-header'><span class='day-name'>{$dayNames[$day]}</span></div>";
}

// 每 slot 一行
for ($slot = 1; $slot <= 16; $slot++) {
    $startTime = date("G:i", strtotime("09:00") + $slot * 30 * 60);
    $endTime = date("G:i", strtotime("09:00") + ($slot + 1) * 30 * 60);
    echo "<tr><td>{$startTime} ~ {$endTime}</td>";


    foreach ($days as $day) {
        if (!empty($slots[$slot][$day])) {
            $data = $slots[$slot][$day];

            // 如果不是该课程的开始格，就跳过（避免重复）
            if (!$data['is_head']) continue;

            $typeClass = in_array($data['type'], ['lecture', 'lab']) ? $data['type'] : 'free';

            echo "<div class='time-slot $typeClass' style='grid-row: span {$data['span']};'>";
            echo "<div class='slot-content'>";
            echo "<div class='slot-title'>{$data['subject']}</div>";
            if (!empty($data['lecturer'])) echo "<div class='slot-details'>{$data['lecturer']}</div>";
            if (!empty($data['venue'])) echo "<div class='slot-details'>Room: {$data['venue']}</div>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='time-slot free'></div>";
        }
    }
}

echo "</div>";
?>
