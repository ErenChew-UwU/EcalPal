import random
from ga_config import CROSSOVER_RATE

def crossover(parent1, parent2):
    if random.random() < CROSSOVER_RATE:
        point = random.randint(1, len(parent1) - 1)
        child1 = parent1[:point] + parent2[point:]
        child2 = parent2[:point] + parent1[point:]
    else:
        # 不交叉，直接复制 parent
        child1 = parent1[:]
        child2 = parent2[:]
    return child1, child2
