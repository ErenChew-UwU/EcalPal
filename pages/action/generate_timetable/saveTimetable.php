<?php

// 确保只输出 JSON
header('Content-Type: application/json');

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 在脚本开始时清空所有输出缓冲区
while (ob_get_level()) {
    ob_end_clean();
}

// 防止任何 HTML 输出
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Internal server error: ' . $error['message']
        ]);
        exit;
    }
});
require_once '../../../dbconnect.php'; // 使用您的dbconnect

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['timetable'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$timetable = $data['timetable'];
global $conn;

// 开始事务
$conn->begin_transaction();

try {
    // 创建时间表记录
    $timetableId = 'T' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("
        INSERT INTO timetable (ID, batch_id, start_date, duration_weeks, CreateTime, lastModifyTime, Active)
        VALUES (?, ?, ?, ?, NOW(), NOW(), 1)
    ");
    $stmt->bind_param(
        "sssi",
        $timetableId,
        $timetable['batch_id'],
        $timetable['start_date'],
        $timetable['duration_weeks']
    );
    $stmt->execute();
    
    // 保存时间表槽位
    foreach ($timetable['slots'] as $slot) {
        $stmt = $conn->prepare("
            INSERT INTO timetableslot 
            (timetable_id, week, start_time, duration, lecturer_id, venue_id, subject_id, batch_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssiissss",
            $timetableId,
            $slot['day'],
            $slot['start_time'],
            $slot['duration'],
            $slot['lecturer_id'],
            $slot['venue_id'],
            $slot['subject_id'],
            $timetable['batch_id']
        );
        $stmt->execute();
    }
    
    $conn->commit();
    echo json_encode(['status' => 'success', 'timetable_id' => $timetableId]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>