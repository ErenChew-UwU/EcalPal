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
    
    foreach ($changes as $change) {
        $eventId = $conn->real_escape_string($change['eventId']);
        $batchId = $conn->real_escape_string($change['batchId']);
        $subjectId = $conn->real_escape_string($change['subjectId']);
        $venueId = $conn->real_escape_string($change['venueId']);
        $lecturerId = $conn->real_escape_string($change['lecturerId']);
        $day = $conn->real_escape_string($change['day']);
        $timeSlot = intval($change['timeSlot']);
        $duration = intval($change['duration']);
        
        if ($source === 'generated') {
            // 处理生成模式（新事件）
            // 1. 获取或创建时间表
            $timetableId = getOrCreateTimetable($conn, $batchId);
            
            // 2. 插入新记录
            $sql = "INSERT INTO timetableslot 
                    (timetable_id, day, timeSlot, duration, lecturer_id, venue_id, subject_id, batch_id)
                    VALUES 
                    ('$timetableId', '$day', $timeSlot, $duration, '$lecturerId', '$venueId', '$subjectId', '$batchId')";
            
            if (!$conn->query($sql)) {
                throw new Exception("Failed to insert slot: " . $conn->error);
            }
        } else {
            // 处理数据库模式（更新现有事件）
            // 移除可能的 "db-" 前缀
            $dbId = str_replace('db-', '', $eventId);
            
            $sql = "UPDATE timetableslot SET
                    day = '$day',
                    timeSlot = $timeSlot,
                    duration = $duration,
                    lecturer_id = '$lecturerId',
                    venue_id = '$venueId',
                    subject_id = '$subjectId',
                    batch_id = '$batchId'
                    WHERE id = $dbId";
            
            if (!$conn->query($sql)) {
                throw new Exception("Failed to update slot: " . $conn->error);
            }
        }
    }
    
    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Timetable saved successfully';
} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

// 辅助函数：获取或创建时间表
function getOrCreateTimetable($conn, $batchId) {
    $sql = "SELECT ID FROM timetable 
            WHERE batch_id = '$batchId' AND Active = 1 
            LIMIT 1";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['ID'];
    }
    
    // 创建新时间表
    $startDate = date('Y-m-d');
    $createTime = date('Y-m-d H:i:s');
    
    $insertSql = "INSERT INTO timetable 
                 (batch_id, start_date, duration_weeks, CreateTime, lastModifyTime, Active)
                 VALUES 
                 ('$batchId', '$startDate', 14, '$createTime', '$createTime', 1)";
    
    if ($conn->query($insertSql)) {
        return $conn->insert_id;
    }
    
    throw new Exception("Failed to create timetable: " . $conn->error);
}