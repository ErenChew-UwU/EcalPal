<?php
$batchs = ["B0001", "B0002"];
$batchsJson = json_encode($batchs);

// __DIR__ 会返回当前 PHP 文件所在目录
$pythonScript = __DIR__ . "/action/generate_timetable_python/generate_timetable.py";

// 用相对路径调用 Python
$cmd = "python " . escapeshellarg($pythonScript) . " " . escapeshellarg($batchsJson);
$output = shell_exec($cmd);

$data = json_decode($output, true);

if (is_array($data)) {
    foreach ($data as $gene) {
        echo $gene['batchId'] . " - " . $gene['day'] . "<br>";
    }
} else {
    echo "Error: " . htmlspecialchars($output);
}
?>
