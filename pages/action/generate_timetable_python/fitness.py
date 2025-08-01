from get_data import fetch_lecturer_availability, fetch_venue_availability, calculate_pair_count
from ga_config import (
    LECTURER_CONFLICT_WEIGHT, 
    VENUE_CONFLICT_WEIGHT, 
    BATCH_CONFLICT_WEIGHT, 
    DEFAULT_SCORE_BASE,
    AVAILABILITY_WEIGHT,
    SOFT_CONSTRAINTS_TINY,
    SOFT_CONSTRAINTS_SMALL,
    SOFT_CONSTRAINTS_MEDIUM,
    SOFT_CONSTRAINTS_LARGE
    )

def is_available(bitmap, slot):
    return (bitmap & (1 << (slot - 1))) != 0

def calculate_fitness(genes, conn):
    pair_count = calculate_pair_count(conn)
    score = pair_count * DEFAULT_SCORE_BASE         # Default Score
    penalty = 0
    lecturer_avail = fetch_lecturer_availability(conn)  # list of {lecturer_id, day, timeslot}
    venue_avail = fetch_venue_availability(conn)        # list of {venue_id, day, timeslot}

    for gene1 in genes:
        for gene2 in genes:
            if gene1 == gene2:
                continue
            # Hard Constraints: Day, Time, Venue, Lecturer, Batch, Avalidable Time of Venue and Lecturer
            if (gene1['day'] == gene2['day'] and
                gene1['timeSlot'] == gene2['timeSlot']):
                
                if gene1['venueId'] == gene2['venueId']:
                    penalty += VENUE_CONFLICT_WEIGHT
                if gene1['lecturerId'] == gene2['lecturerId']:
                    penalty += LECTURER_CONFLICT_WEIGHT
                if gene1['batchId'] == gene2['batchId']:
                    penalty += BATCH_CONFLICT_WEIGHT

        if not any(
            a['lecturer_id'] == gene1['lecturerId'] and 
            a['day'] == gene1['day'] and 
            is_available(a['availability_bitmap'], gene1['timeSlot'])
            for a in lecturer_avail
        ):
            penalty += AVAILABILITY_WEIGHT
        if not any(
            v['venue_id'] == gene1['venueId'] and 
            v['day'] == gene1['day'] and 
            is_available(v['availability_bitmap'], gene1['timeSlot'])
            for v in venue_avail
        ):
            penalty += AVAILABILITY_WEIGHT

        # Soft Constraints: 
        # 软约束：中午1点到2点（slot 9, 10）尽量不排课
        if gene1['timeSlot'] == 9 or gene1['timeSlot'] == 10:
            penalty += SOFT_CONSTRAINTS_SMALL

        # 软约束：星期三下午1点后尽量不排（slot >= 9）
        if gene1['day'] == 'WE' and gene1['timeSlot'] >= 9:
            penalty += SOFT_CONSTRAINTS_MEDIUM

        # 软约束：星期五12点后（slot >= 8）避免排课
        if gene1['day'] == 'FR' and gene1['timeSlot'] >= 7:
            penalty += SOFT_CONSTRAINTS_MEDIUM

        # 软约束：同科不同讲师
        for gene2 in genes:
            if gene1 == gene2:
                continue
            if gene1['subjectId'] == gene2['subjectId'] and gene1['lecturerId'] != gene2['lecturerId']:
                penalty += SOFT_CONSTRAINTS_LARGE

        # 软约束：避免课程时间太分散（尽量连续）
        for gene2 in genes:
            if gene1 == gene2 or gene1['batchId'] != gene2['batchId'] or gene1['day'] != gene2['day']:
                continue
            if abs(gene1['timeSlot'] - gene2['timeSlot']) > 0:
                penalty += SOFT_CONSTRAINTS_TINY

    final_score = max(0, score - penalty)
    return final_score