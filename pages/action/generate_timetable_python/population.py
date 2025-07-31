from chromosome import Chromosome
from ga_config import POPULATION_SIZE

def create_population(batch_ids):
    population = []
    for _ in range(POPULATION_SIZE):
        genes = []
        for batch_id in batch_ids:
            chrom = Chromosome(batch_id)
            genes.extend(chrom.genes)
        population.append(genes)
    return population


# testing 

if __name__ == "__main__" :
    batchs = {"B0001","B0002"}
    print(create_population(batchs))