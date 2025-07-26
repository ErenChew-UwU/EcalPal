<?php
require_once './GAconfig.php';
require_once '../../../dbconnect.php'; // 使用您的dbconnect

class FitnessCalculator {
    public static function calculate($chromosome) {
        $fitness = 1000; // 初始适应度
        global $conn; // 使用您的数据库连接
        
        // 1. 检查教师冲突
        $teacherConflicts = self::calculateTeacherConflicts($chromosome);
        $fitness -= $teacherConflicts * GAconfig::TEACHER_CONFLICT_WEIGHT;
        
        // 2. 检查场地冲突
        $venueConflicts = self::calculateVenueConflicts($chromosome);
        $fitness -= $venueConflicts * GAconfig::VENUE_CONFLICT_WEIGHT;
        
        // 3. 检查班级冲突
        $batchConflicts = self::calculateBatchConflicts($chromosome);
        $fitness -= $batchConflicts * GAconfig::BATCH_CONFLICT_WEIGHT;
        
        // 4. 检查可用性
        $availabilityIssues = self::calculateAvailabilityIssues($chromosome, $conn);
        $fitness -= $availabilityIssues * GAconfig::AVAILABILITY_WEIGHT;
        
        // 确保适应度不为负
        return max(0, $fitness);
    }
    
    private static function calculateTeacherConflicts($chromosome) {
        $schedule = [];
        $conflicts = 0;
        
        foreach ($chromosome->genes as $gene) {
            $key = $gene['lecturer_id'] . '_' . $gene['day'] . '_' . $gene['start_time'];
            
            if (isset($schedule[$key])) {
                $conflicts++;
            } else {
                $schedule[$key] = true;
                
                // 检查时间段重叠
                for ($i = 1; $i < $gene['duration']; $i++) {
                    $nextKey = $gene['lecturer_id'] . '_' . $gene['day'] . '_' . ($gene['start_time'] + $i);
                    if (isset($schedule[$nextKey])) $conflicts++;
                    $schedule[$nextKey] = true;
                }
            }
        }
        
        return $conflicts;
    }
    
    private static function calculateVenueConflicts($chromosome) {
        // 类似教师冲突计算，使用venue_id
    }
    
    private static function calculateBatchConflicts($chromosome) {
        // 类似教师冲突计算，使用batch_id
    }
    
    private static function calculateAvailabilityIssues($chromosome, $db) {
        $issues = 0;
        
        foreach ($chromosome->genes as $gene) {
            // 检查教师可用性
            $stmt = $db->prepare("
                SELECT availability_bitmap 
                FROM lecturer_availability 
                WHERE lecturer_id = ? AND day = ?
            ");
            $stmt->bind_param("ss", $gene['lecturer_id'], $gene['day']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $bitmap = $row['availability_bitmap'];
                $bit = ($bitmap >> ($gene['start_time'] - 1)) & 1;
                if ($bit === 0) $issues++;
            }
            
            // 检查场地可用性（类似逻辑）
        }
        
        return $issues;
    }
}
?>