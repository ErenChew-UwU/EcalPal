from ga_config import MAX_GENERATIONS, ELITISM_COUNT, POPULATION_SIZE, TARGET_FITNESS_BASE, MAX_NO_IMPROVE, MINIMUN_TARGET_BASE, DEFAULT_SCORE_BASE
from fitness import calculate_fitness
from select_parent import select_parents
from mutation import mutate
from crossover import crossover
from population import create_population
from dbconnect import get_connection
from get_data import calculate_pair_count
import copy
import json
import sys



def run_ga(batch_ids, conn, generations=MAX_GENERATIONS):
    population = create_population(batch_ids, conn)
    progress = 0
    best_fitness = 0
    no_improve_count = 0
    best_individual = None
    PAIR_COUNT = calculate_pair_count(batch_ids,conn)
    score = PAIR_COUNT * DEFAULT_SCORE_BASE  
    for gen in range(generations):
        # 计算适应度
        population = sorted(population, key=lambda chrom: -calculate_fitness(batch_ids, chrom, conn))

        # 保留精英
        new_population = population[:ELITISM_COUNT]

        while len(new_population) < POPULATION_SIZE:
            p1, p2 = select_parents(batch_ids, population, conn)
            c1, c2 = crossover(p1, p2)
            if calculate_fitness(batch_ids, c1, conn) < PAIR_COUNT * MINIMUN_TARGET_BASE:
                c1 = mutate(c1, conn)

            if calculate_fitness(batch_ids, c2, conn) < PAIR_COUNT * MINIMUN_TARGET_BASE:
                c2 = mutate(c2, conn)
            new_population.extend([c1, c2])

        population = new_population[:POPULATION_SIZE]

        # 更新最优个体
        current_best = calculate_fitness(batch_ids, population[0], conn)
        if current_best > best_fitness:
            best_fitness = current_best
            best_individual = copy.deepcopy(population[0])
            no_improve_count = 0
        else:
            no_improve_count += 1

        # 目标适应度
        target = PAIR_COUNT * TARGET_FITNESS_BASE
        targetMin = PAIR_COUNT * MINIMUN_TARGET_BASE
        if best_fitness >= target:
            # if __name__ == "__main__":
            #     print(f"Early stop at generation {gen + 1}, fitness: {best_fitness} | {target}")
            break

        if no_improve_count >= MAX_NO_IMPROVE and best_fitness >= targetMin:
            # if __name__ == "__main__":
            #     print(f"Early stop at generation {gen + 1}, no improvement for {MAX_NO_IMPROVE} generations.{targetMin}")
            break
        
        if no_improve_count >= MAX_NO_IMPROVE * 2:
            # if __name__ == "__main__":
            #     print(f"Early stop at generation {gen + 1}, no improvement for {MAX_NO_IMPROVE * 2} generations.{targetMin}")
            break


        # if __name__ == "__main__":
        #     progress += 1
        #     progresing = int((progress / generations) * 100)
        #     print(f"Progress: {progresing}% | Generation {gen + 1}: Best fitness = {best_fitness}")

    return best_individual  # 返回适应度最高个体


# if __name__ == "__main__":
#     conn = get_connection()
#     batchs = ["B0001"]
#     result = run_ga(batchs, conn)
#     print(result)
#     fitness_result = calculate_fitness(batchs, result, conn)
#     print(fitness_result)

if __name__ == "__main__":
    # 从 stdin 获取 JSON
    batch_json = sys.stdin.read().strip()
    try:
        batch_list = json.loads(batch_json)
    except json.JSONDecodeError:
        print(json.dumps({"error": "Invalid JSON"}))
        sys.exit(1)

    if not isinstance(batch_list, list) or not batch_list:
        print(json.dumps({"error": "Invalid batch list"}))
        sys.exit(1)

    # 连接数据库
    conn = get_connection()

    # 调用你的 GA 算法
    result = run_ga(batch_list, conn)

    # 输出结果 JSON
    print(json.dumps(result, ensure_ascii=False))