import random
import uuid
from ga_config import DAYS, TIME_SLOTS
from get_data import (
    fetch_venue,
    fetch_lecturer,
    fetch_lecturer_subject,
    fetch_batch_subject,
)
from fitness import calculate_fitness
from dbconnect import get_connection

class Chromosome:
    def __init__(self, batch_id, conn):
        self.conn = conn
        self.batch_id = batch_id
        self.venues = fetch_venue(conn)
        self.lecturers = fetch_lecturer(conn)
        self.lecturer_subjects = fetch_lecturer_subject(conn)
        self.batch_subjects = self._get_current_subjects()
        self.genes = self._create_genes()
        self.fitness = None

    def _get_current_subjects(self):
        all_subjects = fetch_batch_subject(self.conn)
        return [
            subj for subj in all_subjects
            if subj['batch_id'] == self.batch_id and subj['status'] == 'current'
        ]

    def _find_qualified_lecturers(self, subject_id):
        return [
            ls['lecturer_id']
            for ls in self.lecturer_subjects
            if ls['subject_id'] == subject_id
        ]

    def _create_genes(self):
        genes = []
        for subj in self.batch_subjects:
            for _ in range(subj['lesson_per_week']):
                gene = {
                    'UUID': str(uuid.uuid4()),
                    'batchId': self.batch_id,
                    'subjectId': subj['subject_id'],
                    'day': random.choice(DAYS),
                    'timeSlot': random.choice(TIME_SLOTS),
                    'duration': subj['timeslot_per_lesson'],
                    'venueId': random.choice(self.venues)['ID'] if self.venues else "V0001"
                }

                qualified = self._find_qualified_lecturers(subj['subject_id'])
                if qualified:
                    gene['lecturerId'] = random.choice(qualified)
                else:
                    gene['lecturerId'] = random.choice(self.lecturers)['ID'] if self.lecturers else "L0001"

                genes.append(gene)
        return genes

# Testing 
if __name__ == "__main__" :
    l = 900
    m = 0
    conn = get_connection()
    total = 100
    for i in range(total):

        genes = []
        chrom = Chromosome("B0001", conn)
        genes.extend(chrom.genes)
        if calculate_fitness(genes, conn) < l :
            l = calculate_fitness(genes, conn)
        if calculate_fitness(genes, conn) > m :
            m = calculate_fitness(genes, conn)
        if i % 100 == 0:
            progress = int((i / total) * 100)
            print(f"Progress: {progress}%")
    print(l , " , " , m)
