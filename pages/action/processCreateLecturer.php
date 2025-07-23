<?php
include_once("../dbconnect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $lecturerId = $_POST['lecturerId'];
    $name = $_POST['lecturerName'];
    $position = $_POST['position'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $selectedSlots = json_decode($_POST['selectedSlots'], true);
    
    // 图片处理
    $imagePath = '';
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/lecturers/";
        $fileName = basename($_FILES["profileImage"]["name"]);
        $targetFile = $targetDir . $lecturerId . '_' . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // 验证图片类型
        $validExtensions = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $validExtensions)) {
            if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $targetFile)) {
                $imagePath = $targetFile;
            }
        }
    }
    
    // 数据库操作
    try {
        $conn->begin_transaction();
        
        // 1. 添加/更新讲师记录
        if (isset($_POST['editMode'])) {
            // 更新逻辑
            $sql = "UPDATE Lecturer SET name=?, position=?, email=?, phone=?, department=?" . 
                   ($imagePath ? ", imagePath=?" : "") . 
                   " WHERE ID=?";
            
            $stmt = $conn->prepare($sql);
            if ($imagePath) {
                $stmt->bind_param("sssssss", $name, $position, $email, $phone, $department, $imagePath, $lecturerId);
            } else {
                $stmt->bind_param("ssssss", $name, $position, $email, $phone, $department, $lecturerId);
            }
        } else {
            // 添加新讲师
            $sql = "INSERT INTO Lecturer (ID, name, position, email, phone, department, imagePath) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $lecturerId, $name, $position, $email, $phone, $department, $imagePath);
        }
        $stmt->execute();
        
        // 2. 处理可用时间
        $deleteSql = "DELETE FROM Lecturer_Availability WHERE lecturer_id=?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("s", $lecturerId);
        $deleteStmt->execute();
        
        if (!empty($selectedSlots)) {
            $insertSql = "INSERT INTO Lecturer_Availability (lecturer_id, day, time_slot) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            
            foreach ($selectedSlots as $slot) {
                $insertStmt->bind_param("sss", $lecturerId, $slot['day'], $slot['time']);
                $insertStmt->execute();
            }
        }
        
        $conn->commit();
        header("Location: dashboardLecturer.php?success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: dashboardLecturer.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>