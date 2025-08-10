<?php
// save_timetable.php
require_once __DIR__ . "/../../dbconnect.php";
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['payload'])) {
    echo json_encode(['status'=>'error','message'=>'No payload']);
    exit;
}

// helper to generate new timetable ID like T0006
function generate_new_timetable_id($conn) {
    $res = $conn->query("SELECT ID FROM timetable ORDER BY ID DESC LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $last = $row['ID']; // like T0005
        $num = intval(substr($last,1)) + 1;
    } else {
        $num = 1;
    }
    return 'T' . str_pad($num, 4, '0', STR_PAD_LEFT);
}

// Begin transaction
$conn->begin_transaction();
try {
    foreach ($input['payload'] as $cal) {
        $timetable_id = $cal['timetable_id'] ?? null;
        $batchId = $cal['batchId'] ?? null;
        $events = $cal['events'] ?? [];

        if ($timetable_id) {
            // ensure timetable exists
            // delete old slots (simple)
            $stmt = $conn->prepare("DELETE FROM timetableslot WHERE timetable_id = ?");
            $stmt->bind_param("s", $timetable_id);
            $stmt->execute();
        } else {
            // create a new timetable for this batch
            $timetable_id = generate_new_timetable_id($conn);
            $now = date('Y-m-d H:i:s');
            $start_date = date('Y-m-d'); // you may want custom start date
            $duration_weeks = 14; // default
            $stmt = $conn->prepare("INSERT INTO timetable (ID, batch_id, start_date, duration_weeks, CreateTime, lastModifyTime, Active) VALUES (?,?,?,?,?,?,1)");
            $stmt->bind_param("sssiss", $timetable_id, $batchId, $start_date, $duration_weeks, $now, $now);
            $stmt->execute();
        }

        // Insert all events
        $insertStmt = $conn->prepare("INSERT INTO timetableslot (timetable_id, day, timeSlot, duration, lecturer_id, venue_id, subject_id, batch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($events as $ev) {
            // validate fields
            $day = $ev['day'] ?? 'MO';
            $timeSlot = intval($ev['timeSlot'] ?? 1);
            $duration = intval($ev['duration'] ?? 1);
            $lecturer = $ev['lecturerId'] ?? $ev['lecturer_id'] ?? null;
            $venue = $ev['venueId'] ?? $ev['venue_id'] ?? null;
            $subject = $ev['subjectId'] ?? $ev['subject_id'] ?? null;
            $batch = $ev['batchId'] ?? $batchId ?? null;

            $insertStmt->bind_param("ssiiisss", $timetable_id, $day, $timeSlot, $duration, $lecturer, $venue, $subject, $batch);
            $insertStmt->execute();
        }
    }
    $conn->commit();
    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
