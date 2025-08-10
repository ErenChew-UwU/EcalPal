<?php
require_once __DIR__ . "/../../dbconnect.php";

// 统计总数
$total_batches = $conn->query("SELECT COUNT(*) AS cnt FROM batch")->fetch_assoc()['cnt'];

// 统计已有时间表的批次数
$total_with_timetable = $conn->query("
    SELECT COUNT(DISTINCT b.ID) AS cnt
    FROM batch b
    JOIN timetable t ON b.ID = t.batch_id
    WHERE t.Active = 1
")->fetch_assoc()['cnt'];

// 统计有 current 科目的批次数
$total_with_current_subject = $conn->query("
    SELECT COUNT(DISTINCT b.ID) AS cnt
    FROM batch b
    JOIN batch_subject bs ON b.ID = bs.batch_id
    WHERE bs.status = 'current'
")->fetch_assoc()['cnt'];

// 查询批次详细信息
$sql = "
    SELECT 
        b.ID, 
        b.course_name, 
        b.intake_name,
        CASE WHEN EXISTS (
            SELECT 1 
            FROM timetable t 
            WHERE t.batch_id = b.ID AND t.Active = 1
        ) THEN 1 ELSE 0 END AS has_timetable,
        CASE WHEN EXISTS (
            SELECT 1 
            FROM batch_subject bs 
            WHERE bs.batch_id = b.ID AND bs.status = 'current'
        ) THEN 1 ELSE 0 END AS has_current_subject
    FROM batch b
    ORDER BY b.course_name, b.intake_name
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>选择要生成的时间表</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../stylesheets/style_header.css">
<link rel="stylesheet" href="../../stylesheets/style_all.css">
<style>
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-icon.total { background: rgba(78, 115, 223, 0.15); color: var(--accent-color); }
    .stat-icon.full-time { background: rgba(56, 161, 105, 0.15); color: var(--success-color); }
    .stat-icon.part-time { background: rgba(236, 201, 75, 0.15); color: var(--warning-color); }

    .stat-info {
        flex: 1;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 16px;
        color: var(--text-light);
    }

    table {
        border-collapse: collapse;
        width: 100%;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    table th {
        background: var(--light-bg);
        padding: 10px;
        font-weight: 600;
    }
    table td {
        padding: 10px;
        border-top: 1px solid #e2e8f0;
        text-align: center;
    }
    .yes { color: green; font-weight: bold; }
    .no { color: red; font-weight: bold; }
    .btn-primary {
        background: var(--accent-color);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
    }
    .btn-primary:hover {
        background: #3b5fdb;
    }
</style>
</head>
<body>
<?php include("../page_all/header_index.php"); ?>

<div class="container">
    <h1>选择要生成的时间表</h1>

    <!-- Stats Section -->
    <div class="stats-container">
        <!-- Total Batches -->
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">
                    <?php echo $total_batches; ?>
                </div>
                <div class="stat-label">Total Batches</div>
            </div>
        </div>

        <!-- Batches with Timetable -->
        <div class="stat-card">
            <div class="stat-icon full-time">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">
                    <?php echo $total_with_timetable; ?>
                </div>
                <div class="stat-label">Batches with Timetable</div>
            </div>
        </div>

        <!-- Batches with Current Subject -->
        <div class="stat-card">
            <div class="stat-icon part-time">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">
                    <?php echo $total_with_current_subject; ?>
                </div>
                <div class="stat-label">Batches with Current Subject</div>
            </div>
        </div>
    </div>


    <!-- 表格 -->
    <form action="generateTimetable.php" method="post" onsubmit="showLoading()">
        <table>
            <thead>
                <tr>
                    <th>选择</th>
                    <th>课程名称</th>
                    <th>入学期</th>
                    <th>是否已有时间表</th>
                    <th>是否有 Current 科目</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td>
                        <?php if ($row['has_current_subject']) { ?>
                            <input type="checkbox" name="batch_ids[]" value="<?php echo $row['ID']; ?>">
                        <?php } else { ?>
                            <input type="checkbox" disabled>
                        <?php } ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['intake_name']); ?></td>
                    <td class="<?php echo $row['has_timetable'] ? 'yes' : 'no'; ?>">
                        <?php echo $row['has_timetable'] ? '是 (覆盖)' : '否'; ?>
                    </td>
                    <td class="<?php echo $row['has_current_subject'] ? 'yes' : 'no'; ?>">
                        <?php echo $row['has_current_subject'] ? '是' : '否'; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <br>
        <input type="submit" class="btn-primary" value="生成时间表">
    </form>

        <!-- 加载层 -->
    <div id="loading-overlay" style="
        display:none;
        position:fixed;
        top:0; left:0; width:100%; height:100%;
        background:rgba(255,255,255,0.8);
        z-index:9999;
        text-align:center;
        font-size:20px;
        color:#333;
    ">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);">
            <div class="spinner" style="
                border:8px solid #f3f3f3;
                border-top:8px solid var(--accent-color);
                border-radius:50%;
                width:60px; height:60px;
                animation:spin 1s linear infinite;
                margin:auto;
            "></div>
            <p style="margin-top:15px;">正在生成时间表，请稍候...</p>
        </div>
    </div>

    <style>
    @keyframes spin {
        0% { transform:rotate(0deg); }
        100% { transform:rotate(360deg); }
    }
    </style>

    <script>
    function showLoading() {
        document.getElementById('loading-overlay').style.display = 'block';
    }
    </script>
</div>
</body>
</html>
