from ga_config import MAX_GENERATIONS, ELITISM_COUNT, POPULATION_SIZE, TARGET_FITNESS_BASE, MAX_NO_IMPROVE
from fitness import calculate_fitness
from select_parent import select_parents
from mutation import mutate
from crossover import crossover
from population import create_population
from dbconnect import get_connection
from get_data import calculate_pair_count



def run_ga(batch_ids, conn, generations=MAX_GENERATIONS):
    population = create_population(batch_ids, conn)
    progress = 0
    best_fitness = 0
    no_improve_count = 0
    for gen in range(generations):
        # 计算适应度
        population = sorted(population, key=lambda chrom: -calculate_fitness(chrom, conn))

        # 保留精英
        new_population = population[:ELITISM_COUNT]

        while len(new_population) < POPULATION_SIZE:
            p1, p2 = select_parents(population, conn)
            c1, c2 = crossover(p1, p2)
            c1 = mutate(c1, conn)
            c2 = mutate(c2, conn)
            new_population.extend([c1, c2])

        population = new_population[:POPULATION_SIZE]
        current_best = max([calculate_fitness(ind, conn) for ind in population])

        # 如果当前更好，就重置无提升计数器
        if current_best > best_fitness:
            best_fitness = current_best
            no_improve_count = 0
        else:
            no_improve_count += 1

        target = calculate_pair_count(conn) * TARGET_FITNESS_BASE

        if best_fitness >= target:
            if __name__ == "__main__":
                print(f"Early stop at generation {gen + 1}, fitness: {best_fitness} | {target}")
            break

        if no_improve_count >= MAX_NO_IMPROVE:
            if __name__ == "__main__":
                print(f"Early stop at generation {gen + 1}, no improvement for {MAX_NO_IMPROVE} generations.")
            break

        if __name__ == "__main__":
            progress += 1
            if progress % (MAX_GENERATIONS / 100) == 0:
                progresing = int((progress / generations) * 100)
                print(f"Progress: {progresing}% | Generation {gen + 1}: Best fitness = {best_fitness}")

    return population[0]  # 返回适应度最高个体


if __name__ == "__main__":
    conn = get_connection()
    batchs = {"B0001","B0002"}
    result = run_ga(batchs, conn)
    print(result)
