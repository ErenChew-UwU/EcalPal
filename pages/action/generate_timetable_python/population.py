from chromosome import Chromosome
from ga_config import POPULATION_SIZE
from dbconnect import get_connection

def create_population(batch_ids, conn):
    population = []
    for _ in range(POPULATION_SIZE):
        genes = []
        for batch_id in batch_ids:
            chrom = Chromosome(batch_id, conn)
            genes.extend(chrom.genes)
        population.append(genes)
    return population


# testing 

if __name__ == "__main__" :
    conn = get_connection()
    batchs = {"B0001","B0002"}
    print(create_population(batchs, conn))