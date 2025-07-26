<?php
class GAConfig {
    // 遗传算法参数
    const POPULATION_SIZE = 100;
    const MAX_GENERATIONS = 1000;
    const CROSSOVER_RATE = 0.85;
    const MUTATION_RATE = 0.15;
    const ELITISM_COUNT = 2;
    
    // 时间表约束权重
    const HARD_CONSTRAINT_WEIGHT = 100;
    const TEACHER_CONFLICT_WEIGHT = 50;
    const VENUE_CONFLICT_WEIGHT = 40;
    const BATCH_CONFLICT_WEIGHT = 60;
    const AVAILABILITY_WEIGHT = 30;
    
    // 其他配置
    const MAX_RETRIES = 100; // 最大重试次数
}
?>