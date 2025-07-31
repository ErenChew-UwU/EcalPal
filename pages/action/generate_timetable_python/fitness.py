from ga_config import (
    LECTURER_CONFLICT_WEIGHT, 
    VENUE_CONFLICT_WEIGHT, 
    BATCH_CONFLICT_WEIGHT, 
    DEFAULT_SCORE
    )

def calculate_fitness(genes):
    score = DEFAULT_SCORE  # 初始分
    penalty = 0

    for gene1 in genes:
        for gene2 in genes:
            if gene1 == gene2:
                continue
            # 冲突示例：时间、地点、教师重复
            if (gene1['day'] == gene2['day'] and
                gene1['timeSlot'] == gene2['timeSlot']):
                
                if gene1['venueId'] == gene2['venueId']:
                    penalty += VENUE_CONFLICT_WEIGHT
                if gene1['lecturerId'] == gene2['lecturerId']:
                    penalty += LECTURER_CONFLICT_WEIGHT
                if gene1.get('batchId') == gene2.get('batchId'):
                    penalty += BATCH_CONFLICT_WEIGHT

    final_score = max(0, score - penalty)
    return final_score
