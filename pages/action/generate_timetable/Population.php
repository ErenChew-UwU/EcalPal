<?php
require_once './Chromosome.php';

class Population {
    public $chromosomes = [];
    
    public function __construct($size, $batchId) {
        for ($i = 0; $i < $size; $i++) {
            $this->chromosomes[] = new Chromosome($batchId);
        }
    }
    
    public function evaluate() {
        foreach ($this->chromosomes as $chromosome) {
            $chromosome->calculateFitness();
        }
        
        usort($this->chromosomes, function($a, $b) {
            return $b->fitness <=> $a->fitness;
        });
    }
    
    public function getFittest() {
        $this->evaluate();
        return $this->chromosomes[0];
    }
}
?>