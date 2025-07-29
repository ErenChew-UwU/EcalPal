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
