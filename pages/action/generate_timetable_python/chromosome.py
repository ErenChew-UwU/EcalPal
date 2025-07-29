import random
from ga_config import DAYS, TIME_SLOTS

class Chromosome:
    def __init__(self, lessons):
        self.lessons = lessons  # 每个 lesson 是 dict
        self.genes = self._create_genes()
        self.fitness = None

    def _create_genes(self):
        genes = []
        for lesson in self.lessons:
            gene = lesson.copy()
            gene['day'] = random.choice(DAYS)
            gene['start_time'] = random.choice(TIME_SLOTS)
            gene['venue_id'] = f"V000{random.randint(1, 3)}"
            gene['lecturer_id'] = f"L000{random.randint(1, 3)}"
            genes.append(gene)
        return genes
