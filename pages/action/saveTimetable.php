<?php
require_once __DIR__ . "/../../dbconnect.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

if (!$data) {
    $response['message'] = 'Invalid data';
    echo json_encode($response);
    exit;
}

try {
    $conn->begin_transaction();

    $source = $data['source'] ?? '';
    $changes = $data['changes'] ?? [];

    if (empty($changes)) {
        throw new Exception("No timetable changes provided.");
    }

    // 取第一个 change 用于判断 timetable 所属
    $firstChange = $changes[0];
    $timetableId = null;
    $batchTimetableMap = [];

    if ($source === 'generated') {
    // 为每个 unique batchId 获取或创建 timetable
    foreach ($changes as $change) {
            $batchId = $conn->real_escape_string($change['batchId']);
            if (!isset($batchTimetableMap[$batchId])) {
                $batchTimetableMap[$batchId] = getOrCreateTimetable($conn, $batchId);
            }
        }
    } else {
        // db 模式：直接取 timetable_id
        $timetableId = $conn->real_escape_string($firstChange['timetableId']);
        if (!$timetableId) {
            throw new Exception("Invalid timetable ID.");
        }
    }

    // 清空旧的 timetableslot 记录
    if ($source === 'generated') {
        foreach ($batchTimetableMap as $timetableId) {
            $deleteSql = "DELETE FROM timetableslot WHERE timetable_id = '$timetableId'";
            if (!$conn->query($deleteSql)) {
                throw new Exception("Failed to clear old timetable slots: " . $conn->error);
            }
        }
    } else {
        $deleteSql = "DELETE FROM timetableslot WHERE timetable_id = '$timetableId'";
        if (!$conn->query($deleteSql)) {
            throw new Exception("Failed to clear old timetable slots: " . $conn->error);
        }
    }


    // 插入新记录
    foreach ($changes as $change) {
        $day = $conn->real_escape_string($change['day']);
        $timeSlot = intval($change['timeSlot']);
        $duration = intval($change['duration']);
        $lecturerId = $conn->real_escape_string($change['lecturerId']);
        $venueId = $conn->real_escape_string($change['venueId']);
        $subjectId = $conn->real_escape_string($change['subjectId']);
        $batchId = $conn->real_escape_string($change['batchId']);

        // 获取正确的 timetableId
        $timetableIdForInsert = ($source === 'generated') ? $batchTimetableMap[$batchId] : $timetableId;

        $insertSql = "INSERT INTO timetableslot 
            (timetable_id, day, timeSlot, duration, lecturer_id, venue_id, subject_id, batch_id)
            VALUES 
            ('$timetableIdForInsert', '$day', $timeSlot, $duration, '$lecturerId', '$venueId', '$subjectId', '$batchId')";

        if (!$conn->query($insertSql)) {
            throw new Exception("Failed to insert slot: " . $conn->error);
        }
    }

    $now = date('Y-m-d H:i:s');

    if ($source === 'generated') {
        foreach ($batchTimetableMap as $timetableIdToUpdate) {
            $updateTimeSql = "UPDATE timetable SET lastModifyTime = '$now' WHERE ID = '$timetableIdToUpdate'";
            if (!$conn->query($updateTimeSql)) {
                throw new Exception("Failed to update lastModifyTime: " . $conn->error);
            }
        }
    } else {
        $updateTimeSql = "UPDATE timetable SET lastModifyTime = '$now' WHERE ID = '$timetableId'";
        if (!$conn->query($updateTimeSql)) {
            throw new Exception("Failed to update lastModifyTime: " . $conn->error);
        }
    }



    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Timetable saved successfully.';
} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);


// 获取或创建 timetable
function getOrCreateTimetable($conn, $batchId) {
    $sql = "SELECT ID FROM timetable 
            WHERE batch_id = '$batchId' AND Active = 1 
            LIMIT 1";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['ID'];
    }

    $timetableResult = $conn->query("SELECT ID FROM timetable ORDER BY ID DESC LIMIT 1");
    $lastTimetableId = $timetableResult->fetch_assoc()['ID'] ?? 'T0000';

    $numericPart = (int)substr($lastTimetableId, 1);
    $newNumericPart = str_pad($numericPart + 1, 4, '0', STR_PAD_LEFT);
    $newTimetableId = 'T' . $newNumericPart;


    $now = date('Y-m-d H:i:s');
    $startDate = date('Y-m-d');
    $insertSql = "INSERT INTO timetable 
        (ID, batch_id, start_date, duration_weeks, CreateTime, lastModifyTime, Active)
        VALUES 
        ('$newTimetableId', '$batchId', '$startDate', 14, '$now', '$now', 1)";
    
    if ($conn->query($insertSql)) {
        return $newTimetableId;
    }

    throw new Exception("Failed to create new timetable: " . $conn->error);
}
