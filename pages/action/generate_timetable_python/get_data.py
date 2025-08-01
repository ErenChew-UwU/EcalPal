def fetch_batch(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM batch")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_batch_subject(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM batch_subject")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_image(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM image")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_lecturer(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lecturer")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_lecturer_availability(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lecturer_availability")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_lecturer_subject(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lecturer_subject")
    data = cursor.fetchall()
    cursor.close()
    return data

def fetch_batch_subjects_by_batch(batch_id,conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM batch_subject WHERE batch_id = %s AND status = 'current'", (batch_id,))
    batch_subjects = cursor.fetchall()
    cursor.close()
    return batch_subjects


def fetch_lesson_formats_by_batch_subject(batch_subject_ids,conn):
    if not batch_subject_ids:
        return []
    placeholders = ', '.join(['%s'] * len(batch_subject_ids))
    query = f"SELECT * FROM lesson_formats WHERE batch_subject_id IN ({placeholders})"

    cursor = conn.cursor(dictionary=True)
    cursor.execute(query, batch_subject_ids)
    formats = cursor.fetchall()
    cursor.close()
    return formats


def fetch_lesson_formats(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lesson_formats")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_student(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM student")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_subject(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM subject")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_timetable(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM timetable")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_timetableslot(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM timetableslot")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_venue(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM venue")
    data = cursor.fetchall()
    cursor.close()
    return data


def fetch_venue_availability(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM venue_availability")
    data = cursor.fetchall()
    cursor.close()
    return data

def fetch_active_timetables(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM timetable WHERE status = 'active'")
    data = cursor.fetchall()
    cursor.close()
    return data

def fetch_venue_availability_by_venue(venue_id,conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM venue_availability WHERE venue_id = %s", (venue_id,))
    data = cursor.fetchall()
    cursor.close()
    return data

def fetch_timetableslot_by_timetable(timetable_id,conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM timetableslot WHERE timetable_id = %s", (timetable_id,))
    data = cursor.fetchall()
    cursor.close()
    return data

def fetch_lecturer_availability_by_lecturer(lecturer_id,conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lecturer_availability WHERE lecturer_id = %s", (lecturer_id,))
    data = cursor.fetchall()
    cursor.close()
    return data

def fetch_student_by_batch(batch_id,conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM student WHERE batch_id = %s", (batch_id,))
    data = cursor.fetchall()
    cursor.close()
    return data

def fetch_batch_by_id(batch_id,conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM batch WHERE batch_id = %s", (batch_id,))
    data = cursor.fetchone()
    cursor.close()
    return data

def fetch_lecturer_ids_for_subject(subject_id, conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT lecturer_id FROM lecturer_subject WHERE subject_id = %s", (subject_id,))
    results = cursor.fetchall()
    cursor.close()
    return [row['lecturer_id'] for row in results]

def fetch_all_venue_ids(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT ID FROM venue")
    results = cursor.fetchall()
    cursor.close()
    return [row['ID'] for row in results]

def calculate_pair_count(conn):
    cursor = conn.cursor()
    cursor.execute("SELECT lesson_per_week FROM batch_subject WHERE status = 'current'")
    lessons = cursor.fetchall()
    cursor.close()

    total_lessons = sum(row[0] for row in lessons)  # 每条 lesson_per_week 加总
    return total_lessons * (total_lessons - 1) // 2  # 计算所有 pair 的组合数


# {
#   "status": "success",
#   "timetable": [
#       {
#         "timetableId": "T0001",
#         "batchId": "B0001",
#         "slots": [
#             {
#                 "UUID": "{randomGenerate}",
#                 "subjectId": "SB0001",
#                 "day": "MO",
#                 "timeSlot": 1, # 用bitmap来表示在第几个（第一格是1，第二个2，第三格4）
#                 "duration": 2, # 1是半个小时,2是1小时
#                 "venueId": "V0001",
#                 "lecturerId": "L0001"
#             },
#             {
#                 "UUID": "{randomGenerate}", # 可能存在两个一样的subject，根据他的lesson可能会在一个星期里有两堂课在不同时间，但尽量是同个老师教
#                 "subjectId": "SB0001",
#                 "day": "MO",
#                 "timeSlot": 4, # 用bitmap来表示在第几个（第一格是1，第二个2，第三格4）
#                 "duration": 2, # 1是半个小时,2是1小时
#                 "venueId": "V0001",
#                 "lecturerId": "L0001"
#             },
#             {...}
#         ],
#         "unplaced": [
#             {
#                 "UUID": "{randomGenerate}", # 可能存在两个一样的subject，根据他的lesson可能会在一个星期里有两堂课在不同时间，但尽量是同个老师教
#                 "subjectId": "SB0001",
#                 "duration": 2, # 1是半个小时,2是1小时
#                 "preferredLecturerIds": ["L0002", "L0005"],
#                 "reason": "No available venue at desired time"
#             }
#         ]
#     },
#     { # 可能同时生成多个时间表
#         "timetableId": "T0001",
#         "batchId": "B0001",
#         "slots": [
#             {
#                 "UUID": "{randomGenerate}",
#                 "subjectId": "SB0001",
#                 "day": "MO",
#                 "timeSlot": 1, # 用bitmap来表示在第几个（第一格是1，第二个2，第三格4）
#                 "duration": 2, # 1是半个小时,2是1小时
#                 "venueId": "V0001",
#                 "lecturerId": "L0001"
#             },
#             {
#                 "UUID": "{randomGenerate}", # 可能存在两个一样的subject，根据他的lesson可能会在一个星期里有两堂课在不同时间，但尽量是同个老师教
#                 "subjectId": "SB0001",
#                 "day": "MO",
#                 "timeSlot": 4, # 用bitmap来表示在第几个（第一格是1，第二个2，第三格4）
#                 "duration": 2, # 1是半个小时,2是1小时
#                 "venueId": "V0001",
#                 "lecturerId": "L0001"
#             },
#             {...}
#         ],
#         "unplaced": [
#             {
#                 "UUID": "{randomGenerate}", # 可能存在两个一样的subject，根据他的lesson可能会在一个星期里有两堂课在不同时间，但尽量是同个老师教
#                 "subjectId": "SB0001",
#                 "duration": 2, # 1是半个小时,2是1小时
#                 "preferredLecturerIds": ["L0002", "L0005"],
#                 "reason": "No available venue at desired time"
#             }
#         ]
#     }
#   ]
# }
