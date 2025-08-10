<?php
$batches = $_POST['batch_ids'] ?? []; // 从 createTimetable.php 获取
if (empty($batches)) {
    die("请选择至少一个 Batch");
}

$batchJson = json_encode($batches, JSON_UNESCAPED_UNICODE);
$pythonScript = __DIR__ . "/generate_timetable_python/generate_timetable.py";

// 调用 Python 并传参数
$cmd = "python " . escapeshellarg($pythonScript) . " " . escapeshellarg($batchJson);
$output = shell_exec($cmd);

// 解析结果
$data = json_decode($output, true);
if (!$data) {
    die("生成失败: " . htmlspecialchars($output));
}

$tempDir = __DIR__ . "/temp";
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}
file_put_contents($tempDir . "/timetable.json", json_encode($data, JSON_UNESCAPED_UNICODE));


// 存临时文件

// 跳转到编辑页面
header("Location: editTimetable.php");
exit;
?>
