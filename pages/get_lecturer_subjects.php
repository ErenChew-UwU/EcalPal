<?php
// pages/action/get_lecturer_subjects.php
include_once("../dbconnect.php");

header('Content-Type: application/json');

// 获取讲师ID参数
$lecturerId = $_GET['lecturer_id'] ?? '';

if (empty($lecturerId)) {
    echo json_encode(['error' => 'Lecturer ID is required']);
    exit;
}

try {
    // 准备SQL查询获取讲师可教授的科目
    $sql = "SELECT s.ID, s.fullname, s.shortname, s.code 
            FROM lecturer_subject ls
            JOIN subject s ON ls.subject_id = s.ID
            WHERE ls.lecturer_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $lecturerId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $subjects = [];
    
    // 收集所有科目信息并格式化为组合字段
    while ($row = $result->fetch_assoc()) {
        // 创建组合字段：完整名称 + ID
        $displayText = "({$row['ID']}) {$row['fullname']}";
        
        $subjects[] = [
            'display' => $displayText,      // 组合字段用于显示
            'ID' => $row['ID'],             // 保留单独ID字段
            'fullname' => $row['fullname'], // 保留完整名称
            'shortname' => $row['shortname'],
            'code' => $row['code']
        ];
    }
    
    // 返回JSON格式的科目列表
    echo json_encode([
        'success' => true,
        'subjects' => $subjects
    ]);
    
} catch (Exception $e) {
    // 错误处理
    error_log("Lecturer Subjects Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}