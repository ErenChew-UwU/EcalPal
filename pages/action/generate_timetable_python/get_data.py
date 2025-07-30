from dbconnect import get_connection


def fetch_batch():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM batch")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_batch_subject():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM batch_subject")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_image():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM image")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_lecturer():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lecturer")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_lecturer_availability():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lecturer_availability")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_lecturer_subject():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lecturer_subject")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def fetch_batch_subjects_by_batch(batch_id):
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM batch_subject WHERE batch_id = %s AND status = 'current'", (batch_id,))
    batch_subjects = cursor.fetchall()
    cursor.close()
    conn.close()
    return batch_subjects


def fetch_lesson_formats_by_batch_subject(batch_subject_ids):
    if not batch_subject_ids:
        return []
    placeholders = ', '.join(['%s'] * len(batch_subject_ids))
    query = f"SELECT * FROM lesson_formats WHERE batch_subject_id IN ({placeholders})"

    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute(query, batch_subject_ids)
    formats = cursor.fetchall()
    cursor.close()
    conn.close()
    return formats


def fetch_lesson_formats():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lesson_formats")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_student():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM student")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_subject():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM subject")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_timetable():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM timetable")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_timetableslot():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM timetableslot")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_venue():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM venue")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data


def fetch_venue_availability():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM venue_availability")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def fetch_active_timetables():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM timetable WHERE status = 'active'")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def fetch_venue_availability_by_venue(venue_id):
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM venue_availability WHERE venue_id = %s", (venue_id,))
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def fetch_timetableslot_by_timetable(timetable_id):
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM timetableslot WHERE timetable_id = %s", (timetable_id,))
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def fetch_lecturer_availability_by_lecturer(lecturer_id):
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM lecturer_availability WHERE lecturer_id = %s", (lecturer_id,))
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def fetch_student_by_batch(batch_id):
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM student WHERE batch_id = %s", (batch_id,))
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def fetch_batch_by_id(batch_id):
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM batch WHERE batch_id = %s", (batch_id,))
    data = cursor.fetchone()
    cursor.close()
    conn.close()
    return data




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
