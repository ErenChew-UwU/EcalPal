<?php
// pages/action/get_availability.php
include_once("../dbconnect.php");

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

if (empty($type) || empty($id)) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $table = ($type === 'lecturer') ? 'Lecturer_Availability' : 'Venue_Availability';
    $idField = ($type === 'lecturer') ? 'lecturer_id' : 'venue_id';
    
    $sql = "SELECT day, availability_bitmap 
            FROM $table 
            WHERE $idField = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $availability = [];
    while ($row = $result->fetch_assoc()) {
        // 确保键名是大写，与前端匹配
        $availability[strtoupper($row['day'])] = (int)$row['availability_bitmap'];
    }
    
    // 确保返回所有7天的数据（即使没有记录）
    $allDays = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];
    $completeAvailability = [];
    foreach ($allDays as $day) {
        $completeAvailability[$day] = $availability[$day] ?? 0;
    }
    
    echo json_encode(['availability' => $completeAvailability]);
} catch (Exception $e) {
    error_log("Availability Error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}