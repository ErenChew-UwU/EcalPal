import random
from fitness import calculate_fitness
from ga_config import TOURNAMENT_SIZE

def select_parents(population, conn):
    selected = []
    for _ in range(2):
        tournament = random.sample(population, TOURNAMENT_SIZE)
        best = max(tournament, key=lambda x: calculate_fitness(x, conn))  # 使用 lambda 加 conn
        selected.append(best)
    return selected[0], selected[1]
