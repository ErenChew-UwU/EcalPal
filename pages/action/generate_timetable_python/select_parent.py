import random
from fitness import calculate_fitness
from ga_config import TOURNAMENT_SIZE

def select_parents(batch_ids, population, conn):
    selected = []
    for _ in range(2):
        tournament = random.sample(population, TOURNAMENT_SIZE)
        best = max(tournament, key=lambda x: calculate_fitness(batch_ids, x, conn))  # 使用 lambda 加 conn
        selected.append(best)
    return selected[0], selected[1]


# def select_parents(batch_ids, population, conn):
#     fitnesses = [calculate_fitness(batch_ids, ind, conn) for ind in population]
#     total_fitness = sum(fitnesses)
#     probabilities = [f / total_fitness for f in fitnesses]
#     parents = random.choices(population, weights=probabilities, k=2)
#     return parents[0], parents[1]