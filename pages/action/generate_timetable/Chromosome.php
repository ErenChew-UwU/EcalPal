<?php
require_once './GAConfig.php';
require_once '../../../dbconnect.php'; // 使用您的dbconnect

class Chromosome {
    public $genes = []; // 基因数组 [batch_subject_id, subject_id, lecturer_id, venue_id, day, start_time, duration]
    public $fitness = 0;
    
    public function __construct($batchId) {
        global $conn; // 使用您的数据库连接
        
        // 获取批次的所有课程
        $sql = "
            SELECT bs.ID, bs.subject_id, lf.lessons_per_week, lf.timeslot_per_lesson 
            FROM batch_subject bs
            JOIN lesson_formats lf ON bs.ID = lf.batch_subject_id
            WHERE bs.batch_id = '$batchId' AND bs.status = 'current'
        ";
        
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            for ($i = 0; $i < $row['lessons_per_week']; $i++) {
                $this->genes[] = [
                    'batch_subject_id' => $row['ID'],
                    'subject_id' => $row['subject_id'],
                    'duration' => $row['timeslot_per_lesson'],
                    'lecturer_id' => null,
                    'venue_id' => null,
                    'day' => null,
                    'start_time' => null
                ];
            }
        }
        
        $this->initializeRandom($conn);
    }
    
    // 随机初始化基因
    private function initializeRandom($conn) {
        foreach ($this->genes as &$gene) {
            // 随机选择讲师
            $lecturers = $conn->query("
                SELECT lecturer_id 
                FROM lecturer_subject 
                WHERE subject_id = '{$gene['subject_id']}'
            ")->fetch_all(MYSQLI_ASSOC);
            
            if (!empty($lecturers)) {
                $gene['lecturer_id'] = $lecturers[array_rand($lecturers)]['lecturer_id'];
            }
            
            // 随机选择场地
            $venues = $conn->query("SELECT ID FROM venue")->fetch_all(MYSQLI_ASSOC);
            $gene['venue_id'] = $venues[array_rand($venues)]['ID'];
            
            // 随机选择日期和时间
            $days = ['MO', 'TU', 'WE', 'TH', 'FR'];
            $gene['day'] = $days[array_rand($days)];
            $gene['start_time'] = rand(1, 10); // 假设有10个时间段
        }
    }
    
    // 计算适应度
    public function calculateFitness() {
        require_once 'FitnessCalculator.php';
        $this->fitness = FitnessCalculator::calculate($this);
        return $this->fitness;
    }
}
?>