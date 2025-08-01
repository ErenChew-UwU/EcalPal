import random
from ga_config import TIME_SLOTS, DAYS, MUTATION_RATE
from get_data import fetch_lecturer_ids_for_subject, fetch_all_venue_ids

def mutate(chromosome, conn):
    all_venues = fetch_all_venue_ids(conn)  # 例如 ['V0001', 'V0002', ...]
    
    for gene in chromosome:
        if random.random() < MUTATION_RATE:
            gene['day'] = random.choice(DAYS)
            gene['timeSlot'] = random.choice(TIME_SLOTS)

            # 随机变更教师（若该科目有多个教师）
            lecturers = fetch_lecturer_ids_for_subject(gene['subjectId'], conn)
            if lecturers:
                gene['lecturerId'] = random.choice(lecturers)

            # 随机变更课室
            gene['venueId'] = random.choice(all_venues)

    return chromosome
