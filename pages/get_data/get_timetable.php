<?php
// 文件：api/get_timetable.php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "root", "", "ecalpal");

// 获取参数
$timetable_id = $_GET['id'] ?? '';

if (!$timetable_id) {
    echo json_encode(['error' => 'Missing timetable ID']);
    exit;
}

// 查询 timetable 主信息
$timetable_query = $mysqli->prepare("SELECT * FROM timetable WHERE ID = ?");
$timetable_query->bind_param("s", $timetable_id);
$timetable_query->execute();
$timetable_result = $timetable_query->get_result()->fetch_assoc();

if (!$timetable_result) {
    echo json_encode(['error' => 'Timetable not found']);
    exit;
}

// 查询 batch
$batch_id = $timetable_result['batch_id'];
$batch_result = $mysqli->query("SELECT * FROM batch WHERE ID = '$batch_id'")->fetch_assoc();

// 查询 timetable slots
$slots_result = $mysqli->query("SELECT * FROM timetableslot WHERE timetable_id = '$timetable_id'");
$slots = [];
while ($row = $slots_result->fetch_assoc()) {
    $slots[] = $row;
}

// 查询 student
$students_result = $mysqli->query("SELECT name FROM student WHERE batch_id = '$batch_id'");
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// 查询 subject
$subject_ids = array_unique(array_column($slots, 'subject_id'));
$subjects = [];
foreach ($subject_ids as $sid) {
    $sres = $mysqli->query("SELECT * FROM subject WHERE ID = '$sid'")->fetch_assoc();
    $subjects[$sid] = $sres;
}

// 查询 lecturers
$lecturer_ids = array_unique(array_column($slots, 'lecturer_id'));
$lecturers = [];
foreach ($lecturer_ids as $lid) {
    $lres = $mysqli->query("SELECT * FROM lecturer WHERE ID = '$lid'")->fetch_assoc();
    $lecturers[$lid] = $lres;
}

// 查询 venues
$venue_ids = array_unique(array_column($slots, 'venue_id'));
$venues = [];
foreach ($venue_ids as $vid) {
    $vres = $mysqli->query("SELECT * FROM venue WHERE ID = '$vid'")->fetch_assoc();
    $venues[$vid] = $vres;
}

// 输出 JSON
echo json_encode([
    'timetable' => $timetable_result,
    'batch' => $batch_result,
    'slots' => $slots,
    'students' => $students,
    'subjects' => $subjects,
    'lecturers' => $lecturers,
    'venues' => $venues
]);


?>