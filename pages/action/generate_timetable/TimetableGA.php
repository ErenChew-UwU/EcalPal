<?php
require_once './GAConfig.php';
require_once './Population.php';
require_once './Chromosome.php';
require_once '../../../../dbconnect.php'; // 包含您的数据库连接

class TimetableGA {
    private $batchId;
    private $conn; // 数据库连接对象
    
    public function __construct($batchId) {
        global $conn; // 使用您的全局数据库连接
        $this->batchId = $batchId;
        $this->conn = $conn;
    }
    
    public function run() {
        $population = new Population(GAConfig::POPULATION_SIZE, $this->batchId);
        $population->evaluate();
        
        $generationCount = 0;
        
        while ($generationCount < GAConfig::MAX_GENERATIONS) {
            $newPopulation = [];
            
            // 精英主义：保留最优个体
            for ($i = 0; $i < GAConfig::ELITISM_COUNT; $i++) {
                $newPopulation[] = clone $population->chromosomes[$i];
            }
            
            // 生成新一代
            while (count($newPopulation) < GAConfig::POPULATION_SIZE) {
                $parent1 = $this->tournamentSelection($population);
                $parent2 = $this->tournamentSelection($population);
                
                if (mt_rand() / mt_getrandmax() < GAConfig::CROSSOVER_RATE) {
                    list($child1, $child2) = $this->crossover($parent1, $parent2);
                    $newPopulation[] = $child1;
                    $newPopulation[] = $child2;
                } else {
                    $newPopulation[] = clone $parent1;
                    $newPopulation[] = clone $parent2;
                }
            }
            
            // 变异
            foreach ($newPopulation as $chromosome) {
                if (mt_rand() / mt_getrandmax() < GAConfig::MUTATION_RATE) {
                    $this->mutate($chromosome);
                }
            }
            
            $population->chromosomes = $newPopulation;
            $population->evaluate();

            if ($generationCount % 100 === 0) {
                $bestFitness = $population->getFittest()->fitness;
                error_log("代 {$generationCount} - 最佳适应度: {$bestFitness}");
            }
            
            $generationCount++;
        }

        $finalFitness = $population->getFittest()->fitness;
        error_log("完成! 最终代 {$generationCount} - 最佳适应度: {$finalFitness}");
        
        return $population->getFittest();
    }
    
    private function tournamentSelection($population) {
        $tournamentSize = 5;
        $tournament = [];
        
        for ($i = 0; $i < $tournamentSize; $i++) {
            $randomIndex = mt_rand(0, count($population->chromosomes) - 1);
            $tournament[] = $population->chromosomes[$randomIndex];
        }
        
        usort($tournament, function($a, $b) {
            return $b->fitness <=> $a->fitness;
        });
        
        return $tournament[0];
    }
    
    private function crossover($parent1, $parent2) {
        $child1 = clone $parent1;
        $child2 = clone $parent2;
        
        $crossoverPoint = mt_rand(1, count($parent1->genes) - 2);
        
        for ($i = $crossoverPoint; $i < count($parent1->genes); $i++) {
            $child1->genes[$i] = $parent2->genes[$i];
            $child2->genes[$i] = $parent1->genes[$i];
        }
        
        return [$child1, $child2];
    }
    
    private function mutate($chromosome) {
        $mutatedGeneIndex = mt_rand(0, count($chromosome->genes) - 1);
        $gene = &$chromosome->genes[$mutatedGeneIndex];
        
        $mutationType = mt_rand(1, 3);
        
        switch ($mutationType) {
            case 1: // 改变时间
                $gene['start_time'] = mt_rand(1, 10);
                break;
            case 2: // 改变日期
                $days = ['MO', 'TU', 'WE', 'TH', 'FR'];
                $gene['day'] = $days[array_rand($days)];
                break;
            case 3: // 改变场地
                // 使用类中的数据库连接
                $venues = $this->conn->query("SELECT ID FROM venue")->fetch_all(MYSQLI_ASSOC);
                $gene['venue_id'] = $venues[array_rand($venues)]['ID'];
                break;
        }
    }
}
?>