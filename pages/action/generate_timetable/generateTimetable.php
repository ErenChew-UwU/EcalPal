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
// 开启详细错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 记录请求开始
error_log("开始生成时间表 - " . date('Y-m-d H:i:s'));

require_once './GAconfig.php';
require_once './Population.php';
require_once './Chromosome.php';
require_once './TimetableGA.php';
require_once '../../../dbconnect.php'; // 根据实际路径调整

// 从请求获取批次ID
$batchId = $_GET['batch_id'] ?? 'B0001';

try {
    // 执行遗传算法
    $ga = new TimetableGA($batchId);
    $bestChromosome = $ga->run();
    
    // 准备返回数据
    $result = [
        'status' => 'success',
        'timetable' => [
            'batch_id' => $batchId,
            'start_date' => date('Y-m-d'),
            'duration_weeks' => 14,
            'slots' => []
        ]
    ];
    
    foreach ($bestChromosome->genes as $index => $gene) {
        $result['timetable']['slots'][] = [
            'id' => 'slot-' . time() . '-' . $index,
            'subject_id' => $gene['subject_id'],
            'lecturer_id' => $gene['lecturer_id'],
            'venue_id' => $gene['venue_id'],
            'day' => $gene['day'],
            'start_time' => $gene['start_time'],
            'duration' => $gene['duration']
        ];
    }
    
    // 记录成功
    error_log("成功生成时间表 - 批次: $batchId");
    
    // 返回结果
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    // 记录错误
    error_log("生成时间表错误: " . $e->getMessage());
    
    // 返回错误信息
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '生成失败: ' . $e->getMessage()
    ]);
}
?>