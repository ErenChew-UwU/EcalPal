import random
import uuid
from ga_config import DAYS, TIME_SLOTS
from get_data import (
    fetch_venue,
    fetch_lecturer,
    fetch_lecturer_subject,
    fetch_batch_subject,
)

class Chromosome:
    def __init__(self, batch_id):
        self.batch_id = batch_id
        self.venues = fetch_venue()
        self.lecturers = fetch_lecturer()
        self.lecturer_subjects = fetch_lecturer_subject()
        self.batch_subjects = self._get_current_subjects()
        self.genes = self._create_genes()
        self.fitness = None

    def _get_current_subjects(self):
        all_subjects = fetch_batch_subject()
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

    genes = []
    chrom = Chromosome("B0001")
    genes.extend(chrom.genes)
    print(genes)
