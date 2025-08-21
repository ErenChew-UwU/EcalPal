<?php

set_time_limit(0);
$batches = $_POST['batch_ids'] ?? [];

if (!is_array($batches) || empty($batches)) {
    die("请选择至少一个批次");
}

// 转成 ["B0001","B0002"] 这种 JSON
$batchJson = json_encode(array_values($batches), JSON_UNESCAPED_UNICODE);

// Python 脚本路径
$pythonScript = __DIR__ . "/generate_timetable_python/generate_timetable.py";

// 用 proc_open 传 stdin 避免命令行转义问题
$descriptorspec = [
    0 => ["pipe", "r"],  // stdin
    1 => ["pipe", "w"],  // stdout
    2 => ["pipe", "w"]   // stderr
];

$process = proc_open("python " . escapeshellarg($pythonScript), $descriptorspec, $pipes);

if (is_resource($process)) {
    // 把 JSON 写入 Python stdin
    fwrite($pipes[0], $batchJson);
    fclose($pipes[0]);

    // 读取 Python stdout
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // 读取 Python stderr
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $return_value = proc_close($process);

    if ($return_value !== 0) {
        die("Python 执行出错: " . htmlspecialchars($error));
    }

    // 解析 Python 返回的 JSON
    $data = json_decode($output, true);
    if (!$data) {
        die("生成失败: " . htmlspecialchars($output));
    }

    // 存到 temp
    $tempDir = __DIR__ . "/temp";
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    file_put_contents($tempDir . "/timetable.json", json_encode($data, JSON_UNESCAPED_UNICODE));

    // 跳转到编辑页面
    header("Location: editTimetable.php?from=generate");
    exit;
}
?>
